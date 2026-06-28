<?php

namespace App\Jobs;

use App\Enums\UploadBatchStatus;
use App\Enums\UploadBatchStepStatus;
use App\Models\BulkUploadJobLog;
use App\Models\UploadBatch;
use App\Models\UploadBatchStep;
use App\Services\PreviewPublishService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

/**
 * PublishBulkProductsJob
 *
 * Third (optional) step in the bulk upload pipeline.
 *
 * Publishes all READY previews in a batch as live Product rows.
 * Only runs when publish=true was requested at dispatch time.
 *
 * Rules:
 *  - Only processes previews with status = READY (never NEEDS_REVIEW or DRAFT).
 *  - Individual preview failures are logged but do not rollback successful ones.
 *  - Transitions batch to COMPLETED on success (even if some previews failed to publish).
 *  - Writes a BulkUploadJobLog row with metrics on completion or failure.
 */
class PublishBulkProductsJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    // ── Queue configuration ───────────────────────────────────────────────

    /** Maximum seconds before Laravel kills the job. */
    public int $timeout = 300;

    /** Retry attempts before marking permanently failed. */
    public int $tries = 3;

    /** Exponential backoff in seconds. */
    public function backoff(): array
    {
        return [30, 60, 120];
    }

    /** Retry until 24 hours from job creation. */
    public function retryUntil(): \DateTimeInterface
    {
        return now()->addHours(24);
    }

    /** Correlation ID for logs. */
    public function getCorrelationId(): string
    {
        return "publish:{$this->batchUuid}";
    }

    // ── Constructor ───────────────────────────────────────────────────────

    public function __construct(
        public readonly string $batchUuid,
    ) {}

    // ── Unique identity ───────────────────────────────────────────────────

    public function uniqueId(): string
    {
        return "publish:{$this->batchUuid}";
    }

    // ── Handle ────────────────────────────────────────────────────────────

    public function handle(PreviewPublishService $publisher): void
    {
        $startedAt = now();
        $memBefore = memory_get_usage(true);

        // ── 1. Load batch ─────────────────────────────────────────────────
        $batch = UploadBatch::find($this->batchUuid);

        if ($batch === null) {
            $this->delete();
            return;
        }

        // Skip terminal states (idempotency)
        if (in_array($batch->status, [UploadBatchStatus::COMPLETED, UploadBatchStatus::FAILED], true)) {
            return;
        }

        // ── 2. Create / update step record ───────────────────────────────
        $step = UploadBatchStep::firstOrCreate(
            ['batch_uuid' => $batch->id, 'step' => 'publish'],
            ['status'     => UploadBatchStepStatus::PENDING]
        );

        $step->markRunning()->save();

        // ── 3. Publish READY previews via service ─────────────────────────
        try {
            $metrics = $publisher->publishBatch($batch);

            // ── 4. Mark step completed ────────────────────────────────────
            $step->markCompleted()->save();

            // ── 5. Transition batch → COMPLETED ───────────────────────────
            $batch->update([
                'status'           => UploadBatchStatus::COMPLETED,
                'progress_message' => sprintf(
                    'Publish complete. %d published, %d failed.',
                    $metrics['published'],
                    $metrics['failed']
                ),
            ]);

            // ── 6. Write success log with metrics ─────────────────────────
            $this->writeJobLog(
                batchUuid: $batch->id,
                status:    2,
                startedAt: $startedAt,
                memBefore: $memBefore,
                extra:     $metrics,
            );

        } catch (Throwable $e) {
            $step->markFailed($e->getMessage())->save();

            $this->writeJobLog(
                batchUuid: $batch->id,
                status:    3,
                startedAt: $startedAt,
                memBefore: $memBefore,
                error:     $e->getMessage(),
            );

            throw $e; // re-throw so Laravel can retry
        }
    }

    // ── Failure hook ──────────────────────────────────────────────────────

    /**
     * Called by Laravel after all retry attempts are exhausted.
     * Marks batch as FAILED — already-published products are NOT rolled back.
     */
    public function failed(Throwable $e): void
    {
        $batch = UploadBatch::find($this->batchUuid);

        if ($batch === null) {
            return;
        }

        try {
            $batch->update([
                'status'           => UploadBatchStatus::FAILED,
                'progress_message' => 'Publish pipeline failed: ' . $e->getMessage(),
            ]);
        } catch (Throwable) {
            // Already terminal — ignore
        }

        $this->writeJobLog(
            batchUuid: $batch->id,
            status:    3,
            startedAt: now(),
            memBefore: memory_get_usage(true),
            error:     $e->getMessage(),
        );
    }

    // ── Internals ─────────────────────────────────────────────────────────

    /**
     * Write a BulkUploadJobLog row with execution metrics.
     *
     * @param array<string,mixed> $extra Merged into a structured metrics payload stored in error field comments.
     */
    private function writeJobLog(
        string  $batchUuid,
        int     $status,
        Carbon  $startedAt,
        int     $memBefore,
        ?string $error = null,
        array   $extra = [],
    ): void {
        $endedAt    = now();
        $durationMs = (int) ($startedAt->diffInMilliseconds($endedAt));
        $memUsedMb  = round((memory_get_usage(true) - $memBefore) / 1024 / 1024, 2);

        BulkUploadJobLog::create([
            'batch_uuid'  => $batchUuid,
            'job_name'    => self::class,
            'status'      => $status,
            'attempt'     => $this->attempts(),
            'worker'      => gethostname(),
            'started_at'  => $startedAt,
            'ended_at'    => $endedAt,
            'duration_ms' => $durationMs,
            'memory_mb'   => max(0, $memUsedMb),
            'error'       => $error,
        ]);
    }
}
