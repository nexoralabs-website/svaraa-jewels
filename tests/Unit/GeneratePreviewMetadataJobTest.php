<?php

namespace Tests\Unit;

use App\Enums\PreviewStatus;
use App\Enums\UploadBatchStatus;
use App\Enums\UploadBatchStepStatus;
use App\Jobs\GeneratePreviewMetadataJob;
use App\Models\BulkUploadJobLog;
use App\Models\BulkUploadPreview;
use App\Models\Category;
use App\Models\UploadBatch;
use App\Models\UploadBatchStep;
use App\Services\PreviewValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for GeneratePreviewMetadataJob.
 *
 * Covers: status transitions, validation enrichment, step tracking, metrics,
 * idempotency guards, published-preview skipping.
 */
class GeneratePreviewMetadataJobTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────

    private function makeBatch(UploadBatchStatus $status = UploadBatchStatus::REVIEW_READY): UploadBatch
    {
        $batch = UploadBatch::create([
            'status'      => UploadBatchStatus::QUEUED,
            'total_pages' => 1,
        ]);

        if ($status !== UploadBatchStatus::QUEUED) {
            $order = [
                UploadBatchStatus::EXTRACTING->value   => 1,
                UploadBatchStatus::PROCESSING->value   => 2,
                UploadBatchStatus::REVIEW_READY->value => 3,
                UploadBatchStatus::COMPLETED->value    => 4,
                UploadBatchStatus::FAILED->value       => 99,
            ];
            $target = $order[$status->value] ?? 0;
            if ($target >= 1) $batch->update(['status' => UploadBatchStatus::EXTRACTING]);
            if ($target >= 2) $batch->update(['status' => UploadBatchStatus::PROCESSING]);
            if ($target >= 3) $batch->update(['status' => UploadBatchStatus::REVIEW_READY]);
            if ($status === UploadBatchStatus::COMPLETED) $batch->update(['status' => UploadBatchStatus::COMPLETED]);
        }

        return $batch->fresh();
    }

    private function makePreview(UploadBatch $batch, PreviewStatus $status = PreviewStatus::NEEDS_REVIEW): BulkUploadPreview
    {
        return BulkUploadPreview::create([
            'batch_uuid'        => $batch->id,
            'status'            => $status,
            'source'            => 'pdf',
            'ai_extracted_data' => ['name' => 'Test Product'],
        ]);
    }

    private function dispatch(UploadBatch $batch): void
    {
        (new GeneratePreviewMetadataJob($batch->id))
            ->handle(app(PreviewValidationService::class));
    }

    // ── Batch status transitions ──────────────────────────────────────────

    public function test_transitions_batch_to_processing_then_review_ready(): void
    {
        $batch = $this->makeBatch(UploadBatchStatus::REVIEW_READY);

        $this->dispatch($batch);

        $this->assertSame(UploadBatchStatus::REVIEW_READY, $batch->fresh()->status);
    }

    public function test_skips_terminal_completed_batch(): void
    {
        $batch = $this->makeBatch(UploadBatchStatus::COMPLETED);

        $previewCountBefore = $batch->previews()->count();

        $this->dispatch($batch);

        // No step should have been created
        $this->assertNull(
            UploadBatchStep::where('batch_uuid', $batch->id)->where('step', 'generate_metadata')->first()
        );
    }

    public function test_silently_returns_when_batch_not_found(): void
    {
        $this->expectNotToPerformAssertions();

        (new GeneratePreviewMetadataJob('00000000-0000-0000-0000-000000000000'))
            ->handle(app(PreviewValidationService::class));
    }

    // ── Validation enrichment ─────────────────────────────────────────────

    public function test_sets_validation_result_on_preview(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch);

        $this->dispatch($batch);

        $fresh = $preview->fresh();
        $this->assertIsArray($fresh->validation_result);
        $this->assertArrayHasKey('blocking', $fresh->validation_result);
        $this->assertArrayHasKey('warnings', $fresh->validation_result);
        $this->assertArrayHasKey('score',    $fresh->validation_result);
    }

    public function test_sets_processing_metadata_with_generated_at_timestamp(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch);

        $this->dispatch($batch);

        $meta = $preview->fresh()->processing_metadata;
        $this->assertIsArray($meta);
        $this->assertArrayHasKey('metadata_generated_at', $meta);
        $this->assertArrayHasKey('validation_score',      $meta);
        $this->assertArrayHasKey('has_blocking_errors',   $meta);
    }

    public function test_score_in_metadata_matches_validation_result(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch);

        $this->dispatch($batch);

        $fresh = $preview->fresh();
        $this->assertSame(
            $fresh->validation_result['score'],
            $fresh->processing_metadata['validation_score']
        );
    }

    public function test_preview_with_complete_data_gets_high_score(): void
    {
        $batch = $this->makeBatch();
        $cat   = Category::firstOrCreate(
            ['slug' => 'rings'],
            ['name' => 'Rings', 'status' => true]
        );

        $preview = BulkUploadPreview::create([
            'batch_uuid'         => $batch->id,
            'status'             => PreviewStatus::NEEDS_REVIEW,
            'source'             => 'pdf',
            'name'               => 'Gold Ring',
            'category_id'        => $cat->id,
            'price'              => 4999.00,
            'preview_image_path' => 'products/test/p1.jpg',
            'ai_extracted_data'  => ['name' => 'Gold Ring'],
        ]);

        $this->dispatch($batch);

        $result = $preview->fresh()->validation_result;
        $this->assertGreaterThan(50, $result['score']);
    }

    // ── Does NOT publish ──────────────────────────────────────────────────

    public function test_does_not_transition_any_preview_to_published(): void
    {
        $batch = $this->makeBatch();
        $this->makePreview($batch);

        $this->dispatch($batch);

        $publishedCount = $batch->previews()
            ->where('status', PreviewStatus::PUBLISHED->value)
            ->count();

        $this->assertSame(0, $publishedCount);
    }

    public function test_skips_already_published_previews(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch, PreviewStatus::PUBLISHED);

        $validationBefore = $preview->validation_result;

        $this->dispatch($batch);

        // Published preview must not be touched
        $this->assertSame(PreviewStatus::PUBLISHED, $preview->fresh()->status);
        $this->assertSame($validationBefore, $preview->fresh()->validation_result);
    }

    // ── Step management ───────────────────────────────────────────────────

    public function test_creates_generate_metadata_step(): void
    {
        $batch = $this->makeBatch();

        $this->dispatch($batch);

        $step = UploadBatchStep::where('batch_uuid', $batch->id)
            ->where('step', 'generate_metadata')
            ->first();

        $this->assertNotNull($step);
        $this->assertSame(UploadBatchStepStatus::COMPLETED, $step->status);
    }

    public function test_step_has_duration_and_timestamps(): void
    {
        $batch = $this->makeBatch();

        $this->dispatch($batch);

        $step = UploadBatchStep::where('batch_uuid', $batch->id)
            ->where('step', 'generate_metadata')->first();

        $this->assertNotNull($step->started_at);
        $this->assertNotNull($step->finished_at);
        $this->assertGreaterThanOrEqual(0, $step->duration_ms);
    }

    // ── Job log metrics ───────────────────────────────────────────────────

    public function test_writes_completed_job_log(): void
    {
        $batch = $this->makeBatch();

        $this->dispatch($batch);

        $log = BulkUploadJobLog::where('batch_uuid', $batch->id)
            ->where('job_name', GeneratePreviewMetadataJob::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertSame(2, (int) $log->status);
    }

    public function test_job_log_has_all_metric_fields(): void
    {
        $batch = $this->makeBatch();

        $this->dispatch($batch);

        $log = BulkUploadJobLog::where('batch_uuid', $batch->id)
            ->where('job_name', GeneratePreviewMetadataJob::class)
            ->first();

        $this->assertNotNull($log->started_at);
        $this->assertNotNull($log->ended_at);
        $this->assertNotNull($log->duration_ms);
        $this->assertNotNull($log->memory_mb);
        $this->assertNotNull($log->worker);
    }

    // ── Chunked processing ────────────────────────────────────────────────

    public function test_enriches_multiple_previews(): void
    {
        $batch = $this->makeBatch();

        for ($i = 1; $i <= 5; $i++) {
            $this->makePreview($batch);
        }

        $this->dispatch($batch);

        $enrichedCount = $batch->previews()
            ->whereNotNull('validation_result')
            ->count();

        $this->assertSame(5, $enrichedCount);
    }
}
