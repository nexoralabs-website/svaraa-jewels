<?php

namespace App\Services;

use App\Enums\UploadBatchStatus;
use App\Models\BulkUploadPreview;
use App\Models\UploadBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * BulkUploadHealthService
 *
 * Runs a battery of health checks against the bulk upload subsystem.
 *
 * Scoring:
 *   Each check carries a penalty weight. The final score starts at 100
 *   and is reduced by the sum of penalties for failing checks.
 *
 *   score < 80  → warning state
 *   score < 50  → unhealthy state
 *
 * Usage:
 *   $result = app(BulkUploadHealthService::class)->check();
 *   // ['healthy' => bool, 'score' => int, 'warnings' => [], 'failures' => []]
 */
class BulkUploadHealthService
{
    // ── Penalty weights (deducted from 100) ──────────────────────────────

    private const PENALTY_FAILED_JOBS_HIGH   = 30;
    private const PENALTY_FAILED_JOBS_LOW    = 10;
    private const PENALTY_STALE_BATCHES      = 20;
    private const PENALTY_ORPHAN_PREVIEWS    = 10;
    private const PENALTY_QUEUE_UNHEALTHY    = 20;
    private const PENALTY_STORAGE_FAIL       = 25;
    private const PENALTY_DB_LATENCY_HIGH    = 15;

    // ── Thresholds ────────────────────────────────────────────────────────

    /** A batch stuck in EXTRACTING for more than this many minutes is stale */
    private const STALE_EXTRACTING_MINUTES = 30;

    /** Failed jobs in last 24h that trigger high-penalty */
    private const FAILED_JOBS_HIGH_THRESHOLD = 5;

    /** DB query time in ms that triggers a latency warning */
    private const DB_LATENCY_WARN_MS = 200;

    // ── Public API ────────────────────────────────────────────────────────

    /**
     * Run all health checks and return a structured result.
     *
     * @return array{healthy: bool, score: int, warnings: string[], failures: string[]}
     */
    public function check(): array
    {
        $score    = 100;
        $warnings = [];
        $failures = [];

        foreach ($this->runChecks() as $result) {
            if ($result['status'] === 'fail') {
                $failures[] = $result['message'];
                $score      -= $result['penalty'];
            } elseif ($result['status'] === 'warn') {
                $warnings[] = $result['message'];
                $score      -= (int) ($result['penalty'] / 2); // warnings are half-penalty
            }
        }

        $score = max(0, $score);

        return [
            'healthy'  => $score >= 50,
            'score'    => $score,
            'warnings' => $warnings,
            'failures' => $failures,
        ];
    }

    // ── Individual checks ─────────────────────────────────────────────────

    /**
     * @return array<int, array{status: string, message: string, penalty: int}>
     */
    private function runChecks(): array
    {
        return [
            $this->checkQueueHealth(),
            $this->checkFailedBatches(),
            $this->checkStaleBatches(),
            $this->checkOrphanPreviews(),
            $this->checkStorageAvailability(),
            $this->checkDbLatency(),
        ];
    }

    private function checkQueueHealth(): array
    {
        try {
            $failedCount = DB::table('failed_jobs')
                ->where('failed_at', '>=', now()->subHours(24))
                ->count();

            if ($failedCount >= self::FAILED_JOBS_HIGH_THRESHOLD) {
                return [
                    'status'  => 'fail',
                    'message' => "Queue: {$failedCount} failed jobs in last 24h (threshold: " . self::FAILED_JOBS_HIGH_THRESHOLD . ').',
                    'penalty' => self::PENALTY_QUEUE_UNHEALTHY,
                ];
            }

            if ($failedCount > 0) {
                return [
                    'status'  => 'warn',
                    'message' => "Queue: {$failedCount} failed job(s) in last 24h.",
                    'penalty' => self::PENALTY_QUEUE_UNHEALTHY,
                ];
            }

            return ['status' => 'ok', 'message' => 'Queue healthy.', 'penalty' => 0];
        } catch (Throwable $e) {
            return [
                'status'  => 'fail',
                'message' => 'Queue: cannot reach jobs table — ' . $e->getMessage(),
                'penalty' => self::PENALTY_QUEUE_UNHEALTHY,
            ];
        }
    }

