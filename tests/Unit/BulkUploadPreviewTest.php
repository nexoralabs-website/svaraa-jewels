<?php

namespace Tests\Unit;

use App\Enums\PreviewStatus;
use App\Enums\UploadBatchStatus;
use App\Models\BulkUploadPreview;
use App\Models\Category;
use App\Models\UploadBatch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class BulkUploadPreviewTest extends TestCase
{
    use RefreshDatabase;

    // ── Factories ─────────────────────────────────────────────────────────

    private function makeBatch(): UploadBatch
    {
        return UploadBatch::create([
            'status' => UploadBatchStatus::REVIEW_READY,
        ]);
    }

    private function makePreview(array $overrides = []): BulkUploadPreview
    {
        $batch = $this->makeBatch();

        return BulkUploadPreview::create(array_merge([
            'batch_uuid'        => $batch->id,
            'source'            => 'pdf',
            'status'            => PreviewStatus::NEEDS_REVIEW,
            'ai_extracted_data' => [
                'name'               => 'AI Name',
                'description'        => 'AI Desc',
                'price'              => 999.00,
                'sku'                => 'AI-SKU-001',
                'slug'               => 'ai-name',
                'preview_image_path' => 'images/ai.jpg',
            ],
        ], $overrides));
    }

    // ── Enum casting ──────────────────────────────────────────────────────

    public function test_status_is_cast_to_preview_status_enum(): void
    {
        $preview = $this->makePreview();
        $this->assertInstanceOf(PreviewStatus::class, $preview->fresh()->status);
        $this->assertSame(PreviewStatus::NEEDS_REVIEW, $preview->fresh()->status);
    }

    // ── Array casts ───────────────────────────────────────────────────────

    public function test_ai_extracted_data_is_cast_to_array(): void
    {
        $preview = $this->makePreview();
        $this->assertIsArray($preview->fresh()->ai_extracted_data);
    }

    public function test_gallery_images_is_cast_to_array(): void
    {
        $preview = $this->makePreview(['gallery_images' => ['img1.jpg', 'img2.jpg']]);
        $this->assertIsArray($preview->fresh()->gallery_images);
        $this->assertCount(2, $preview->fresh()->gallery_images);
    }

    public function test_validation_result_is_cast_to_array(): void
    {
        $preview = $this->makePreview(['validation_result' => ['score' => 80, 'blocking' => []]]);
        $this->assertIsArray($preview->fresh()->validation_result);
    }

    // ── Datetime casts ────────────────────────────────────────────────────

    public function test_reviewed_at_is_cast_to_datetime(): void
    {
        $preview = $this->makePreview(['reviewed_at' => '2026-01-15 12:00:00']);
        $this->assertInstanceOf(Carbon::class, $preview->fresh()->reviewed_at);
    }

    public function test_published_at_is_cast_to_datetime(): void
    {
        $preview = $this->makePreview(['published_at' => '2026-01-15 12:00:00']);
        $this->assertInstanceOf(Carbon::class, $preview->fresh()->published_at);
    }

    // ── Boolean cast ──────────────────────────────────────────────────────

    public function test_is_placeholder_is_cast_to_boolean(): void
    {
        $preview = $this->makePreview(['is_placeholder' => true]);
        $this->assertIsBool($preview->fresh()->is_placeholder);
        $this->assertTrue($preview->fresh()->is_placeholder);
    }

    // ── Relationships ─────────────────────────────────────────────────────

    public function test_batch_relationship(): void
    {
        $preview = $this->makePreview();
        $this->assertInstanceOf(UploadBatch::class, $preview->batch);
    }

    public function test_category_relationship(): void
    {
        $cat     = Category::create(['name' => 'Rings', 'slug' => 'rings', 'status' => true]);
        $preview = $this->makePreview(['category_id' => $cat->id]);
        $this->assertInstanceOf(Category::class, $preview->category);
        $this->assertSame($cat->id, $preview->category->id);
    }

    public function test_publisher_relationship(): void
    {
        $user    = User::factory()->create();
        $preview = $this->makePreview(['user_id' => $user->id]);
        $this->assertInstanceOf(User::class, $preview->publisher);
        $this->assertSame($user->id, $preview->publisher->id);
    }

    // ── isEdited() ────────────────────────────────────────────────────────

    public function test_is_not_edited_by_default(): void
    {
        $preview = new BulkUploadPreview(['edited_count' => 0, 'edited_fields' => []]);
        $this->assertFalse($preview->isEdited());
    }

    public function test_is_edited_when_edited_count_positive(): void
    {
        $preview = new BulkUploadPreview(['edited_count' => 1, 'edited_fields' => []]);
        $this->assertTrue($preview->isEdited());
    }

    public function test_is_edited_when_edited_fields_not_empty(): void
    {
        $preview = new BulkUploadPreview(['edited_count' => 0, 'edited_fields' => ['name']]);
        $this->assertTrue($preview->isEdited());
    }

    // ── hasBlockingErrors() ───────────────────────────────────────────────

    public function test_no_blocking_errors_when_validation_result_is_null(): void
    {
        $preview = new BulkUploadPreview(['validation_result' => null]);
        $this->assertFalse($preview->hasBlockingErrors());
    }

    public function test_no_blocking_errors_when_blocking_array_empty(): void
    {
        $preview = new BulkUploadPreview(['validation_result' => ['blocking' => [], 'score' => 90]]);
        $this->assertFalse($preview->hasBlockingErrors());
    }

    public function test_has_blocking_errors_when_blocking_array_not_empty(): void
    {
        $preview = new BulkUploadPreview([
            'validation_result' => ['blocking' => ['price is required'], 'score' => 0],
        ]);
        $this->assertTrue($preview->hasBlockingErrors());
    }

    // ── validationScore() ─────────────────────────────────────────────────

    public function test_validation_score_returns_zero_when_no_result(): void
    {
        $preview = new BulkUploadPreview(['validation_result' => null]);
        $this->assertSame(0, $preview->validationScore());
    }

    public function test_validation_score_reads_score_key(): void
    {
        $preview = new BulkUploadPreview(['validation_result' => ['score' => 85]]);
        $this->assertSame(85, $preview->validationScore());
    }

    // ── markReady() ───────────────────────────────────────────────────────

    public function test_mark_ready_throws_when_blocking_errors_present(): void
    {
        $preview = new BulkUploadPreview([
            'validation_result' => ['blocking' => ['missing price']],
        ]);
        $this->expectException(LogicException::class);
        $preview->markReady();
    }

    public function test_mark_ready_sets_status_and_reviewed_at(): void
    {
        $preview = new BulkUploadPreview([
            'validation_result' => ['blocking' => []],
        ]);

        $preview->markReady();

        $this->assertSame(PreviewStatus::READY, $preview->status);
        $this->assertInstanceOf(Carbon::class, $preview->reviewed_at);
    }

    // ── markPublished() ───────────────────────────────────────────────────

    public function test_mark_published_sets_status_and_timestamps(): void
    {
        $preview = new BulkUploadPreview(['published_product_id' => null]);
        $preview->markPublished(42);

        $this->assertSame(PreviewStatus::PUBLISHED, $preview->status);
        $this->assertSame(42, $preview->published_product_id);
        $this->assertInstanceOf(Carbon::class, $preview->published_at);
    }

    public function test_mark_published_throws_when_already_published(): void
    {
        $preview = new BulkUploadPreview(['published_product_id' => 10]);
        $this->expectException(LogicException::class);
        $preview->markPublished(20);
    }

    // ── resetToAi() ───────────────────────────────────────────────────────

    public function test_reset_to_ai_restores_fields_from_ai_extracted_data(): void
    {
        $preview = new BulkUploadPreview([
            'name'                => 'Edited Name',
            'description'         => 'Edited Desc',
            'price'               => 500.00,
            'sku'                 => 'EDITED-SKU',
            'slug'                => 'edited-name',
            'preview_image_path'  => 'images/edited.jpg',
            'edited_count'        => 3,
            'edited_fields'       => ['name', 'price'],
            'ai_extracted_data'   => [
                'name'               => 'AI Name',
                'description'        => 'AI Desc',
                'price'              => 999.00,
                'sku'                => 'AI-SKU-001',
                'slug'               => 'ai-name',
                'preview_image_path' => 'images/ai.jpg',
            ],
        ]);

        $preview->resetToAi();

        $this->assertSame('AI Name', $preview->name);
        $this->assertSame('AI Desc', $preview->description);
        $this->assertEquals(999.00, (float) $preview->price);
        $this->assertSame('AI-SKU-001', $preview->sku);
        $this->assertSame('ai-name', $preview->slug);
        $this->assertSame('images/ai.jpg', $preview->preview_image_path);
        $this->assertSame(0, $preview->edited_count);
        $this->assertEmpty($preview->edited_fields);
    }

    // ── incrementVersion() ────────────────────────────────────────────────

    public function test_increment_version_increases_by_one(): void
    {
        $preview = new BulkUploadPreview(['version' => 1]);
        $preview->incrementVersion();
        $this->assertSame(2, $preview->version);
    }

    public function test_increment_version_is_chainable(): void
    {
        $preview = new BulkUploadPreview(['version' => 5]);
        $result  = $preview->incrementVersion();
        $this->assertSame($preview, $result);
        $this->assertSame(6, $preview->version);
    }

    public function test_increment_version_persists_across_saves(): void
    {
        $preview = $this->makePreview(['version' => 1]);
        $preview->incrementVersion()->save();

        $this->assertSame(2, $preview->fresh()->version);
    }

    // ── Query scopes ──────────────────────────────────────────────────────

    public function test_scope_ready(): void
    {
        $this->makePreview(['status' => PreviewStatus::READY]);
        $this->makePreview(['status' => PreviewStatus::NEEDS_REVIEW]);

        $this->assertCount(1, BulkUploadPreview::ready()->get());
    }

    public function test_scope_needs_review(): void
    {
        $this->makePreview(['status' => PreviewStatus::NEEDS_REVIEW]);
        $this->makePreview(['status' => PreviewStatus::DRAFT]);

        $this->assertCount(1, BulkUploadPreview::needsReview()->get());
    }

    public function test_scope_published(): void
    {
        $this->makePreview(['status' => PreviewStatus::PUBLISHED]);
        $this->makePreview(['status' => PreviewStatus::READY]);

        $this->assertCount(1, BulkUploadPreview::published()->get());
    }

    // ── SoftDeletes ───────────────────────────────────────────────────────

    public function test_soft_delete_excludes_from_default_query(): void
    {
        $preview = $this->makePreview();
        $id      = $preview->id;
        $preview->delete();

        $this->assertNull(BulkUploadPreview::find($id));
        $this->assertNotNull(BulkUploadPreview::withTrashed()->find($id));
    }
}
