<?php

namespace App\Jobs;

use App\Enums\UploadBatchStatus;
use App\Enums\UploadBatchStepStatus;
use App\Models\BulkUploadJobLog;
use App\Models\UploadBatch;
use App\Models\UploadBatchStep;
use App\Services\BulkUploadService;
use App\Services\PdfProductImportService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessPdfBulkUploadJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    // ── Queue configuration ───────────────────────────────────────────────

    /** Maximum seconds the job may run before being killed. */
    public int $timeout = 300;

    /** Number of attempts before the job is marked failed. */
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

    /** Add a correlation ID to log output. */
    public function getCorrelationId(): string
    {
        return "{$this->batchUuid}:{$this->startPage}-{$this->endPage}";
    }

    // ── Constructor ───────────────────────────────────────────────────────

    public function __construct(
        public readonly string $batchUuid,
        public readonly string $filePath,
        public readonly int    $startPage,
        public readonly int    $endPage,
    ) {}

    // ── Unique identity ───────────────────────────────────────────────────

    /**
     * Prevent duplicate dispatches for the same batch + page range.
     * Laravel uses this as the cache key for the uniqueness lock.
     */
    public function uniqueId(): string
    {
        return "{$this->batchUuid}:{$this->startPage}-{$this->endPage}";
    }

    // ── Handle ────────────────────────────────────────────────────────────

    public function handle(BulkUploadService $service, PdfProductImportService $pdfService): void
    {
        Log::info('JOB START', [
            'batch'=>$this->batchUuid,
            'file'=>$this->filePath,
            'pages'=>"{$this->startPage}-{$this->endPage}"
        ]);

        $startedAt = now();
        $memBefore = memory_get_usage(true);

        // ── 1. Load and validate batch ────────────────────────────────────
        $batch = UploadBatch::find($this->batchUuid);

        if ($batch === null) {
            // Batch was deleted — discard job silently, do not retry
            $this->delete();
            return;
        }

        // Skip if already in a terminal state (idempotency guard)
        if (in_array($batch->status, [UploadBatchStatus::COMPLETED, UploadBatchStatus::FAILED], true)) {
            return;
        }

        // ── 2. Transition batch → EXTRACTING (once; skip if already past it) ──
        if ($batch->status === UploadBatchStatus::QUEUED) {
            $batch->update([
                'status'           => UploadBatchStatus::EXTRACTING,
                'progress_message' => 'Extracting pages…',
            ]);
        }

        // ── 3. Create / update UploadBatchStep ────────────────────────────
        $stepName = 'extract_pdf';

        $step = UploadBatchStep::firstOrCreate(
            ['batch_uuid' => $batch->id, 'step' => $stepName],
            ['status' => UploadBatchStepStatus::PENDING]
        );

        $step->markRunning()->save();

        // ── 4. Real PDF extraction — create one preview per page with actual images ──
        try {
            if (! Storage::disk('public')->exists($this->filePath)) {
                throw new \RuntimeException("PDF file not found: {$this->filePath}");
            }

            $candidates = $pdfService->extractCandidates($this->filePath);

            Log::info('EXTRACTION RESULT', [
                'count'=>count($candidates),
                'paths'=>array_column($candidates, 'stored_path')
            ]);

            if (empty($candidates)) {
                throw new \RuntimeException('No images extracted from PDF');
            }

            $pageCount = $this->endPage - $this->startPage + 1;
            $previewCount = 0;

            foreach ($candidates as $candidate) {
                $pageNumber = $candidate['pdf_page'];

                if ($pageNumber < $this->startPage || $pageNumber > $this->endPage) {
                    continue;
                }

                // Idempotency: skip if a preview for this exact page already exists
                $alreadyExists = $batch->previews()
                    ->where('source_pdf', $this->filePath)
                    ->where('pdf_page', $pageNumber)
                    ->exists();

                if ($alreadyExists) {
                    continue;
                }

                Log::info('PREVIEW CREATE', [
                    'path'=>$candidate['stored_path'] ?? null
                ]);

                $preview = $service->createPreview($batch, [
                    'source'             => 'pdf',
                    'source_pdf'         => $this->filePath,
                    'pdf_page'           => $pageNumber,
                    'name'               => $candidate['name'],
                    'preview_image_path' => $candidate['stored_path'],
                    'processing_metadata' => [
                        'extraction_source' => 'pdf',
                        'page'              => $pageNumber,
                        'file'              => $this->filePath,
                        'sha256'            => $candidate['sha256'],
                        'from_embedded'     => $candidate['from_embedded'],
                        'pages_found'       => $candidate['pages_found'] ?? [$pageNumber],
                    ],
                ]);

                $previewCount++;
            }

            // ── 5. Update processed_pages counter atomically ──────────────
            UploadBatch::where('id', $batch->id)
                ->increment('processed_pages', $pageCount);

            // Reload to get current counts
            $batch->refresh();

            // ── 6. Mark step completed ────────────────────────────────────
            $step->markCompleted()->save();

            // ── 7. Transition batch to REVIEW_READY when all pages done ──
            if ($batch->processed_pages >= $batch->total_pages && $batch->total_pages > 0) {
                $batch->update([
                    'status'           => UploadBatchStatus::REVIEW_READY,
                    'progress_message' => 'Extraction complete. Ready for review.',
                ]);
            }

            Log::info('JOB COMPLETE', [
                'batch'=>$this->batchUuid,
                'preview_count'=>$previewCount ?? 0
            ]);

            // ── 8. Write success job log ──────────────────────────────────
            $this->writeJobLog(
                batchUuid:  $batch->id,
                status:     2, // completed
                startedAt:  $startedAt,
                memBefore:  $memBefore,
            );

        } catch (Throwable $e) {
            Log::error('JOB FAILED', [
                'batch'=>$this->batchUuid,
                'message'=>$e->getMessage()
            ]);
            $step->markFailed($e->getMessage())->save();

            $this->writeJobLog(
                batchUuid: $batch->id,
                status:    3, // failed
                startedAt: $startedAt,
                memBefore: $memBefore,
                error:     $e->getMessage(),
            );

            throw $e; // re-throw so Laravel retries
        }
    }

    // ── Failure hook ──────────────────────────────────────────────────────

    /**
     * Called by Laravel after all retry attempts are exhausted.
     * Marks the batch as FAILED so downstream jobs are blocked.
     */
    public function failed(Throwable $e): void
    {
        $batch = UploadBatch::find($this->batchUuid);

        if ($batch === null) {
            return;
        }

        // Only transition to FAILED from non-terminal states
        try {
            $batch->update([
                'status'           => UploadBatchStatus::FAILED,
                'progress_message' => 'Extraction failed: ' . $e->getMessage(),
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

    private function writeJobLog(
        string    $batchUuid,
        int       $status,
        \Carbon\Carbon $startedAt,
        int       $memBefore,
        ?string   $error = null,
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