    private function checkFailedBatches(): array
    {
        $recentFailed = UploadBatch::where('status', UploadBatchStatus::FAILED->value)
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        if ($recentFailed >= self::FAILED_JOBS_HIGH_THRESHOLD) {
            return [
                'status'  => 'fail',
                'message' => "Batches: {$recentFailed} batches failed in last 24h.",
                'penalty' => self::PENALTY_FAILED_JOBS_HIGH,
            ];
        }

        if ($recentFailed > 0) {
            return [
                'status'  => 'warn',
                'message' => "Batches: {$recentFailed} batch(es) failed in last 24h.",
                'penalty' => self::PENALTY_FAILED_JOBS_LOW,
            ];
        }

        return ['status' => 'ok', 'message' => 'No recently failed batches.', 'penalty' => 0];
    }

    private function checkStaleBatches(): array
    {
        $staleCount = UploadBatch::whereIn('status', [
            UploadBatchStatus::EXTRACTING->value,
            UploadBatchStatus::PROCESSING->value,
        ])
        ->where('updated_at', '<=', now()->subMinutes(self::STALE_EXTRACTING_MINUTES))
        ->count();

        if ($staleCount > 0) {
            return [
                'status'  => 'fail',
                'message' => "Stale: {$staleCount} batch(es) stuck in EXTRACTING/PROCESSING for >" . self::STALE_EXTRACTING_MINUTES . 'min.',
                'penalty' => self::PENALTY_STALE_BATCHES,
            ];
        }

        return ['status' => 'ok', 'message' => 'No stale batches detected.', 'penalty' => 0];
    }

    private function checkOrphanPreviews(): array
    {
        // Orphan = preview row whose batch_uuid references a non-existent batch
        $orphanExists = DB::table('bulk_upload_previews')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('upload_batches')
                  ->whereColumn('upload_batches.id', 'bulk_upload_previews.batch_uuid');
            })->exists();

        if ($orphanExists) {
            return [
                'status'  => 'warn',
                'message' => 'Orphan previews detected (batch_uuid references missing batch).',
                'penalty' => self::PENALTY_ORPHAN_PREVIEWS,
            ];
        }

        return ['status' => 'ok', 'message' => 'No orphan previews.', 'penalty' => 0];
    }

    private function checkStorageAvailability(): array
    {
        try {
            $testKey = 'bulk-upload-health-probe-' . now()->timestamp . '.txt';
            Storage::disk('public')->put($testKey, 'ok');
            $content = Storage::disk('public')->get($testKey);
            Storage::disk('public')->delete($testKey);

            if ($content !== 'ok') {
                return [
                    'status'  => 'fail',
                    'message' => 'Storage: public disk read/write probe returned unexpected content.',
                    'penalty' => self::PENALTY_STORAGE_FAIL,
                ];
            }

            return ['status' => 'ok', 'message' => 'Storage public disk is readable/writable.', 'penalty' => 0];
        } catch (Throwable $e) {
            return [
                'status'  => 'fail',
                'message' => 'Storage: public disk unavailable — ' . $e->getMessage(),
                'penalty' => self::PENALTY_STORAGE_FAIL,
            ];
        }
    }

    private function checkDbLatency(): array
    {
        try {
            $start  = microtime(true);
            DB::select('SELECT 1');
            $elapsed = (int) ((microtime(true) - $start) * 1000);

            if ($elapsed >= self::DB_LATENCY_WARN_MS) {
                return [
                    'status'  => 'warn',
                    'message' => "DB latency high: {$elapsed}ms (threshold: " . self::DB_LATENCY_WARN_MS . 'ms).',
                    'penalty' => self::PENALTY_DB_LATENCY_HIGH,
                ];
            }

            return ['status' => 'ok', 'message' => "DB latency OK: {$elapsed}ms.", 'penalty' => 0];
        } catch (Throwable $e) {
            return [
                'status'  => 'fail',
                'message' => 'DB: connection failed — ' . $e->getMessage(),
                'penalty' => 100, // total failure
            ];
        }
    }
}
