<?php

namespace App\Jobs;

use App\Enums\PreviewStatus;
use App\Enums\UploadBatchStatus;
use App\Enums\UploadBatchStepStatus;
use App\Models\BulkUploadJobLog;
use App\Models\BulkUploadPreview;
use App\Models\UploadBatch;
use App\Models\UploadBatchStep;
use App\Services\PreviewValidationService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

/**
 * GeneratePreviewMetadataJob
 *
 * Second step in the bulk upload pipeline.
 *
 * Runs after ProcessPdfBulkUploadJob completes and all preview rows exist.
 * Enriches each preview row with:
 *  - processing_metadata (source, extracted_at, enrichment flags)
 *  - validation_result   (blocking errors, warnings, readiness score)
 *  - status → DRAFT or NEEDS_REVIEW based on score
 *
 * Rules:
 *  - Never publishes a product — publishing is exclusively PublishBulkProductsJob's job.
 *  - Processes rows in chunks of 100 to avoid memory pressure.
 *  - Individual preview failures are recorded but do not abort the job.
 *  - Batch status: EXTRACTING/REVIEW_READY → PROCESSING → REVIEW_READY (when done).
 *  - Writes a BulkUploadJobLog row on completion or failure.
 */
class GeneratePreviewMetadataJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    // ── Queue configuration ───────────────────────────────────────────────

    /** Maximum seconds the job may run. */
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
        return "metadata:{$this->batchUuid}";
    }

    // ── Constructor ───────────────────────────────────────────────────────

    public function __construct(
        public readonly string $batchUuid,
    ) {}

    // ── Unique identity ───────────────────────────────────────────────────

    public function uniqueId(): string
    {
        return "metadata:{$this->batchUuid}";
    }

    // ── Handle ────────────────────────────────────────────────────────────

    public function handle(PreviewValidationService $validator): void
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

        // ── 2. Transition batch → PROCESSING (only from EXTRACTING) ─────
        // In the full chain, ProcessPdfBulkUploadJob may leave the batch at
        // EXTRACTING (partial pages) or REVIEW_READY (all pages done).
        // We can only advance forward: EXTRACTING → PROCESSING.
        // If already at PROCESSING or REVIEW_READY, leave the status alone.
        if ($batch->status === UploadBatchStatus::EXTRACTING) {
            $batch->update([
                'status'           => UploadBatchStatus::PROCESSING,
                'progress_message' => 'Generating preview metadata…',
            ]);
        } elseif ($batch->status === UploadBatchStatus::REVIEW_READY) {
            // Batch already reached REVIEW_READY from the extraction step;
            // metadata enrichment runs in-place — no status change needed here.
            $batch->update(['progress_message' => 'Generating preview metadata…']);
        }

        // ── 3. Create / update step record ───────────────────────────────
        $step = UploadBatchStep::firstOrCreate(
            ['batch_uuid' => $batch->id, 'step' => 'generate_metadata'],
            ['status'     => UploadBatchStepStatus::PENDING]
        );

        $step->markRunning()->save();

        // ── 4. Process previews in chunks ─────────────────────────────────
        $processed    = 0;
        $failed       = 0;
        $skippedCount = 0;

        try {
            $batch->previews()
                ->whereNotIn('status', [
                    PreviewStatus::PUBLISHED->value,
                    PreviewStatus::FAILED->value,
                ])
                ->chunkById(100, function ($previews) use (
                    $validator, &$processed, &$failed, &$skippedCount
                ): void {
                    foreach ($previews as $preview) {
                        try {
                            $this->enrichPreview($preview, $validator);
                            $processed++;
                        } catch (Throwable $e) {
                            // Mark preview as failed; do not abort the whole job
                            $preview->forceFill([
                                'status'              => PreviewStatus::FAILED,
                                'processing_metadata' => array_merge(
                                    $preview->processing_metadata ?? [],
                                    [
                                        'metadata_error'      => $e->getMessage(),
                                        'metadata_failed_at'  => now()->toIso8601String(),
                                    ]
                                ),
                            ])->save();

                            $failed++;
                        }
                    }
                });

            // ── 5. Mark step completed ────────────────────────────────────
            $step->markCompleted()->save();

            // ── 6. Transition batch → REVIEW_READY (only if still PROCESSING) ──
            // If already at REVIEW_READY (set by ProcessPdfBulkUploadJob when
            // all pages were done in one shot), just update the message.
            if ($batch->fresh()->status === UploadBatchStatus::PROCESSING) {
                $batch->update([
                    'status'           => UploadBatchStatus::REVIEW_READY,
                    'progress_message' => "Metadata generation complete. {$processed} previews enriched.",
                ]);
            } else {
                $batch->update([
                    'progress_message' => "Metadata generation complete. {$processed} previews enriched.",
                ]);
            }

            // ── 7. Write success log ──────────────────────────────────────
            $this->writeJobLog(
                batchUuid: $batch->id,
                status:    2,
                startedAt: $startedAt,
                memBefore: $memBefore,
                extra:     ['processed' => $processed, 'failed' => $failed],
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
     * Marks the batch as FAILED so PublishBulkProductsJob is not dispatched.
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
                'progress_message' => 'Metadata generation failed: ' . $e->getMessage(),
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
     * Enrich a single preview with validation result and updated metadata.
     * Never changes status to PUBLISHED — only NEEDS_REVIEW or DRAFT.
     */
    private function enrichPreview(BulkUploadPreview $preview, PreviewValidationService $validator): void
    {
        $validationResult = $validator->validate($preview);

        // Determine status: previews with blocking errors stay NEEDS_REVIEW,
        // previews without blocking errors are promoted to READY-eligible but
        // we do not auto-publish — keep them at NEEDS_REVIEW for human review.
        $currentMeta = $preview->processing_metadata ?? [];
        $currentMeta['metadata_generated_at'] = now()->toIso8601String();
        $currentMeta['validation_score']       = $validationResult['score'];
        $currentMeta['has_blocking_errors']    = count($validationResult['blocking']) > 0;

        $preview->forceFill([
            'validation_result'   => $validationResult,
            'processing_metadata' => $currentMeta,
            // Keep existing status; only demote to NEEDS_REVIEW if it was DRAFT
            'status' => $preview->status === PreviewStatus::DRAFT
                ? PreviewStatus::NEEDS_REVIEW
                : $preview->status,
        ])->save();
    }

    /**
     * Write a BulkUploadJobLog row with execution metrics.
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
