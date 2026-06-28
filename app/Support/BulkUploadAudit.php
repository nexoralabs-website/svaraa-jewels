<?php

namespace App\Support;

use App\Models\BulkUploadJobLog;
use App\Models\BulkUploadPreview;
use App\Models\UploadBatch;
use Illuminate\Support\Collection;

/**
 * BulkUploadAudit
 *
 * Provides structured audit queries over bulk_upload_job_logs and
 * processing_metadata columns. No writes — read-only audit surface.
 *
 * Designed to be used by admin dashboards and operations commands.
 */
class BulkUploadAudit
{
    // ── Generic record ────────────────────────────────────────────────────

    /**
     * Record an audit entry in a batch's metadata event_history.
     * Wraps common operations (retry, manual override, admin action).
     */
    public static function record(string $batchUuid, string $action, array $context = []): void
    {
        $batch = UploadBatch::find($batchUuid);
        if ($batch === null) {
            return;
        }

        $current = $batch->metadata ?? [];
        $history = $current['event_history'] ?? [];

        $history[] = array_merge([
            'action'     => $action,
            'recorded_at'=> now()->toIso8601String(),
            'actor'      => auth()->id() ?? 'system',
        ], $context);

        $current['event_history'] = $history;

        UploadBatch::where('id', $batchUuid)->update([
            'metadata' => json_encode($current),
        ]);
    }

    // ── Batch-scoped queries ──────────────────────────────────────────────

    /**
     * Return all job log entries for a batch, ordered by started_at.
     *
     * @return Collection<int, BulkUploadJobLog>
     */
    public static function forBatch(string $batchUuid): Collection
    {
        return BulkUploadJobLog::where('batch_uuid', $batchUuid)
            ->orderBy('started_at')
            ->get();
    }

    /**
     * Return all non-deleted preview rows for a batch.
     *
     * @return Collection<int, BulkUploadPreview>
     */
    public static function forPreview(string $batchUuid): Collection
    {
        return BulkUploadPreview::where('batch_uuid', $batchUuid)
            ->orderBy('pdf_page')
            ->get(['id', 'status', 'name', 'pdf_page', 'validation_result',
                   'edited_count', 'published_product_id', 'created_at', 'updated_at']);
    }

    // ── Timeline ──────────────────────────────────────────────────────────

    /**
     * Build a chronological timeline of events for a batch.
     *
     * Returns an array of timeline entries:
     * [
     *   ['time' => Carbon, 'type' => 'job_log'|'metadata_event', 'label' => string, 'detail' => string],
     *   ...
     * ]
     */
    public static function timeline(string $batchUuid): array
    {
        $timeline = [];

        // --- Job log entries ---
        BulkUploadJobLog::where('batch_uuid', $batchUuid)
            ->orderBy('started_at')
            ->each(function (BulkUploadJobLog $log) use (&$timeline) {
                $shortName = class_basename($log->job_name);
                $statusLabel = match ((int) $log->status) {
                    0 => 'pending',
                    1 => 'running',
                    2 => 'completed',
                    3 => 'failed',
                    default => 'unknown',
                };

                $timeline[] = [
                    'time'   => $log->started_at,
                    'type'   => 'job_log',
                    'label'  => "{$shortName} [{$statusLabel}]",
                    'detail' => $log->error
                        ? "Error: {$log->error}"
                        : "Duration: {$log->duration_ms}ms | Memory: {$log->memory_mb}MB | Attempt: {$log->attempt}",
                ];
            });

        // --- Metadata event_history entries ---
        $batch = UploadBatch::find($batchUuid);
        if ($batch) {
            $history = $batch->metadata['event_history'] ?? [];
            foreach ($history as $entry) {
                $label  = $entry['action'] ?? $entry['event'] ?? 'event';
                $actor  = $entry['actor'] ?? 'system';

                $timeline[] = [
                    'time'   => isset($entry['recorded_at'])
                        ? \Carbon\Carbon::parse($entry['recorded_at'])
                        : \Carbon\Carbon::parse($entry['event_recorded_at'] ?? now()),
                    'type'   => 'metadata_event',
                    'label'  => "[{$label}] by {$actor}",
                    'detail' => collect($entry)
                        ->except(['action', 'event', 'recorded_at', 'event_recorded_at', 'actor'])
                        ->map(fn ($v, $k) => "{$k}={$v}")
                        ->join(', '),
                ];
            }
        }

        // Sort by time ascending
        usort($timeline, fn ($a, $b) => $a['time'] <=> $b['time']);

        return $timeline;
    }
}
