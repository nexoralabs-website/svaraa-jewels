<?php

namespace App\Livewire;

use App\Enums\PreviewStatus;
use App\Enums\UploadBatchStatus;
use App\Jobs\PublishBulkProductsJob;
use App\Models\BulkUploadPreview;
use App\Models\Category;
use App\Models\UploadBatch;
use App\Services\BulkUploadRecoveryService;
use App\Services\BulkUploadService;
use App\Services\PreviewValidationService;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use LogicException;
use Throwable;

/**
 * BulkUploadPreviewGrid
 *
 * DB-backed review grid for BulkUploadPreview rows.
 *
 * Responsibilities:
 *  - Load from DB with pagination (25/page), eager-load category, avoid N+1.
 *  - Filter by batch, status, duplicates, edited.
 *  - Inline edit with optimistic locking via `version`.
 *  - Row actions: saveRow, duplicateRow, deleteRow, resetToAi, markReady, reprocessBatch.
 *  - Bulk actions: publishSelected, saveDraftSelected, deleteSelected, autoFillPrices, regenerateDescriptions.
 *  - Never contains business logic — delegates to BulkUploadService / PreviewValidationService.
 */
class BulkUploadPreviewGrid extends Component
{
    use WithPagination;

    // ── Filters ───────────────────────────────────────────────────────────

    #[Url(history: true)]
    public ?string $filterBatch = null;

    #[Url(history: true)]
    public ?string $filterStatus = '';

    #[Url(history: true)]
    public bool $filterDuplicates = false;

    #[Url(history: true)]
    public bool $filterEdited = false;

    // ── Selection ─────────────────────────────────────────────────────────

    /** IDs of selected preview rows */
    public array $selectedIds = [];

    public bool $selectAll = false;

    public array $selectedRows = [];

    public function selectAllRows(): void
    {
        $this->selectedRows =
            $this->previews()
                ->pluck('id')
                ->toArray();
        $this->selectedIds = $this->selectedRows;
    }

    public function deselectAllRows(): void
    {
        $this->selectedRows = [];
        $this->selectedIds = [];
    }

    public function toggleRow(int $id): void
    {
        if (in_array($id, $this->selectedRows)) {
            $this->selectedRows =
                array_values(
                    array_diff(
                        $this->selectedRows,
                        [$id]
                    )
                );
        } else {
            $this->selectedRows[] = $id;
        }
    }

    public function updatedSelectedRows(): void
    {
        $this->selectedIds = $this->selectedRows;
    }

    public function updatedSelectedIds(): void
    {
        $this->selectedRows = $this->selectedIds;
    }

    // ── Inline edit state ─────────────────────────────────────────────────

    /**
     * Pending edits keyed by preview ID.
     * Shape: [ id => ['name' => ..., 'price' => ..., 'category_id' => ..., 'stock' => ...] ]
     */
    public array $editValues = [];

    /** Optimistic lock versions captured when the row was loaded for editing */
    public array $editVersions = [];

    // ── Compare Changes modal ─────────────────────────────────────────────

    public bool  $showCompareModal = false;
    public ?int  $comparePreviewId = null;
    public array $compareAi        = [];
    public array $compareEdited    = [];

    // ── Category options cache ────────────────────────────────────────────

    public array $categoryOptions = [];

    // ── Lifecycle ─────────────────────────────────────────────────────────

