<?php

namespace App\Services;

use App\Enums\PreviewStatus;
use App\Enums\UploadBatchStatus;
use App\Models\BulkUploadJobLog;
use App\Models\BulkUploadPreview;
use App\Models\UploadBatch;
use Illuminate\Support\Facades\DB;

/**
 * BulkUploadMetricsService
 *
 * Computes operational metrics from bulk_upload_job_logs and related tables.
 * Uses aggregates and EXISTS checks — avoids COUNT(*) on large sets where possible.
 *
 * All methods return arrays; no rendering or formatting.
 */
class BulkUploadMetricsService
{
    // ── Per-batch metrics ─────────────────────────────────────────────────

    /**
     * Compute metrics for a single batch.
     *
     * Returns:
     * [
     *   'batch_uuid'           => string,
     *   'status'               => string,
     *   'total_pages'          => int,
     *   'processed_pages'      => int,
     *   'previews_total'       => int,
     *   'previews_published'   => int,
     *   'previews_failed'      => int,
     *   'avg_processing_time_ms' => float|null,
     *   'avg_memory_mb'        => float|null,
     *   'retry_count'          => int,
     *   'publish_success_rate' => float,   // 0.0–1.0
     *   'publish_failure_rate' => float,
     *   'queue_wait_ms'        => int|null,
     * ]
     */
    public function getBatchMetrics(string $batchUuid): array
    {
        $batch = UploadBatch::find($batchUuid);

        if ($batch === null) {
            return ['batch_uuid' => $batchUuid, 'error' => 'not_found'];
        }

        // Aggregate job logs for this batch in a single query
        $logStats = BulkUploadJobLog::where('batch_uuid', $batchUuid)
            ->selectRaw('
                AVG(duration_ms)  AS avg_duration_ms,
                AVG(memory_mb)    AS avg_memory_mb,
                SUM(CASE WHEN attempt > 1 THEN 1 ELSE 0 END) AS retry_count,
                MIN(started_at)   AS first_started,
                MAX(ended_at)     AS last_ended
            ')
            ->first();

        // Preview status breakdown — single GROUP BY
        $previewCounts = BulkUploadPreview::where('batch_uuid', $batchUuid)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalPreviews     = $previewCounts->sum();
        $publishedPreviews = (int) ($previewCounts[PreviewStatus::PUBLISHED->value] ?? 0);
        $failedPreviews    = (int) ($previewCounts[PreviewStatus::FAILED->value] ?? 0);

        $successRate = $totalPreviews > 0
            ? round($publishedPreviews / $totalPreviews, 4)
            : 0.0;
        $failureRate = $totalPreviews > 0
            ? round($failedPreviews / $totalPreviews, 4)
            : 0.0;

        // Queue wait = time between batch created_at and first job started_at
        $queueWaitMs = null;
        if ($logStats && $logStats->first_started && $batch->created_at) {
            $queueWaitMs = (int) ($batch->created_at->diffInMilliseconds(
                \Carbon\Carbon::parse($logStats->first_started)
            ));
        }

        return [
            'batch_uuid'             => $batchUuid,
            'status'                 => $batch->status->name,
            'total_pages'            => $batch->total_pages,
            'processed_pages'        => $batch->processed_pages,
            'previews_total'         => (int) $totalPreviews,
            'previews_published'     => $publishedPreviews,
            'previews_failed'        => $failedPreviews,
            'avg_processing_time_ms' => $logStats && $logStats->avg_duration_ms !== null ? round((float) $logStats->avg_duration_ms, 2) : null,
            'avg_memory_mb'          => $logStats && $logStats->avg_memory_mb !== null ? round((float) $logStats->avg_memory_mb, 3) : null,
            'retry_count'            => $logStats ? (int) $logStats->retry_count : 0,
            'publish_success_rate'   => $successRate,
            'publish_failure_rate'   => $failureRate,
            'queue_wait_ms'          => $queueWaitMs,
        ];
    }

    // ── System-wide metrics ───────────────────────────────────────────────

    /**
     * Compute system-wide metrics across all batches.
     *
     * Returns:
     * [
     *   'total_batches'           => int,
     *   'completed_batches'       => int,
     *   'failed_batches'          => int,
     *   'batch_completion_rate'   => float,   // 0.0–1.0
     *   'total_previews'          => int,
     *   'total_published'         => int,
     *   'total_failed_previews'   => int,
     *   'avg_processing_time_ms'  => float|null,
     *   'avg_memory_mb'           => float|null,
     *   'avg_previews_per_batch'  => float,
     *   'retry_count'             => int,
     *   'publish_success_rate'    => float,
     * ]
     */
    public function getSystemMetrics(): array
    {
        // Batch status counts — single GROUP BY
        $batchCounts = UploadBatch::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalBatches     = (int) $batchCounts->sum();
        $completedBatches = (int) ($batchCounts[UploadBatchStatus::COMPLETED->value] ?? 0);
        $failedBatches    = (int) ($batchCounts[UploadBatchStatus::FAILED->value] ?? 0);

        $completionRate = $totalBatches > 0
            ? round($completedBatches / $totalBatches, 4)
            : 0.0;

        // Job log aggregates — single query
        $logStats = BulkUploadJobLog::selectRaw('
            AVG(duration_ms)  AS avg_duration_ms,
            AVG(memory_mb)    AS avg_memory_mb,
            SUM(CASE WHEN attempt > 1 THEN 1 ELSE 0 END) AS retry_count
        ')->first();

        // Preview counts — single GROUP BY
        $previewCounts = BulkUploadPreview::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalPreviews   = (int) $previewCounts->sum();
        $totalPublished  = (int) ($previewCounts[PreviewStatus::PUBLISHED->value] ?? 0);
        $totalFailed     = (int) ($previewCounts[PreviewStatus::FAILED->value] ?? 0);

        $successRate = $totalPreviews > 0
            ? round($totalPublished / $totalPreviews, 4)
            : 0.0;

        $avgPerBatch = $totalBatches > 0
            ? round($totalPreviews / $totalBatches, 2)
            : 0.0;

        return [
            'total_batches'          => $totalBatches,
            'completed_batches'      => $completedBatches,
            'failed_batches'         => $failedBatches,
            'batch_completion_rate'  => $completionRate,
            'total_previews'         => $totalPreviews,
            'total_published'        => $totalPublished,
            'total_failed_previews'  => $totalFailed,
            'avg_processing_time_ms' => $logStats ? round((float) $logStats->avg_duration_ms, 2) : null,
            'avg_memory_mb'          => $logStats ? round((float) $logStats->avg_memory_mb, 3) : null,
            'avg_previews_per_batch' => $avgPerBatch,
            'retry_count'            => $logStats ? (int) $logStats->retry_count : 0,
            'publish_success_rate'   => $successRate,
        ];
    }

    // ── Dashboard summary ─────────────────────────────────────────────────

    /**
     * Lightweight dashboard payload for admin overview panels.
     * Designed to be fast: avoids full-table scans.
     *
     * Returns:
     * [
     *   'today_uploads'       => int,
     *   'today_success_pct'   => int,      // 0–100
     *   'avg_pages'           => float,
     *   'queue_load'          => int,       // pending jobs in DB queue
     *   'retry_rate'          => float,     // retried / total job logs
     *   'health_score'        => int,       // 0–100 (from BulkUploadHealthService)
     *   'active_batches'      => int,
     *   'total_published_today'=> int,
     * ]
     */
    public function dashboard(): array
    {
        $todayStart = now()->startOfDay();

        // Today's batch count — indexed on created_at
        $todayBatches = UploadBatch::where('created_at', '>=', $todayStart)->count();

        // Today's completions
        $todayCompleted = UploadBatch::where('created_at', '>=', $todayStart)
            ->where('status', UploadBatchStatus::COMPLETED->value)
            ->count();

        $todaySuccessPct = $todayBatches > 0
            ? (int) round(($todayCompleted / $todayBatches) * 100)
            : 0;

        // Active (non-terminal) batches
        $activeBatches = UploadBatch::whereNotIn('status', [
            UploadBatchStatus::COMPLETED->value,
            UploadBatchStatus::FAILED->value,
        ])->count();

        // Avg pages per batch (recent 30 batches for speed)
        $avgPages = UploadBatch::latest()
            ->limit(30)
            ->avg('total_pages') ?? 0.0;

        // Queue load from DB queue table
        $queueLoad = DB::table('jobs')->count();

        // Retry rate: job logs with attempt > 1 / total logs
        $totalLogs  = BulkUploadJobLog::count();
        $retryLogs  = BulkUploadJobLog::where('attempt', '>', 1)->count();
        $retryRate  = $totalLogs > 0 ? round($retryLogs / $totalLogs, 4) : 0.0;

        // Published today
        $publishedToday = BulkUploadPreview::where('published_at', '>=', $todayStart)
            ->where('status', PreviewStatus::PUBLISHED->value)
            ->count();

        // Inline health score (lightweight version — avoids full service boot)
        $failedBatches = UploadBatch::where('status', UploadBatchStatus::FAILED->value)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
        $healthScore = max(0, 100 - ($failedBatches * 10) - (int) ($retryRate * 50));

        return [
            'today_uploads'         => $todayBatches,
            'today_success_pct'     => $todaySuccessPct,
            'avg_pages'             => round((float) $avgPages, 1),
            'queue_load'            => $queueLoad,
            'retry_rate'            => $retryRate,
            'health_score'          => (int) $healthScore,
            'active_batches'        => $activeBatches,
            'total_published_today' => $publishedToday,
        ];
    }
}
