<?php

namespace Tests\Unit;

use App\Enums\PreviewStatus;
use App\Enums\UploadBatchStatus;
use App\Enums\UploadBatchStepStatus;
use App\Jobs\PublishBulkProductsJob;
use App\Models\BulkUploadJobLog;
use App\Models\BulkUploadPreview;
use App\Models\Category;
use App\Models\UploadBatch;
use App\Models\UploadBatchStep;
use App\Services\PreviewPublishService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for PublishBulkProductsJob.
 *
 * Covers: READY-only processing, batch completion, step tracking,
 * metrics logging, failure isolation, idempotency guards.
 */
class PublishBulkProductsJobTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────

    private function makeBatch(UploadBatchStatus $status = UploadBatchStatus::REVIEW_READY): UploadBatch
    {
        $batch = UploadBatch::create([
            'status'      => UploadBatchStatus::QUEUED,
            'total_pages' => 1,
        ]);

        $transitions = [
            UploadBatchStatus::EXTRACTING->value   => [UploadBatchStatus::EXTRACTING],
            UploadBatchStatus::PROCESSING->value   => [UploadBatchStatus::EXTRACTING, UploadBatchStatus::PROCESSING],
            UploadBatchStatus::REVIEW_READY->value => [UploadBatchStatus::EXTRACTING, UploadBatchStatus::PROCESSING, UploadBatchStatus::REVIEW_READY],
            UploadBatchStatus::COMPLETED->value    => [UploadBatchStatus::EXTRACTING, UploadBatchStatus::PROCESSING, UploadBatchStatus::REVIEW_READY, UploadBatchStatus::COMPLETED],
        ];

        foreach ($transitions[$status->value] ?? [] as $s) {
            $batch->update(['status' => $s]);
        }

        return $batch->fresh();
    }

    private function makeReadyPreview(UploadBatch $batch): BulkUploadPreview
    {
        $cat = Category::firstOrCreate(
            ['slug' => 'rings'],
            ['name' => 'Rings', 'status' => true]
        );

        return BulkUploadPreview::create([
            'batch_uuid'         => $batch->id,
            'status'             => PreviewStatus::READY,
            'source'             => 'pdf',
            'name'               => 'Gold Ring ' . uniqid(),
            'category_id'        => $cat->id,
            'price'              => 4999.00,
            'preview_image_path' => 'products/' . $batch->id . '/page-1.jpg',
            'ai_extracted_data'  => ['name' => 'Gold Ring'],
            'content_hash'       => hash('sha256', uniqid()),
        ]);
    }

    private function dispatch(UploadBatch $batch): void
    {
        (new PublishBulkProductsJob($batch->id))
            ->handle(app(PreviewPublishService::class));
    }

    // ── Batch status transitions ──────────────────────────────────────────

    public function test_transitions_batch_to_completed(): void
    {
        $batch = $this->makeBatch();

        $this->dispatch($batch);

        $this->assertSame(UploadBatchStatus::COMPLETED, $batch->fresh()->status);
    }

    public function test_batch_completed_even_when_no_ready_previews(): void
    {
        $batch = $this->makeBatch();

        // No previews at all — should still complete
        $this->dispatch($batch);

        $this->assertSame(UploadBatchStatus::COMPLETED, $batch->fresh()->status);
    }

    public function test_skips_terminal_completed_batch(): void
    {
        $batch = $this->makeBatch(UploadBatchStatus::COMPLETED);

        $this->dispatch($batch);

        // No step should have been created
        $this->assertNull(
            UploadBatchStep::where('batch_uuid', $batch->id)->where('step', 'publish')->first()
        );
    }

    public function test_silently_returns_when_batch_not_found(): void
    {
        $this->expectNotToPerformAssertions();

        (new PublishBulkProductsJob('00000000-0000-0000-0000-000000000000'))
            ->handle(app(PreviewPublishService::class));
    }

    // ── READY-only processing ─────────────────────────────────────────────

    public function test_publishes_only_ready_previews(): void
    {
        $batch = $this->makeBatch();

        $ready       = $this->makeReadyPreview($batch);
        $needsReview = BulkUploadPreview::create([
            'batch_uuid'        => $batch->id,
            'status'            => PreviewStatus::NEEDS_REVIEW,
            'source'            => 'pdf',
            'ai_extracted_data' => ['name' => 'Unreviewed'],
        ]);

        $this->dispatch($batch);

        $this->assertSame(PreviewStatus::PUBLISHED, $ready->fresh()->status);
        $this->assertSame(PreviewStatus::NEEDS_REVIEW, $needsReview->fresh()->status);
    }

    public function test_does_not_republish_already_published_previews(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makeReadyPreview($batch);

        // First publish
        $this->dispatch($batch);
        $productId = $preview->fresh()->published_product_id;

        // Reopen batch for second publish attempt
        $batch2 = UploadBatch::create([
            'status'      => UploadBatchStatus::QUEUED,
            'total_pages' => 1,
        ]);
        $batch2->update(['status' => UploadBatchStatus::EXTRACTING]);
        $batch2->update(['status' => UploadBatchStatus::PROCESSING]);
        $batch2->update(['status' => UploadBatchStatus::REVIEW_READY]);

        $preview->forceFill(['batch_uuid' => $batch2->id])->save();

        // publishBatch via publishSingle will skip already-published (published_product_id set)
        (new PublishBulkProductsJob($batch2->id))
            ->handle(app(PreviewPublishService::class));

        // published_product_id must not have changed
        $this->assertSame($productId, $preview->fresh()->published_product_id);
    }

    // ── Step management ───────────────────────────────────────────────────

    public function test_creates_publish_step_record(): void
    {
        $batch = $this->makeBatch();

        $this->dispatch($batch);

        $step = UploadBatchStep::where('batch_uuid', $batch->id)
            ->where('step', 'publish')
            ->first();

        $this->assertNotNull($step);
    }

    public function test_step_is_completed_after_successful_run(): void
    {
        $batch = $this->makeBatch();

        $this->dispatch($batch);

        $step = UploadBatchStep::where('batch_uuid', $batch->id)
            ->where('step', 'publish')->first();

        $this->assertSame(UploadBatchStepStatus::COMPLETED, $step->status);
        $this->assertNotNull($step->finished_at);
        $this->assertGreaterThanOrEqual(0, $step->duration_ms);
    }

    // ── Job log metrics ───────────────────────────────────────────────────

    public function test_writes_completed_job_log(): void
    {
        $batch = $this->makeBatch();

        $this->dispatch($batch);

        $log = BulkUploadJobLog::where('batch_uuid', $batch->id)
            ->where('job_name', PublishBulkProductsJob::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertSame(2, (int) $log->status); // 2 = completed
    }

    public function test_job_log_has_duration_and_memory(): void
    {
        $batch = $this->makeBatch();

        $this->dispatch($batch);

        $log = BulkUploadJobLog::where('batch_uuid', $batch->id)
            ->where('job_name', PublishBulkProductsJob::class)->first();

        $this->assertNotNull($log->duration_ms);
        $this->assertNotNull($log->memory_mb);
        $this->assertGreaterThanOrEqual(0, (float) $log->memory_mb);
    }

    public function test_job_log_has_worker_and_timestamps(): void
    {
        $batch = $this->makeBatch();

        $this->dispatch($batch);

        $log = BulkUploadJobLog::where('batch_uuid', $batch->id)
            ->where('job_name', PublishBulkProductsJob::class)->first();

        $this->assertNotNull($log->worker);
        $this->assertNotNull($log->started_at);
        $this->assertNotNull($log->ended_at);
        $this->assertTrue($log->ended_at->gte($log->started_at));
    }

    // ── Product creation ──────────────────────────────────────────────────

    public function test_publishes_ready_preview_and_creates_product(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makeReadyPreview($batch);

        $this->dispatch($batch);

        $fresh = $preview->fresh();
        $this->assertSame(PreviewStatus::PUBLISHED, $fresh->status);
        $this->assertNotNull($fresh->published_product_id);
        $this->assertNotNull($fresh->published_at);
    }

    public function test_progress_message_contains_published_count(): void
    {
        $batch = $this->makeBatch();
        $this->makeReadyPreview($batch);

        $this->dispatch($batch);

        $this->assertStringContainsString('1 published', $batch->fresh()->progress_message);
    }
}
