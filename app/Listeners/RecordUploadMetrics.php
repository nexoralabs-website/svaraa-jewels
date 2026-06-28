<?php

namespace App\Listeners;

use App\Events\UploadBatchCompleted;
use App\Events\UploadBatchFailed;
use App\Events\UploadBatchRecovered;
use App\Models\UploadBatch;

/**
 * RecordUploadMetrics
 *
 * Listens to batch lifecycle events and writes a structured metrics snapshot
 * into the batch's processing_metadata JSON column.
 *
 * No external calls — purely DB writes.
 */
class RecordUploadMetrics
{
    // ── UploadBatchCompleted ──────────────────────────────────────────────

    public function handleCompleted(UploadBatchCompleted $event): void
    {
        $this->stampMetadata($event->batch, array_merge([
            'event'          => 'completed',
            'event_recorded_at' => now()->toIso8601String(),
        ], $event->metrics));
    }

    // ── UploadBatchFailed ─────────────────────────────────────────────────

    public function handleFailed(UploadBatchFailed $event): void
    {
        $this->stampMetadata($event->batch, [
            'event'          => 'failed',
            'event_recorded_at' => now()->toIso8601String(),
            'failure_reason' => $event->reason,
            'attempt'        => $event->attempt,
        ]);
    }

    // ── UploadBatchRecovered ──────────────────────────────────────────────

    public function handleRecovered(UploadBatchRecovered $event): void
    {
        $this->stampMetadata($event->batch, [
            'event'          => 'recovered',
            'event_recorded_at' => now()->toIso8601String(),
            'retry_count'    => $event->retryCount,
        ]);
    }

    // ── Internals ─────────────────────────────────────────────────────────

    private function stampMetadata(UploadBatch $batch, array $data): void
    {
        $current = $batch->metadata ?? [];
        $history = $current['event_history'] ?? [];
        $history[] = $data;

        $current['event_history']  = $history;
        $current['last_event']     = $data['event'];
        $current['last_event_at']  = $data['event_recorded_at'];

        // Merge any extra metric fields at top level
        foreach ($data as $k => $v) {
            if (! in_array($k, ['event', 'event_recorded_at'], true)) {
                $current[$k] = $v;
            }
        }

        // Use query builder to avoid triggering the status-regression guard in boot()
        UploadBatch::where('id', $batch->id)->update(['metadata' => json_encode($current)]);
    }
}
