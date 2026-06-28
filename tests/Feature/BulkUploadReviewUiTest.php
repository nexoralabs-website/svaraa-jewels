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
 * BulkUploadReviewUiTest
 *
 * Tests the BulkUploadPreviewGrid Livewire component:
 * page render, filters, pagination, bulk actions.
 */
class BulkUploadReviewUiTest extends TestCase
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
            'total_pages' => 3,
        ]);
    }

    private function makePreview(UploadBatch $batch, PreviewStatus $status = PreviewStatus::NEEDS_REVIEW, array $overrides = []): BulkUploadPreview
    {
        $cat = Category::firstOrCreate(
            ['slug' => 'rings'],
            ['name' => 'Rings', 'status' => true]
        );

        return BulkUploadPreview::create(array_merge([
            'batch_uuid'        => $batch->id,
            'status'            => $status,
            'source'            => 'pdf',
            'name'              => 'Test Product ' . uniqid(),
            'category_id'       => $cat->id,
            'price'             => 1999.00,
            'preview_image_path'=> 'extracts/test/page-1.jpg',
            'ai_extracted_data' => ['name' => 'AI Product'],
            'content_hash'      => hash('sha256', uniqid()),
        ], $overrides));
    }

    // ── Component mounts ──────────────────────────────────────────────────

    public function test_component_mounts_without_errors(): void
    {
        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->assertOk();
    }

    public function test_component_mounts_with_batch_uuid(): void
    {
        $batch = $this->makeBatch();

        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class, ['batchUuid' => $batch->id])
            ->assertSet('filterBatch', $batch->id);
    }

    // ── Render / page loads ───────────────────────────────────────────────

    public function test_admin_page_returns_200(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/bulk-upload-products')
            ->assertOk();
    }

    public function test_grid_renders_preview_rows(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch);

        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->assertSee($preview->name);
    }

    public function test_grid_shows_empty_state_when_no_rows(): void
    {
        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->assertSee('No previews found');
    }

    // ── Filters ───────────────────────────────────────────────────────────

    public function test_filter_by_batch_uuid(): void
    {
        $batch1  = $this->makeBatch();
        $batch2  = $this->makeBatch();
        $preview1 = $this->makePreview($batch1, PreviewStatus::NEEDS_REVIEW, ['name' => 'Batch One Product']);
        $preview2 = $this->makePreview($batch2, PreviewStatus::NEEDS_REVIEW, ['name' => 'Batch Two Product']);

        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->set('filterBatch', $batch1->id)
            ->assertSee('Batch One Product')
            ->assertDontSee('Batch Two Product');
    }

    public function test_filter_by_status_ready(): void
    {
        $batch   = $this->makeBatch();
        $ready   = $this->makePreview($batch, PreviewStatus::READY, ['name' => 'Ready Product']);
        $review  = $this->makePreview($batch, PreviewStatus::NEEDS_REVIEW, ['name' => 'Review Product']);

        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->set('filterStatus', '2') // READY
            ->assertSee('Ready Product')
            ->assertDontSee('Review Product');
    }

    public function test_filter_by_duplicates(): void
    {
        $batch   = $this->makeBatch();
        $dup     = $this->makePreview($batch, PreviewStatus::NEEDS_REVIEW, ['name' => 'Dup Product', 'occurrences_count' => 3]);
        $single  = $this->makePreview($batch, PreviewStatus::NEEDS_REVIEW, ['name' => 'Unique Product', 'occurrences_count' => 1]);

        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->set('filterDuplicates', true)
            ->assertSee('Dup Product')
            ->assertDontSee('Unique Product');
    }

    public function test_filter_by_edited(): void
    {
        $batch   = $this->makeBatch();
        $edited  = $this->makePreview($batch, PreviewStatus::NEEDS_REVIEW, ['name' => 'Edited Product', 'edited_count' => 2]);
        $pristine = $this->makePreview($batch, PreviewStatus::NEEDS_REVIEW, ['name' => 'Pristine Product', 'edited_count' => 0]);

        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->set('filterEdited', true)
            ->assertSee('Edited Product')
            ->assertDontSee('Pristine Product');
    }

    public function test_filter_change_resets_page(): void
    {
        $batch = $this->makeBatch();

        // After changing filter, selected IDs should be cleared (which happens on filter update)
        $component = Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->set('selectedIds', [999])
            ->set('filterStatus', '1');

        // Verify filter was applied and selection reset
        $component->assertSet('filterStatus', '1')
                  ->assertSet('selectedIds', []);
    }

    // ── Pagination ────────────────────────────────────────────────────────

    public function test_pagination_shows_25_per_page(): void
    {
        $batch = $this->makeBatch();

        // Create 30 previews
        for ($i = 0; $i < 30; $i++) {
            $this->makePreview($batch);
        }

        $component = Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class);

        // The paginator should exist and show 25 on the first page
        $paginator = $component->viewData('previews');
        $this->assertSame(25, $paginator->perPage());
        $this->assertSame(30, $paginator->total());
        $this->assertCount(25, $paginator->getCollection());
    }

    public function test_pagination_second_page_shows_remaining_rows(): void
    {
        $batch = $this->makeBatch();

        for ($i = 0; $i < 27; $i++) {
            $this->makePreview($batch);
        }

        $component = Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->call('nextPage');

        $paginator = $component->viewData('previews');
        $this->assertSame(2, $paginator->currentPage());
        $this->assertLessThanOrEqual(2, $paginator->getCollection()->count());
    }

    // ── Bulk actions ──────────────────────────────────────────────────────

    public function test_save_draft_selected_updates_status(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch, PreviewStatus::NEEDS_REVIEW);

        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->set('selectedIds', [$preview->id])
            ->call('saveDraftSelected');

        $this->assertSame(PreviewStatus::DRAFT, $preview->fresh()->status);
    }

    public function test_delete_selected_soft_deletes_rows(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch, PreviewStatus::NEEDS_REVIEW);

        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->set('selectedIds', [$preview->id])
            ->call('deleteSelected');

        $this->assertNull(BulkUploadPreview::find($preview->id));
        $this->assertNotNull(BulkUploadPreview::withTrashed()->find($preview->id));
    }

    public function test_delete_selected_skips_published_rows(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch, PreviewStatus::PUBLISHED);

        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->set('selectedIds', [$preview->id])
            ->call('deleteSelected');

        // Published rows must not be deleted
        $this->assertNotNull(BulkUploadPreview::find($preview->id));
    }

    public function test_auto_fill_prices_uses_median(): void
    {
        $batch = $this->makeBatch();
        $p1    = $this->makePreview($batch, PreviewStatus::NEEDS_REVIEW, ['price' => 1000.00]);
        $p2    = $this->makePreview($batch, PreviewStatus::NEEDS_REVIEW, ['price' => 3000.00]);
        $p3    = $this->makePreview($batch, PreviewStatus::NEEDS_REVIEW, ['price' => null]);

        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->set('selectedIds', [$p1->id, $p2->id, $p3->id])
            ->call('autoFillPrices');

        // Median of [1000, 3000] = 2000; applied to p3 (no price)
        $this->assertSame(2000.00, (float) $p3->fresh()->price);
        // p1 and p2 already had prices — not overwritten
        $this->assertSame(1000.00, (float) $p1->fresh()->price);
        $this->assertSame(3000.00, (float) $p2->fresh()->price);
    }

    public function test_publish_selected_requires_ready_status(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch, PreviewStatus::NEEDS_REVIEW);

        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->set('selectedIds', [$preview->id])
            ->call('publishSelected');

        // NEEDS_REVIEW preview should not have been published
        $this->assertSame(PreviewStatus::NEEDS_REVIEW, $preview->fresh()->status);
    }

    public function test_select_all_selects_current_page(): void
    {
        $batch = $this->makeBatch();
        $p1    = $this->makePreview($batch);
        $p2    = $this->makePreview($batch);

        $component = Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->set('selectAll', true);

        $this->assertContains($p1->id, $component->get('selectedIds'));
        $this->assertContains($p2->id, $component->get('selectedIds'));
    }

    public function test_unset_select_all_clears_selection(): void
    {
        $batch = $this->makeBatch();
        $this->makePreview($batch);

        Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class)
            ->set('selectAll', true)
            ->set('selectAll', false)
            ->assertSet('selectedIds', []);
    }

    // ── Status counts ─────────────────────────────────────────────────────

    public function test_status_counts_reflect_db_state(): void
    {
        $batch = $this->makeBatch();
        $this->makePreview($batch, PreviewStatus::NEEDS_REVIEW);
        $this->makePreview($batch, PreviewStatus::NEEDS_REVIEW);
        $this->makePreview($batch, PreviewStatus::READY);

        $component = Livewire::actingAs($this->admin())
            ->test(BulkUploadPreviewGrid::class);

        $counts = $component->viewData('statusCounts');
        $this->assertSame(2, (int) ($counts[PreviewStatus::NEEDS_REVIEW->value] ?? 0));
        $this->assertSame(1, (int) ($counts[PreviewStatus::READY->value] ?? 0));
    }
}