    public function mount(?string $batchUuid = null): void
    {
        if ($batchUuid) {
            $this->filterBatch = $batchUuid;
        }

        $this->categoryOptions = Category::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    // ── Render ────────────────────────────────────────────────────────────

    public function render(): View
    {
        return view('livewire.bulk-upload-preview-grid', [
            'previews'       => $this->loadPreviews(),
            'statusCounts'   => $this->statusCounts(),
            'activeBatches'  => $this->activeBatches(),
        ]);
    }

    /** Helper to access current previews collection for selection */
    public function previews()
    {
        return $this->loadPreviews()->getCollection();
    }

    // ── Query ─────────────────────────────────────────────────────────────

    private function loadPreviews(): LengthAwarePaginator
    {
        $query = BulkUploadPreview::with('category')
            ->latest();

        if ($this->filterBatch) {
            $query->where('batch_uuid', $this->filterBatch);
        }

        if ($this->filterStatus !== '' && $this->filterStatus !== null) {
            $query->where('status', (int) $this->filterStatus);
        }

        if ($this->filterDuplicates) {
            $query->where('occurrences_count', '>', 1);
        }

        if ($this->filterEdited) {
            $query->where('edited_count', '>', 0);
        }

        return $query->paginate(25);
    }

    private function statusCounts(): array
    {
        $query = BulkUploadPreview::query();

        if ($this->filterBatch) {
            $query->where('batch_uuid', $this->filterBatch);
        }

        return $query->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }

    private function activeBatches(): array
    {
        return UploadBatch::latest()
            ->limit(20)
            ->get(['id', 'status', 'progress_message', 'total_pages', 'processed_pages'])
            ->map(fn($b) => [
                'id' => $b->id,
                'status' => $b->status->value,
                'progress_message' => $b->progress_message,
                'total_pages' => $b->total_pages,
                'processed_pages' => $b->processed_pages,
            ])
            ->toArray();
    }

    // ── Filter updaters ───────────────────────────────────────────────────

    public function updatedFilterBatch(): void
    {
        $this->resetPage();
        $this->selectedIds = [];
        $this->selectAll   = false;
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
        $this->selectedIds = [];
        $this->selectAll   = false;
    }

    public function updatedFilterDuplicates(): void
    {
        $this->resetPage();
    }

    public function updatedFilterEdited(): void
    {
        $this->resetPage();
    }

    // ── Inline editing ────────────────────────────────────────────────────

    /**
     * Called client-side with debounce 800ms.
     * Captures the current version for optimistic locking.
     */
    public function startEdit(int $id): void
    {
        $preview = BulkUploadPreview::find($id);
        if (! $preview) {
            return;
        }

        $this->editVersions[$id] = $preview->version;
        $this->editValues[$id]   = [
            'name'        => $preview->name,
            'price'       => $preview->price,
            'stock'       => $preview->stock,
            'category_id' => $preview->category_id,
            'description' => $preview->description,
        ];
    }

    /**
     * Persist inline edits for a single row.
     * Checks version to detect concurrent modification.
     */
    public function saveRow(int $id): void
    {
        if (! isset($this->editValues[$id])) {
            return;
        }

        $preview = BulkUploadPreview::find($id);
        if (! $preview) {
            $this->clearEdit($id);
            return;
        }

        // Optimistic lock check
        $capturedVersion = $this->editVersions[$id] ?? null;
        if ($capturedVersion !== null && $preview->version !== $capturedVersion) {
            $this->dispatch('conflict-warning', id: $id, message:
                'This row was modified by another session. Please reload and retry.');
            return;
        }

        $changes = $this->editValues[$id];

        // Delegate all business logic to the service
        app(BulkUploadService::class)->updatePreview($preview, array_filter(
            $changes,
            fn ($v) => $v !== null
        ));

        // Re-run validation and persist result
        $validationResult = app(PreviewValidationService::class)->validate($preview->fresh());
        $preview->fresh()->forceFill(['validation_result' => $validationResult])->save();

        $this->clearEdit($id);

        $this->dispatch('row-saved', id: $id);

        Notification::make()
            ->title('Row saved.')
            ->success()
            ->send();
    }

    private function clearEdit(int $id): void
    {
        unset($this->editValues[$id], $this->editVersions[$id]);
    }

    // ── Row actions ───────────────────────────────────────────────────────

    public function duplicateRow(int $id): void
    {
        $preview = BulkUploadPreview::find($id);
        if (! $preview) {
            return;
        }

        app(BulkUploadService::class)->duplicatePreview($preview);

        Notification::make()
            ->title('Row duplicated.')
            ->success()
            ->send();
    }

    public function deleteRow(int $id): void
    {
        $preview = BulkUploadPreview::find($id);
        if (! $preview) {
            return;
        }

        app(BulkUploadService::class)->deletePreview($preview);

        // Remove from selection if present
        $this->selectedIds = array_values(array_diff($this->selectedIds, [$id]));

        Notification::make()
            ->title('Row deleted.')
            ->warning()
            ->send();
    }

    public function resetToAi(int $id): void
    {
        $preview = BulkUploadPreview::find($id);
        if (! $preview) {
            return;
        }

        app(BulkUploadService::class)->resetPreview($preview);

        // Re-run validation
        $validationResult = app(PreviewValidationService::class)->validate($preview->fresh());
        $preview->fresh()->forceFill(['validation_result' => $validationResult])->save();

        Notification::make()
            ->title('Reset to AI values.')
            ->info()
            ->send();
    }

    public function markReady(int $id): void
    {
        $preview = BulkUploadPreview::find($id);
        if (! $preview) {
            return;
        }

        // Run validation first; block if blocking errors
        $validationResult = app(PreviewValidationService::class)->validate($preview);
        $preview->forceFill(['validation_result' => $validationResult])->save();

        if (count($validationResult['blocking']) > 0) {
            $errors = implode(', ', $validationResult['blocking']);
            Notification::make()
                ->title('Cannot mark as Ready')
                ->body("Blocking errors: {$errors}")
                ->danger()
                ->send();
            return;
        }

        try {
            $preview->markReady()->save();

            Notification::make()
                ->title('Marked as Ready.')
                ->success()
                ->send();
        } catch (LogicException $e) {
            Notification::make()
                ->title('Cannot mark as Ready')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Reprocess the entire batch (completed or failed).
     * Deletes old previews and files, then re-runs extraction.
     */
    public function reprocessBatch(string $batchUuid): void
    {
        $batch = UploadBatch::find($batchUuid);
        if (! $batch) {
            Notification::make()
                ->title('Batch not found.')
                ->danger()
                ->send();
            return;
        }

        // Only allow reprocess for completed or failed batches
        if (!in_array($batch->status, [
            UploadBatchStatus::COMPLETED,
            UploadBatchStatus::FAILED,
        ], true)) {
            Notification::make()
                ->title('Cannot reprocess batch.')
                ->body('Only completed or failed batches can be reprocessed.')
                ->warning()
                ->send();
            return;
        }

        $success = app(BulkUploadRecoveryService::class)->reprocessBatch($batchUuid);

        if ($success) {
            Notification::make()
                ->title('Reprocess started.')
                ->body('The batch is being re-processed. Refresh to see new previews.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Reprocess failed.')
                ->danger()
                ->send();
        }
    }

    // ── Compare Changes modal ─────────────────────────────────────────────

    public function openCompareModal(int $id): void
    {
        $preview = BulkUploadPreview::find($id);
        if (! $preview) {
            return;
        }

        $trackable = ['name', 'category_id', 'description', 'price', 'sku', 'slug', 'preview_image_path'];
        $ai        = $preview->ai_extracted_data ?? [];

        $this->compareAi      = array_intersect_key($ai, array_flip($trackable));
        $this->compareEdited  = array_intersect_key($preview->toArray(), array_flip($trackable));
        $this->comparePreviewId = $id;
        $this->showCompareModal = true;
    }

    public function closeCompareModal(): void
    {
        $this->showCompareModal = false;
        $this->comparePreviewId = null;
        $this->compareAi        = [];
        $this->compareEdited    = [];
    }

    // ── Bulk actions ──────────────────────────────────────────────────────

    public function publishSelected(): void
    {
        if (empty($this->selectedIds)) {
            Notification::make()->title('No rows selected.')->warning()->send();
            return;
        }

        // Only READY previews can be published; collect distinct batch UUIDs
        $batchUuids = BulkUploadPreview::whereIn('id', $this->selectedIds)
            ->where('status', PreviewStatus::READY->value)
            ->distinct()
            ->pluck('batch_uuid')
            ->unique();

        if ($batchUuids->isEmpty()) {
            Notification::make()
                ->title('No Ready rows selected.')
                ->body('Only rows with status READY can be published. Use "Mark Ready" first.')
                ->warning()
                ->send();
            return;
        }

        foreach ($batchUuids as $uuid) {
            // Dispatch per-batch — PublishBulkProductsJob only processes READY rows
            PublishBulkProductsJob::dispatch($uuid);
        }

        $this->selectedIds = [];
        $this->selectAll   = false;

        Notification::make()
            ->title('Publish job dispatched.')
            ->body(count($batchUuids) . ' batch(es) queued for publishing.')
            ->success()
            ->send();
    }

    public function saveDraftSelected(): void
    {
        if (empty($this->selectedIds)) {
            Notification::make()->title('No rows selected.')->warning()->send();
            return;
        }

        $updated = BulkUploadPreview::whereIn('id', $this->selectedIds)
            ->whereNotIn('status', [
                PreviewStatus::PUBLISHED->value,
                PreviewStatus::FAILED->value,
            ])
            ->update(['status' => PreviewStatus::DRAFT->value]);

        $this->selectedIds = [];
        $this->selectAll   = false;

        Notification::make()
            ->title("{$updated} row(s) saved as Draft.")
            ->success()
            ->send();
    }

    public function deleteSelected(): void
    {
        if (empty($this->selectedIds)) {
            Notification::make()->title('No rows selected.')->warning()->send();
            return;
        }

        $deleted = BulkUploadPreview::whereIn('id', $this->selectedIds)
            ->whereNotIn('status', [PreviewStatus::PUBLISHED->value])
            ->delete();

        $this->selectedIds = [];
        $this->selectAll   = false;

        Notification::make()
            ->title("{$deleted} row(s) deleted.")
            ->warning()
            ->send();
    }

    public function autoFillPrices(): void
    {
        if (empty($this->selectedIds)) {
            Notification::make()->title('No rows selected.')->warning()->send();
            return;
        }

        // Compute median price of selected rows that already have a price
        $prices = BulkUploadPreview::whereIn('id', $this->selectedIds)
            ->whereNotNull('price')
            ->where('price', '>', 0)
            ->pluck('price')
            ->map(fn ($v) => (float) $v)
            ->sort()
            ->values();

        if ($prices->isEmpty()) {
            Notification::make()
                ->title('No existing prices found for median calculation.')
                ->warning()
                ->send();
            return;
        }

        $mid    = (int) floor($prices->count() / 2);
        $median = $prices->count() % 2 === 1
            ? $prices[$mid]
            : ($prices[$mid - 1] + $prices[$mid]) / 2;

        // Apply to rows that have no price set
        $updated = BulkUploadPreview::whereIn('id', $this->selectedIds)
            ->where(fn ($q) => $q->whereNull('price')->orWhere('price', 0))
            ->update(['price' => round($median, 2)]);

        Notification::make()
            ->title("{$updated} prices filled with median ₹" . number_format($median, 2))
            ->success()
            ->send();
    }

    public function regenerateDescriptions(): void
    {
        if (empty($this->selectedIds)) {
            Notification::make()->title('No rows selected.')->warning()->send();
            return;
        }

        $service = app(\App\Services\ProductDescriptionService::class);

        $count = 0;
        BulkUploadPreview::whereIn('id', $this->selectedIds)
            ->with('category')
            ->chunkById(50, function ($previews) use ($service, &$count) {
                foreach ($previews as $preview) {
                    $categoryName = $preview->category?->name ?? 'Jewellery';
                    $description  = $service->generate($preview->name ?? '', $categoryName);

                    $meta = $preview->processing_metadata ?? [];
                    $meta['description_regenerated_at'] = now()->toIso8601String();

                    $preview->forceFill([
                        'description'         => $description,
                        'processing_metadata' => $meta,
                    ])->save();

                    $count++;
                }
            });

        Notification::make()
            ->title("{$count} description(s) regenerated.")
            ->success()
            ->send();
    }

    // ── Select-all toggle ─────────────────────────────────────────────────

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            // Select all IDs on the current page
            $this->selectedIds = $this->previews()
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->toArray();
            $this->selectedRows = $this->selectedIds;
        } else {
            $this->selectedIds = [];
            $this->selectedRows = [];
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Human-readable label and Tailwind color for each status integer.
     */
    public function statusLabel(int $value): array
    {
        return match ($value) {
            0 => ['label' => 'Draft',       'color' => 'gray'],
            1 => ['label' => 'Needs Review','color' => 'yellow'],
            2 => ['label' => 'Ready',       'color' => 'blue'],
            3 => ['label' => 'Published',   'color' => 'green'],
            4 => ['label' => 'Failed',      'color' => 'red'],
            default => ['label' => 'Unknown', 'color' => 'gray'],
        };
    }
}