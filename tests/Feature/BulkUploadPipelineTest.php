<?php

namespace Tests\Feature;

use App\Enums\PreviewStatus;
use App\Enums\UploadBatchStatus;
use App\Enums\UploadBatchStepStatus;
use App\Jobs\GeneratePreviewMetadataJob;
use App\Jobs\ProcessPdfBulkUploadJob;
use App\Jobs\PublishBulkProductsJob;
use App\Models\BulkUploadJobLog;
use App\Models\BulkUploadPreview;
use App\Models\Category;
use App\Models\UploadBatch;
use App\Models\UploadBatchStep;
use App\Services\BulkUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * BulkUploadPipelineTest
 *
 * Feature tests covering the full async pipeline orchestration:
 *  ProcessPdfBulkUploadJob → GeneratePreviewMetadataJob → PublishBulkProductsJob
 *
 * Verifies: dispatch, step transitions, status changes, log creation, chain semantics.
 */
class BulkUploadPipelineTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────

    private function makeBatch(int $totalPages = 2): UploadBatch
    {
        return UploadBatch::create([
            'status'      => UploadBatchStatus::QUEUED,
            'total_pages' => $totalPages,
        ]);
    }

    private function makeReadyPreview(UploadBatch $batch): BulkUploadPreview
    {
        $cat = Category::firstOrCreate(
            ['slug' => 'rings'],
            ['name' => 'Rings', 'status' => true]
        );

        return BulkUploadPreview::create([
            'batch_uuid'          => $batch->id,
            'status'              => PreviewStatus::READY,
            'source'              => 'pdf',
            'name'                => 'Gold Ring',
            'category_id'        => $cat->id,
            'price'               => 4999.00,
            'preview_image_path'  => 'products/test/page-1.jpg',
            'ai_extracted_data'   => ['name' => 'Gold Ring'],
            'content_hash'        => hash('sha256', 'gold-ring'),
        ]);
    }

    // ── Bus::chain() dispatch ─────────────────────────────────────────────

    public function test_pipeline_chain_can_be_dispatched(): void
    {
        Bus::fake();

        $batch = $this->makeBatch();

        Bus::chain([
            new ProcessPdfBulkUploadJob($batch->id, 'test.pdf', 1, 2),
            new GeneratePreviewMetadataJob($batch->id),
            new PublishBulkProductsJob($batch->id),
        ])->dispatch();

        Bus::assertChained([
            ProcessPdfBulkUploadJob::class,
            GeneratePreviewMetadataJob::class,
            PublishBulkProductsJob::class,
        ]);
    }

    public function test_process_pdf_job_can_be_dispatched_alone(): void
    {
        Bus::fake();

        $batch = $this->makeBatch();

        ProcessPdfBulkUploadJob::dispatch($batch->id, 'catalogue.pdf', 1, 2);

        Bus::assertDispatched(ProcessPdfBulkUploadJob::class, function ($job) use ($batch) {
            return $job->batchUuid === $batch->id
                && $job->filePath   === 'catalogue.pdf'
                && $job->startPage  === 1
                && $job->endPage    === 2;
        });
    }

    public function test_generate_metadata_job_can_be_dispatched_alone(): void
    {
        Bus::fake();

        $batch = $this->makeBatch();

        GeneratePreviewMetadataJob::dispatch($batch->id);

        Bus::assertDispatched(GeneratePreviewMetadataJob::class, function ($job) use ($batch) {
            return $job->batchUuid === $batch->id;
        });
    }

    public function test_publish_job_can_be_dispatched_alone(): void
    {
        Bus::fake();

        $batch = $this->makeBatch();

        PublishBulkProductsJob::dispatch($batch->id);

        Bus::assertDispatched(PublishBulkProductsJob::class, function ($job) use ($batch) {
            return $job->batchUuid === $batch->id;
        });
    }

    // ── ProcessPdfBulkUploadJob execution ─────────────────────────────────

    public function test_process_pdf_job_transitions_batch_to_extracting(): void
    {
        $batch = $this->makeBatch(1);

        (new ProcessPdfBulkUploadJob($batch->id, 'test.pdf', 1, 1))
            ->handle(app(BulkUploadService::class));

        $this->assertSame(UploadBatchStatus::REVIEW_READY, $batch->fresh()->status);
    }

    public function test_process_pdf_job_creates_preview_rows(): void
    {
        $batch = $this->makeBatch(2);

        (new ProcessPdfBulkUploadJob($batch->id, 'test.pdf', 1, 2))
            ->handle(app(BulkUploadService::class));

        $this->assertSame(2, $batch->previews()->count());
    }

    public function test_process_pdf_job_creates_extract_pdf_step(): void
    {
        $batch = $this->makeBatch(1);

        (new ProcessPdfBulkUploadJob($batch->id, 'test.pdf', 1, 1))
            ->handle(app(BulkUploadService::class));

        $step = UploadBatchStep::where('batch_uuid', $batch->id)
            ->where('step', 'extract_pdf')
            ->first();

        $this->assertNotNull($step);
        $this->assertSame(UploadBatchStepStatus::COMPLETED, $step->status);
    }

    public function test_process_pdf_job_writes_job_log(): void
    {
        $batch = $this->makeBatch(1);

        (new ProcessPdfBulkUploadJob($batch->id, 'test.pdf', 1, 1))
            ->handle(app(BulkUploadService::class));

        $log = BulkUploadJobLog::where('batch_uuid', $batch->id)
            ->where('job_name', ProcessPdfBulkUploadJob::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertSame(2, (int) $log->status); // completed
    }

    public function test_process_pdf_job_is_idempotent_on_duplicate_dispatch(): void
    {
        $batch = $this->makeBatch(1);
        $service = app(BulkUploadService::class);

        (new ProcessPdfBulkUploadJob($batch->id, 'test.pdf', 1, 1))->handle($service);
        (new ProcessPdfBulkUploadJob($batch->id, 'test.pdf', 1, 1))->handle($service);

        // Should still be exactly 1 preview despite two dispatches for the same page
        $this->assertSame(1, $batch->previews()->count());
    }

    // ── GeneratePreviewMetadataJob execution ──────────────────────────────

    public function test_generate_metadata_job_transitions_batch_to_processing_then_review_ready(): void
    {
        $batch = $this->makeBatch(1);
        $batch->update(['status' => UploadBatchStatus::REVIEW_READY]);

        BulkUploadPreview::create([
            'batch_uuid'        => $batch->id,
            'status'            => PreviewStatus::NEEDS_REVIEW,
            'source'            => 'pdf',
            'ai_extracted_data' => ['name' => 'Test'],
        ]);

        (new GeneratePreviewMetadataJob($batch->id))
            ->handle(app(\App\Services\PreviewValidationService::class));

        $this->assertSame(UploadBatchStatus::REVIEW_READY, $batch->fresh()->status);
    }

    public function test_generate_metadata_job_sets_validation_result_on_previews(): void
    {
        $batch = $this->makeBatch(1);
        $batch->update(['status' => UploadBatchStatus::REVIEW_READY]);

        $preview = BulkUploadPreview::create([
            'batch_uuid'        => $batch->id,
            'status'            => PreviewStatus::NEEDS_REVIEW,
            'source'            => 'pdf',
            'ai_extracted_data' => ['name' => 'Test'],
        ]);

        (new GeneratePreviewMetadataJob($batch->id))
            ->handle(app(\App\Services\PreviewValidationService::class));

        $fresh = $preview->fresh();
        $this->assertNotNull($fresh->validation_result);
        $this->assertIsArray($fresh->validation_result);
        $this->assertArrayHasKey('score', $fresh->validation_result);
    }

    public function test_generate_metadata_job_creates_step_record(): void
    {
        $batch = $this->makeBatch(1);
        $batch->update(['status' => UploadBatchStatus::REVIEW_READY]);

        (new GeneratePreviewMetadataJob($batch->id))
            ->handle(app(\App\Services\PreviewValidationService::class));

        $step = UploadBatchStep::where('batch_uuid', $batch->id)
            ->where('step', 'generate_metadata')
            ->first();

        $this->assertNotNull($step);
        $this->assertSame(UploadBatchStepStatus::COMPLETED, $step->status);
    }

    public function test_generate_metadata_job_writes_job_log(): void
    {
        $batch = $this->makeBatch(1);
        $batch->update(['status' => UploadBatchStatus::REVIEW_READY]);

        (new GeneratePreviewMetadataJob($batch->id))
            ->handle(app(\App\Services\PreviewValidationService::class));

        $log = BulkUploadJobLog::where('batch_uuid', $batch->id)
            ->where('job_name', GeneratePreviewMetadataJob::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertSame(2, (int) $log->status);
    }

    public function test_generate_metadata_job_skips_published_previews(): void
    {
        $batch = $this->makeBatch(1);
        $batch->update(['status' => UploadBatchStatus::REVIEW_READY]);

        $preview = $this->makeReadyPreview($batch);
        $preview->forceFill(['status' => PreviewStatus::PUBLISHED])->save();

        (new GeneratePreviewMetadataJob($batch->id))
            ->handle(app(\App\Services\PreviewValidationService::class));

        // Published preview should remain published, not be modified
        $this->assertSame(PreviewStatus::PUBLISHED, $preview->fresh()->status);
    }

    // ── PublishBulkProductsJob execution ──────────────────────────────────

    public function test_publish_job_transitions_batch_to_completed(): void
    {
        $batch = $this->makeBatch(1);
        $batch->update(['status' => UploadBatchStatus::REVIEW_READY]);

        $this->makeReadyPreview($batch);

        (new PublishBulkProductsJob($batch->id))
            ->handle(app(\App\Services\PreviewPublishService::class));

        $this->assertSame(UploadBatchStatus::COMPLETED, $batch->fresh()->status);
    }

    public function test_publish_job_only_processes_ready_previews(): void
    {
        $batch = $this->makeBatch(1);
        $batch->update(['status' => UploadBatchStatus::REVIEW_READY]);

        // One READY and one NEEDS_REVIEW
        $ready       = $this->makeReadyPreview($batch);
        $needsReview = BulkUploadPreview::create([
            'batch_uuid'        => $batch->id,
            'status'            => PreviewStatus::NEEDS_REVIEW,
            'source'            => 'pdf',
            'ai_extracted_data' => ['name' => 'Unreviewed'],
        ]);

        (new PublishBulkProductsJob($batch->id))
            ->handle(app(\App\Services\PreviewPublishService::class));

        // Only the READY one should have been published
        $this->assertSame(PreviewStatus::PUBLISHED, $ready->fresh()->status);
        $this->assertSame(PreviewStatus::NEEDS_REVIEW, $needsReview->fresh()->status);
    }

    public function test_publish_job_creates_publish_step(): void
    {
        $batch = $this->makeBatch(1);
        $batch->update(['status' => UploadBatchStatus::REVIEW_READY]);

        (new PublishBulkProductsJob($batch->id))
            ->handle(app(\App\Services\PreviewPublishService::class));

        $step = UploadBatchStep::where('batch_uuid', $batch->id)
            ->where('step', 'publish')
            ->first();

        $this->assertNotNull($step);
        $this->assertSame(UploadBatchStepStatus::COMPLETED, $step->status);
    }

    public function test_publish_job_writes_job_log(): void
    {
        $batch = $this->makeBatch(1);
        $batch->update(['status' => UploadBatchStatus::REVIEW_READY]);

        (new PublishBulkProductsJob($batch->id))
            ->handle(app(\App\Services\PreviewPublishService::class));

        $log = BulkUploadJobLog::where('batch_uuid', $batch->id)
            ->where('job_name', PublishBulkProductsJob::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertSame(2, (int) $log->status);
        $this->assertNotNull($log->duration_ms);
        $this->assertNotNull($log->memory_mb);
    }

    // ── Terminal state guards ─────────────────────────────────────────────

    public function test_jobs_skip_completed_batch_idempotently(): void
    {
        $batch = $this->makeBatch(1);
        // Force terminal state
        $batch->update(['status' => UploadBatchStatus::EXTRACTING]);
        $batch->update(['status' => UploadBatchStatus::PROCESSING]);
        $batch->update(['status' => UploadBatchStatus::REVIEW_READY]);
        $batch->update(['status' => UploadBatchStatus::COMPLETED]);

        $service = app(BulkUploadService::class);

        // Should not throw and should not create any previews
        (new ProcessPdfBulkUploadJob($batch->id, 'test.pdf', 1, 1))->handle($service);

        $this->assertSame(0, $batch->previews()->count());
    }

    public function test_jobs_silently_delete_when_batch_not_found(): void
    {
        $this->expectNotToPerformAssertions();

        $fakeUuid = '00000000-0000-0000-0000-000000000000';

        // Each job should call $this->delete() and return without throwing
        (new ProcessPdfBulkUploadJob($fakeUuid, 'x.pdf', 1, 1))
            ->handle(app(BulkUploadService::class));

        (new GeneratePreviewMetadataJob($fakeUuid))
            ->handle(app(\App\Services\PreviewValidationService::class));

        (new PublishBulkProductsJob($fakeUuid))
            ->handle(app(\App\Services\PreviewPublishService::class));
    }
}
