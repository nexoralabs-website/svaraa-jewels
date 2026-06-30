<?php

namespace Tests\Feature;

use App\Enums\PreviewStatus;
use App\Enums\UploadBatchStatus;
use App\Livewire\BulkUploadPreviewGrid;
use App\Models\BulkUploadPreview;
use App\Models\Category;
use App\Models\UploadBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * BulkUploadInlineEditTest
 *
 * Tests inline editing behaviour of BulkUploadPreviewGrid:
 * edit persistence, optimistic locking, resetToAi, markReady validation.
 */
class BulkUploadInlineEditTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function makeBatch(): UploadBatch
    {
        return UploadBatch::create([
            'status'      => UploadBatchStatus::REVIEW_READY,
            'total_pages' => 1,
        ]);
    }

    private function makePreview(UploadBatch $batch, array $overrides = []): BulkUploadPreview
    {
        $cat = Category::firstOrCreate(
            ['slug' => 'rings'],
            ['name' => 'Rings', 'status' => true]
        );

        return BulkUploadPreview::create(array_merge([
            'batch_uuid'         => $batch->id,
            'status'             => PreviewStatus::NEEDS_REVIEW,
            'source'             => 'pdf',
            'name'               => 'AI Product Name',
            'category_id'        => $cat->id,
            'price'              => 1999.00,
            'stock'              => 2,
            'preview_image_path' => 'products/test/page-1.jpg',
            'ai_extracted_data'  => [
                'name'               => 'AI Product Name',
                'price'              => 1999.00,
                'category_id'        => $cat->id,
                'preview_image_path' => 'products/test/page-1.jpg',
            ],
            'content_hash'  => hash('sha256', uniqid()),
            'edited_fields' => [],
            'edited_count'  => 0,
            'version'       => 1,
        ], $overrides));
    }

    // ── startEdit() ───────────────────────────────────────────────────────

    public function test_start_edit_populates_edit_values(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch);

        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->call('startEdit', $preview->id)
            ->assertSet("editValues.{$preview->id}.name", 'AI Product Name');
    }

    public function test_start_edit_captures_version(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch);

        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->call('startEdit', $preview->id)
            ->assertSet("editVersions.{$preview->id}", 1);
    }

    // ── saveRow() — field persistence ────────────────────────────────────

    public function test_save_row_persists_name_change(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch);

        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->call('startEdit', $preview->id)
            ->set("editValues.{$preview->id}.name", 'Updated Name')
            ->call('saveRow', $preview->id);

        $this->assertSame('Updated Name', $preview->fresh()->name);
    }

    public function test_save_row_persists_price_change(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch);

        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->call('startEdit', $preview->id)
            ->set("editValues.{$preview->id}.price", 4999.00)
            ->call('saveRow', $preview->id);

        $this->assertSame(4999.00, (float) $preview->fresh()->price);
    }

    public function test_save_row_persists_stock_change(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch);

        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->call('startEdit', $preview->id)
            ->set("editValues.{$preview->id}.stock", 10)
            ->call('saveRow', $preview->id);

        $this->assertSame(10, (int) $preview->fresh()->stock);
    }

    public function test_save_row_increments_edited_count(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch);

        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->call('startEdit', $preview->id)
            ->set("editValues.{$preview->id}.name", 'Changed Name')
            ->call('saveRow', $preview->id);

        $this->assertGreaterThan(0, $preview->fresh()->edited_count);
    }

    public function test_save_row_increments_version(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch);
        $vBefore = $preview->version;

        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->call('startEdit', $preview->id)
            ->set("editValues.{$preview->id}.name", 'New Name')
            ->call('saveRow', $preview->id);

        $this->assertGreaterThan($vBefore, $preview->fresh()->version);
    }

    public function test_save_row_clears_edit_state(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch);

        $component = Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->call('startEdit', $preview->id)
            ->set("editValues.{$preview->id}.name", 'Changed')
            ->call('saveRow', $preview->id);

        // editValues for this id should be cleared
        $this->assertArrayNotHasKey($preview->id, $component->get('editValues'));
    }

    public function test_save_row_updates_validation_result(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch);

        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->call('startEdit', $preview->id)
            ->set("editValues.{$preview->id}.name", 'Valid Updated Name')
            ->call('saveRow', $preview->id);

        $this->assertNotNull($preview->fresh()->validation_result);
    }

    // ── Optimistic locking ────────────────────────────────────────────────

    public function test_save_row_detects_version_conflict(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch);

        $component = Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->call('startEdit', $preview->id);

        // Simulate concurrent modification — version bumped externally
        $preview->forceFill(['version' => 99])->save();

        $component
            ->set("editValues.{$preview->id}.name", 'Should conflict')
            ->call('saveRow', $preview->id);

        // Row should NOT have been updated because version mismatch
        $this->assertNotSame('Should conflict', $preview->fresh()->name);
    }

    public function test_conflict_event_dispatched_on_version_mismatch(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch);

        $component = Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->call('startEdit', $preview->id);

        $preview->forceFill(['version' => 99])->save();

        $component
            ->set("editValues.{$preview->id}.name", 'Conflict name')
            ->call('saveRow', $preview->id)
            ->assertDispatched('conflict-warning');
    }

    // ── resetToAi() ───────────────────────────────────────────────────────

    public function test_reset_to_ai_restores_original_values(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch);

        // Make an edit
        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->call('startEdit', $preview->id)
            ->set("editValues.{$preview->id}.name", 'Manually Changed Name')
            ->call('saveRow', $preview->id);

        // Reset to AI
        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->call('resetToAi', $preview->id);

        $fresh = $preview->fresh();
        $this->assertSame('AI Product Name', $fresh->name);
        $this->assertSame(0, $fresh->edited_count);
        $this->assertEmpty($fresh->edited_fields);
    }

    public function test_reset_to_ai_increments_version(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch);
        $vBefore = $preview->version;

        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->call('resetToAi', $preview->id);

        $this->assertGreaterThan($vBefore, $preview->fresh()->version);
    }

    // ── markReady() ───────────────────────────────────────────────────────

    public function test_mark_ready_succeeds_when_all_fields_valid(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch);

        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->call('markReady', $preview->id);

        $this->assertSame(PreviewStatus::READY, $preview->fresh()->status);
    }

    public function test_mark_ready_fails_when_blocking_errors_present(): void
    {
        $batch   = $this->makeBatch();

        // Preview missing name, category, price, image — all blocking
        $preview = BulkUploadPreview::create([
            'batch_uuid'        => $batch->id,
            'status'            => PreviewStatus::NEEDS_REVIEW,
            'source'            => 'pdf',
            'ai_extracted_data' => [],
        ]);

        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->call('markReady', $preview->id);

        // Should stay at NEEDS_REVIEW (or DRAFT), never become READY
        $this->assertNotSame(PreviewStatus::READY, $preview->fresh()->status);
    }

    public function test_mark_ready_sets_reviewed_at(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch);

        $this->assertNull($preview->reviewed_at);

        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->call('markReady', $preview->id);

        $this->assertNotNull($preview->fresh()->reviewed_at);
    }

    // ── duplicateRow() ────────────────────────────────────────────────────

    public function test_duplicate_row_creates_new_preview(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch);

        $beforeCount = BulkUploadPreview::count();

        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->call('duplicateRow', $preview->id);

        $this->assertSame($beforeCount + 1, BulkUploadPreview::count());
    }

    public function test_duplicate_row_resets_publication_state(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch);

        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->call('duplicateRow', $preview->id);

        $clone = BulkUploadPreview::where('id', '!=', $preview->id)
            ->where('batch_uuid', $batch->id)
            ->latest()
            ->first();

        $this->assertNotNull($clone);
        $this->assertNull($clone->published_product_id);
        $this->assertSame(PreviewStatus::NEEDS_REVIEW, $clone->status);
    }

    // ── deleteRow() ───────────────────────────────────────────────────────

    public function test_delete_row_soft_deletes(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch);

        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->call('deleteRow', $preview->id);

        $this->assertNull(BulkUploadPreview::find($preview->id));
        $this->assertNotNull(BulkUploadPreview::withTrashed()->find($preview->id));
    }

    public function test_delete_row_removes_from_selection(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch);

        $component = Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->set('selectedIds', [$preview->id])
            ->call('deleteRow', $preview->id);

        $this->assertNotContains($preview->id, $component->get('selectedIds'));
    }

    // ── Compare modal ─────────────────────────────────────────────────────

    public function test_open_compare_modal_sets_ai_and_edited_data(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch, ['name' => 'Human Edited', 'edited_count' => 1]);

        $component = Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->call('openCompareModal', $preview->id);

        $this->assertTrue($component->get('showCompareModal'));
        $this->assertSame($preview->id, $component->get('comparePreviewId'));
        $this->assertNotEmpty($component->get('compareAi'));
    }

    public function test_close_compare_modal_clears_state(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch);

        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->call('openCompareModal', $preview->id)
            ->call('closeCompareModal')
            ->assertSet('showCompareModal', false)
            ->assertSet('comparePreviewId', null);
    }

    // ── Regenerate descriptions ───────────────────────────────────────────

    public function test_regenerate_descriptions_updates_description(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch, ['name' => 'Gold Jhumka', 'description' => 'Old description']);

        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->set('selectedIds', [$preview->id])
            ->call('regenerateDescriptions');

        $fresh = $preview->fresh();
        $this->assertNotEmpty($fresh->description);
        // Check processing_metadata was updated
        $this->assertArrayHasKey('description_regenerated_at', $fresh->processing_metadata ?? []);
    }
}
