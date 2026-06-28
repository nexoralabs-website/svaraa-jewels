<?php

namespace Tests\Unit;

use App\Enums\PreviewStatus;
use App\Enums\UploadBatchStatus;
use App\Enums\UploadBatchStepStatus;
use App\Jobs\ProcessPdfBulkUploadJob;
use App\Models\BulkUploadJobLog;
use App\Models\UploadBatch;
use App\Models\UploadBatchStep;
use App\Services\BulkUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for ProcessPdfBulkUploadJob.
 *
 * Covers: retry safety, idempotency, metrics, status transitions, step management.
 */
class ProcessPdfBulkUploadJobTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────

    private function makeBatch(int $totalPages = 1, UploadBatchStatus $status = UploadBatchStatus::QUEUED): UploadBatch
    {
        return UploadBatch::create([
            'status'      => $status,
            'total_pages' => $totalPages,
        ]);
    }

    private function dispatchJob(UploadBatch $batch, string $file = 'test.pdf', int $start = 1, int $end = 1): void
    {
        (new ProcessPdfBulkUploadJob($batch->id, $file, $start, $end))
            ->handle(app(BulkUploadService::class));
    }

    // ── Constructor / uniqueId ────────────────────────────────────────────

    public function test_constructor_sets_readonly_properties(): void
    {
        $job = new ProcessPdfBulkUploadJob('abc-uuid', 'file.pdf', 3, 7);

        $this->assertSame('abc-uuid', $job->batchUuid);
        $this->assertSame('file.pdf', $job->filePath);
        $this->assertSame(3,          $job->startPage);
        $this->assertSame(7,          $job->endPage);
    }

    public function test_unique_id_encodes_batch_and_range(): void
    {
        $job = new ProcessPdfBulkUploadJob('batch-xyz', 'f.pdf', 5, 10);

        $this->assertSame('batch-xyz:5-10', $job->uniqueId());
    }

    public function test_unique_id_differs_for_different_page_ranges(): void
    {
        $a = new ProcessPdfBulkUploadJob('same', 'f.pdf', 1, 10);
        $b = new ProcessPdfBulkUploadJob('same', 'f.pdf', 11, 20);

        $this->assertNotSame($a->uniqueId(), $b->uniqueId());
    }

    public function test_timeout_and_tries_are_set(): void
    {
        $job = new ProcessPdfBulkUploadJob('x', 'f.pdf', 1, 1);

        $this->assertSame(300, $job->timeout);
        $this->assertSame(3,   $job->tries);
    }

    // ── Batch status transitions ──────────────────────────────────────────

    public function test_transitions_queued_batch_to_extracting_during_handle(): void
    {
        $batch = $this->makeBatch();

        // We check the extracting transition by observing that at job end
        // the batch moved to REVIEW_READY (which can only come from EXTRACTING)
        $this->dispatchJob($batch);

        $this->assertSame(UploadBatchStatus::REVIEW_READY, $batch->fresh()->status);
    }

    public function test_marks_batch_review_ready_when_all_pages_processed(): void
    {
        $batch = $this->makeBatch(3);

        $this->dispatchJob($batch, 'test.pdf', 1, 3);

        $this->assertSame(UploadBatchStatus::REVIEW_READY, $batch->fresh()->status);
    }

    public function test_does_not_advance_to_review_ready_when_pages_remain(): void
    {
        $batch = $this->makeBatch(3); // 3 total, only process 1

        $this->dispatchJob($batch, 'test.pdf', 1, 1);

        // processed_pages = 1, total_pages = 3 → should NOT be REVIEW_READY
        $fresh = $batch->fresh();
        $this->assertNotSame(UploadBatchStatus::REVIEW_READY, $fresh->status);
        $this->assertSame(1, $fresh->processed_pages);
    }

    // ── Preview creation ──────────────────────────────────────────────────

    public function test_creates_one_preview_per_page(): void
    {
        $batch = $this->makeBatch(3);

        $this->dispatchJob($batch, 'test.pdf', 1, 3);

        $this->assertSame(3, $batch->previews()->count());
    }

    public function test_preview_has_correct_pdf_page_and_source_fields(): void
    {
        $batch = $this->makeBatch(1);

        $this->dispatchJob($batch, 'catalogue.pdf', 5, 5);

        $preview = $batch->previews()->first();
        $this->assertSame(5,               $preview->pdf_page);
        $this->assertSame('catalogue.pdf', $preview->source_pdf);
        $this->assertSame('pdf',           $preview->source);
    }

    public function test_preview_status_is_needs_review(): void
    {
        $batch = $this->makeBatch(1);

        $this->dispatchJob($batch);

        $preview = $batch->previews()->first();
        $this->assertSame(PreviewStatus::NEEDS_REVIEW, $preview->status);
    }

    // ── Idempotency ───────────────────────────────────────────────────────

    public function test_duplicate_dispatch_does_not_create_duplicate_previews(): void
    {
        $batch   = $this->makeBatch(1);
        $service = app(BulkUploadService::class);

        (new ProcessPdfBulkUploadJob($batch->id, 'test.pdf', 1, 1))->handle($service);
        (new ProcessPdfBulkUploadJob($batch->id, 'test.pdf', 1, 1))->handle($service);

        $this->assertSame(1, $batch->previews()->count());
    }

    public function test_skips_processing_when_batch_is_completed(): void
    {
        // Move batch to terminal
        $batch = $this->makeBatch(1);
        $batch->update(['status' => UploadBatchStatus::EXTRACTING]);
        $batch->update(['status' => UploadBatchStatus::PROCESSING]);
        $batch->update(['status' => UploadBatchStatus::REVIEW_READY]);
        $batch->update(['status' => UploadBatchStatus::COMPLETED]);

        $this->dispatchJob($batch);

        $this->assertSame(0, $batch->previews()->count());
    }

    public function test_skips_processing_when_batch_is_failed(): void
    {
        $batch = $this->makeBatch(1);
        $batch->update(['status' => UploadBatchStatus::EXTRACTING]);
        $batch->update(['status' => UploadBatchStatus::FAILED]);

        $this->dispatchJob($batch);

        $this->assertSame(0, $batch->previews()->count());
    }

    // ── Step management ───────────────────────────────────────────────────

    public function test_creates_extract_pdf_step_on_first_run(): void
    {
        $batch = $this->makeBatch(1);

        $this->dispatchJob($batch);

        $step = UploadBatchStep::where('batch_uuid', $batch->id)
            ->where('step', 'extract_pdf')
            ->first();

        $this->assertNotNull($step);
    }

    public function test_step_is_completed_after_successful_run(): void
    {
        $batch = $this->makeBatch(1);

        $this->dispatchJob($batch);

        $step = UploadBatchStep::where('batch_uuid', $batch->id)
            ->where('step', 'extract_pdf')->first();

        $this->assertSame(UploadBatchStepStatus::COMPLETED, $step->status);
        $this->assertNotNull($step->finished_at);
        $this->assertNotNull($step->duration_ms);
    }

    public function test_step_duration_ms_is_non_negative(): void
    {
        $batch = $this->makeBatch(1);

        $this->dispatchJob($batch);

        $step = UploadBatchStep::where('batch_uuid', $batch->id)
            ->where('step', 'extract_pdf')->first();

        $this->assertGreaterThanOrEqual(0, $step->duration_ms);
    }

    // ── Metrics / job log ─────────────────────────────────────────────────

    public function test_writes_completed_job_log(): void
    {
        $batch = $this->makeBatch(1);

        $this->dispatchJob($batch);

        $log = BulkUploadJobLog::where('batch_uuid', $batch->id)
            ->where('job_name', ProcessPdfBulkUploadJob::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertSame(2, (int) $log->status); // 2 = completed
    }

    public function test_job_log_contains_duration_and_memory(): void
    {
        $batch = $this->makeBatch(1);

        $this->dispatchJob($batch);

        $log = BulkUploadJobLog::where('batch_uuid', $batch->id)->first();

        $this->assertNotNull($log->duration_ms);
        $this->assertNotNull($log->memory_mb);
        $this->assertGreaterThanOrEqual(0, (float) $log->memory_mb);
    }

    public function test_job_log_records_worker_hostname(): void
    {
        $batch = $this->makeBatch(1);

        $this->dispatchJob($batch);

        $log = BulkUploadJobLog::where('batch_uuid', $batch->id)->first();

        $this->assertNotNull($log->worker);
        $this->assertSame(gethostname(), $log->worker);
    }

    public function test_job_log_records_started_at_and_ended_at(): void
    {
        $batch = $this->makeBatch(1);

        $this->dispatchJob($batch);

        $log = BulkUploadJobLog::where('batch_uuid', $batch->id)->first();

        $this->assertNotNull($log->started_at);
        $this->assertNotNull($log->ended_at);
        $this->assertTrue($log->ended_at->gte($log->started_at));
    }

    // ── Batch deleted mid-flight ──────────────────────────────────────────

    public function test_silently_returns_when_batch_not_found(): void
    {
        $this->expectNotToPerformAssertions();

        (new ProcessPdfBulkUploadJob('00000000-0000-0000-0000-000000000000', 'x.pdf', 1, 1))
            ->handle(app(BulkUploadService::class));
    }

    // ── processed_pages counter ───────────────────────────────────────────

    public function test_processed_pages_incremented_by_page_count(): void
    {
        $batch = $this->makeBatch(5);

        $this->dispatchJob($batch, 'test.pdf', 1, 3);

        $this->assertSame(3, $batch->fresh()->processed_pages);
    }
}
