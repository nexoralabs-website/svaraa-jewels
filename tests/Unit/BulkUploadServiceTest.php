<?php

namespace Tests\Unit;

use App\Enums\PreviewStatus;
use App\Enums\UploadBatchStatus;
use App\Models\BulkUploadPreview;
use App\Models\Category;
use App\Models\UploadBatch;
use App\Models\User;
use App\Services\BulkUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkUploadServiceTest extends TestCase
{
    use RefreshDatabase;

    private BulkUploadService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BulkUploadService();
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function makeCategory(): Category
    {
        return Category::create([
            'name'   => 'Rings',
            'slug'   => 'rings',
            'status' => true,
        ]);
    }

    private function basePreviewData(UploadBatch $batch, array $overrides = []): array
    {
        return array_merge([
            'batch_uuid'         => $batch->id,
            'source'             => 'pdf',
            'name'               => 'Gold Ring',
            'category_id'        => null,
            'description'        => 'A beautiful ring.',
            'price'              => 4999.00,
            'sku'                => 'SKU-001',
            'slug'               => 'gold-ring',
            'preview_image_path' => 'images/gold-ring.jpg',
        ], $overrides);
    }

    // ── createBatch() ─────────────────────────────────────────────────────

    public function test_create_batch_with_user(): void
    {
        $user  = User::factory()->create();
        $batch = $this->service->createBatch($user);

        $this->assertInstanceOf(UploadBatch::class, $batch);
        $this->assertSame($user->id, $batch->user_id);
        $this->assertSame(UploadBatchStatus::QUEUED, $batch->status);
        $this->assertSame(0, $batch->total_pages);
        $this->assertSame(0, $batch->processed_pages);
    }

    public function test_create_batch_without_user(): void
    {
        $batch = $this->service->createBatch(null);

        $this->assertNull($batch->user_id);
        $this->assertSame(UploadBatchStatus::QUEUED, $batch->status);
    }

    public function test_create_batch_stores_metadata(): void
    {
        $batch = $this->service->createBatch(null, ['source_file' => 'catalogue.pdf']);

        $this->assertIsArray($batch->fresh()->metadata);
        $this->assertSame('catalogue.pdf', $batch->fresh()->metadata['source_file']);
    }

    public function test_create_batch_persists_to_database(): void
    {
        $batch = $this->service->createBatch(null);

        $this->assertDatabaseHas('upload_batches', ['id' => $batch->id]);
    }

    public function test_create_batch_generates_uuid(): void
    {
        $b1 = $this->service->createBatch(null);
        $b2 = $this->service->createBatch(null);

        $this->assertNotSame($b1->id, $b2->id);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f-]{36}$/',
            $b1->id
        );
    }

    // ── createPreview() ───────────────────────────────────────────────────

    public function test_create_preview_sets_needs_review_status(): void
    {
        $batch   = $this->service->createBatch(null);
        $preview = $this->service->createPreview($batch, $this->basePreviewData($batch));

        $this->assertSame(PreviewStatus::NEEDS_REVIEW, $preview->fresh()->status);
    }

    public function test_create_preview_snapshots_ai_extracted_data(): void
    {
        $batch   = $this->service->createBatch(null);
        $preview = $this->service->createPreview($batch, $this->basePreviewData($batch));

        $ai = $preview->fresh()->ai_extracted_data;

        $this->assertIsArray($ai);
        $this->assertSame('Gold Ring', $ai['name']);
        $this->assertSame('SKU-001', $ai['sku']);
        $this->assertSame('images/gold-ring.jpg', $ai['preview_image_path']);
    }

    public function test_create_preview_generates_content_hash(): void
    {
        $batch   = $this->service->createBatch(null);
        $preview = $this->service->createPreview($batch, $this->basePreviewData($batch));

        $this->assertNotEmpty($preview->fresh()->content_hash);
        $this->assertSame(64, strlen($preview->fresh()->content_hash)); // sha256 hex
    }

    public function test_create_preview_initialises_edit_tracking(): void
    {
        $batch   = $this->service->createBatch(null);
        $preview = $this->service->createPreview($batch, $this->basePreviewData($batch));

        $fresh = $preview->fresh();
        $this->assertSame(0, $fresh->edited_count);
        $this->assertEmpty($fresh->edited_fields);
        $this->assertSame(1, $fresh->version);
    }

    public function test_create_preview_forces_status_to_needs_review(): void
    {
        $batch = $this->service->createBatch(null);
        // Even if caller passes READY, service should override to NEEDS_REVIEW
        $preview = $this->service->createPreview(
            $batch,
            $this->basePreviewData($batch, ['status' => PreviewStatus::READY])
        );

        $this->assertSame(PreviewStatus::NEEDS_REVIEW, $preview->fresh()->status);
    }

    // ── updatePreview() ───────────────────────────────────────────────────

    public function test_update_preview_changes_fields(): void
    {
        $batch   = $this->service->createBatch(null);
        $preview = $this->service->createPreview($batch, $this->basePreviewData($batch));

        $this->service->updatePreview($preview, ['name' => 'Updated Ring']);

        $this->assertSame('Updated Ring', $preview->fresh()->name);
    }

    public function test_update_preview_tracks_edited_fields(): void
    {
        $batch   = $this->service->createBatch(null);
        $preview = $this->service->createPreview($batch, $this->basePreviewData($batch));

        $this->service->updatePreview($preview, ['name' => 'Edited Ring', 'price' => 5999.00]);

        $fresh = $preview->fresh();
        $this->assertContains('name', $fresh->edited_fields);
        $this->assertContains('price', $fresh->edited_fields);
    }

    public function test_update_preview_increments_edited_count(): void
    {
        $batch   = $this->service->createBatch(null);
        $preview = $this->service->createPreview($batch, $this->basePreviewData($batch));

        $this->service->updatePreview($preview, ['name' => 'First Edit']);
        $this->service->updatePreview($preview->fresh(), ['price' => 1000.00]);

        $this->assertSame(2, $preview->fresh()->edited_count);
    }

    public function test_update_preview_increments_version(): void
    {
        $batch   = $this->service->createBatch(null);
        $preview = $this->service->createPreview($batch, $this->basePreviewData($batch));

        $this->assertSame(1, $preview->version);

        $this->service->updatePreview($preview, ['name' => 'v2']);

        $this->assertSame(2, $preview->fresh()->version);
    }

    public function test_update_preview_does_not_increment_edited_count_for_same_value(): void
    {
        $batch   = $this->service->createBatch(null);
        $preview = $this->service->createPreview($batch, $this->basePreviewData($batch));

        // "Gold Ring" is the AI value — sending the same value should not count as an edit
        $this->service->updatePreview($preview, ['name' => 'Gold Ring']);

        $this->assertSame(0, $preview->fresh()->edited_count);
    }

    // ── duplicatePreview() ────────────────────────────────────────────────

    public function test_duplicate_preview_creates_new_row(): void
    {
        $batch   = $this->service->createBatch(null);
        $preview = $this->service->createPreview($batch, $this->basePreviewData($batch));
        $clone   = $this->service->duplicatePreview($preview);

        $this->assertNotSame($preview->id, $clone->id);
        $this->assertSame($preview->name, $clone->name);
    }

    public function test_duplicate_preview_clears_publication_fields(): void
    {
        $batch   = $this->service->createBatch(null);
        $preview = $this->service->createPreview($batch, $this->basePreviewData($batch));

        // Manually set published fields to simulate a published preview
        $preview->forceFill([
            'published_product_id' => 99,
            'reviewed_at'          => now(),
            'published_at'         => now(),
            'status'               => PreviewStatus::PUBLISHED,
        ])->save();

        $clone = $this->service->duplicatePreview($preview->fresh());

        $this->assertNull($clone->published_product_id);
        $this->assertNull($clone->reviewed_at);
        $this->assertNull($clone->published_at);
        $this->assertSame(PreviewStatus::NEEDS_REVIEW, $clone->status);
    }

    public function test_duplicate_preview_resets_version_to_one(): void
    {
        $batch   = $this->service->createBatch(null);
        $preview = $this->service->createPreview($batch, $this->basePreviewData($batch));
        $preview->forceFill(['version' => 5])->save();

        $clone = $this->service->duplicatePreview($preview->fresh());

        $this->assertSame(1, $clone->version);
    }

    // ── resetPreview() ────────────────────────────────────────────────────

    public function test_reset_preview_restores_ai_fields(): void
    {
        $batch   = $this->service->createBatch(null);
        $preview = $this->service->createPreview($batch, $this->basePreviewData($batch));

        $this->service->updatePreview($preview, ['name' => 'Manually Changed', 'price' => 1.00]);
        $this->service->resetPreview($preview->fresh());

        $fresh = $preview->fresh();
        $this->assertSame('Gold Ring', $fresh->name);
        $this->assertSame(0, $fresh->edited_count);
        $this->assertEmpty($fresh->edited_fields);
    }

    public function test_reset_preview_increments_version(): void
    {
        $batch   = $this->service->createBatch(null);
        $preview = $this->service->createPreview($batch, $this->basePreviewData($batch));
        $vBefore = $preview->version;

        $this->service->resetPreview($preview);

        $this->assertGreaterThan($vBefore, $preview->fresh()->version);
    }

    // ── deletePreview() ───────────────────────────────────────────────────

    public function test_delete_preview_soft_deletes(): void
    {
        $batch   = $this->service->createBatch(null);
        $preview = $this->service->createPreview($batch, $this->basePreviewData($batch));
        $id      = $preview->id;

        $this->service->deletePreview($preview);

        $this->assertNull(BulkUploadPreview::find($id));
        $this->assertNotNull(BulkUploadPreview::withTrashed()->find($id));
    }

    public function test_delete_preview_does_not_hard_delete(): void
    {
        $batch   = $this->service->createBatch(null);
        $preview = $this->service->createPreview($batch, $this->basePreviewData($batch));

        $this->service->deletePreview($preview);

        $this->assertDatabaseHas('bulk_upload_previews', ['id' => $preview->id]);
    }
}
