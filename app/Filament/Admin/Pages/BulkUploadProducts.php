<?php

namespace App\Filament\Admin\Pages;

use App\Enums\UploadBatchStatus;
use App\Jobs\GeneratePreviewMetadataJob;
use App\Jobs\ProcessPdfBulkUploadJob;
use App\Models\Category;
use App\Models\Product;
use App\Models\UploadBatch;
use App\Services\BulkUploadService;
use App\Services\PdfProductImportService;
use App\Services\ProductDescriptionService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\RestrictsFileUploadsToSchemaComponents;
use Filament\Schemas\Schema;
use Illuminate\Support\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Bulk Upload Products — Filament admin page.
 *
 * Two upload modes feed the same review table:
 *   1. Direct image upload  (JPEG / PNG / WebP, up to 50 MB each)
 *   2. PDF catalogue upload (smalot parses pages, deduplicates images, generates names)
 *
 * Save actions:
 *   • Save All as Draft   → status = 'draft'  (hidden from storefront)
 *   • Publish Selected    → status = 'active' (live)
 *
 * Validation:
 *   - Required: name, category_id
 *   - Optional: price (blank = null), stock, description, thumbnail
 *   - Price NEVER blocks save or publish.
 */
class BulkUploadProducts extends Page implements HasForms
{
    use InteractsWithForms;
    use RestrictsFileUploadsToSchemaComponents;
    // ── Filament config ───────────────────────────────────────────────────────

    protected static ?string $navigationLabel = 'Bulk Upload';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $title           = 'Bulk Upload Products';
    protected string         $view            = 'filament.admin.pages.bulk-upload-products';

    public static function canAccess(): bool
    {
        return Gate::allows('viewAny', UploadBatch::class);
    }

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-arrow-up-tray';
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return 'Products';
    }

    // ── Constants ─────────────────────────────────────────────────────────────

    private const KEYWORD_MAP = [
        'earrings' => [
            'jhumka', 'jhumki', 'stud', 'studs', 'drop', 'drops',
            'hoop', 'hoops', 'chandbali', 'earring', 'bali', 'danglers',
        ],
    ];

    private const SKU_CODES = [
        'earrings' => 'EAR',
        'default'  => 'GEN',
    ];

    // ── Livewire state ────────────────────────────────────────────────────────

    public ?array $data = [];

    /** All extracted review rows */
    public array $previews = [];

    /** Parsed products generated from PDF uploads */
    public array $parsedProducts = [];

    /** Selected preview row indices for publishing */
    public array $selectedRows = [];

    /** Per-row validation errors */
    public array $rowErrors = [];

    /** How many rows are currently visible in the review table */
    public int $visibleRowCount = 10;

    /** Pipeline progress step */
    public string $step = 'idle';

    /** Progress message shown in the stepper */
    public string $progressMessage = '';

    /** 'draft_saved' | 'published' | '' */
    public string $lastAction = '';
    public int    $lastCount  = 0;

    /** Import summary counters (shown after PDF processing) */
    public int  $summaryPagesScanned  = 0;
    public int  $summaryExtracted     = 0;
    public int  $summaryDuplicates    = 0;
    public int  $summaryPlaceholders  = 0;
    public int  $summaryFailed        = 0;
    public bool $showSummary          = false;

    // ── DB pipeline state (Phase 5) ───────────────────────────────────────

    /** UUID of the most recently created UploadBatch (for grid filter) */
    public ?string $activeBatchUuid = null;

    /** Show the DB-backed review grid instead of in-memory rows */
    public bool $showDbGrid = false;

    // ── Form ──────────────────────────────────────────────────────────────────

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->schema([
                FileUpload::make('pdfUploadData')
                    ->label('Product Catalogue PDFs')
                    ->helperText('PDF — up to 50 MB. Each page is scanned for embedded product images; text is parsed for names and prices.')
                    ->multiple()
                    ->disk('public')
                    ->directory('pdf-uploads')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(51200)
                    ->panelLayout('grid')
                    ->appendFiles()
                    ->live()
                    ->afterStateUpdated(function (?array $state) {
                        if (! empty($state)) {
                            $this->processPdfUploads($state);
                        }
                    }),
                FileUpload::make('images')
                    ->label('Product Images')
                    ->helperText('JPEG · PNG · WebP — up to 50 MB each. Click "Process Images" after uploading.')
                    ->image()
                    ->multiple()
                    ->disk('public')
                    ->directory('products')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(51200)
                    ->panelLayout('grid')
                    ->maxParallelUploads(1)
                    ->fetchFileInformation(false)
                    ->loadingIndicatorPosition('left')
                    ->imagePreviewHeight('180')
                    ->appendFiles()
                    ->afterStateUpdated(fn () => null),
            ]);
    }

    public function mount(): void
    {
        $this->ensureStorageLink();
        $this->form->fill();
    }

    /**
     * Auto-create the public/storage symlink if missing.
     * Prevents "There was an error loading this page" on fresh installs.
     */
    private function ensureStorageLink(): void
    {
        $link   = public_path('storage');
        $target = storage_path('app/public');

        if (! file_exists($link) && ! is_link($link)) {
            try {
                if (! is_dir($target)) {
                    @mkdir($target, 0775, true);
                }
                app('files')->link($target, $link);
            } catch (\Throwable) {
                // Non-fatal — images will fall back to placeholder card
            }
        }
    }

    // ── Manual trigger for image processing ─────────────────────────────────

    /**
     * Called from the Blade template after images finish uploading.
     * Reads the current form state and kicks off metadata generation.
     */
    public function processUploadedImages(): void
    {
        $paths = data_get($this->data, 'images', []);

        if (empty($paths)) {
            Notification::make()
                ->title('No images uploaded yet.')
                ->warning()
                ->send();
            return;
        }

        $start = microtime(true);
        logger()->info('processUploadedImages: start', ['count' => count($paths)]);

        $this->processImageUploads($paths);

        $elapsed = round(microtime(true) - $start, 2);
        logger()->info('processUploadedImages: done', [
            'count'   => count($paths),
            'elapsed' => "{$elapsed}s",
        ]);
    }

    // ── Image upload pipeline (unchanged behaviour) ───────────────────────────

    private function processImageUploads(array $paths): void
    {
        $this->step            = 'generating';
        $this->progressMessage = 'Generating product metadata…';
        $this->lastAction      = '';
        $this->showSummary     = false;
        $this->visibleRowCount = 10;
        $this->selectedRows    = [];

        [$categories, $categoryOpts, $catBySlug, $earringsId] = $this->loadCategories();
        $descService = app(ProductDescriptionService::class);

        $newRows = [];
        foreach (array_values($paths) as $index => $relativePath) {
            $filename       = basename($relativePath);
            $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
            $productName    = $this->filenameToTitle($nameWithoutExt);

            $categorySlug = $this->detectCategoryFromText($nameWithoutExt);
            $categoryName = 'Earrings';
            $categoryId   = $earringsId;
            $description  = $descService->generate($productName, $categoryName);

            $newRows[] = $this->buildRow(
                storedPath:       $relativePath,
                previewUrl:       Storage::disk('public')->url($relativePath),
                name:             $productName,
                categoryId:       $categoryId,
                categoryOpts:     $categoryOpts,
                description:      $description,
                price:            '',
                source:           'image',
                pageInfo:         null,
                duplicatePages:   [],
                occurrencesCount: 1,
                isPlaceholder:    false,
                sourcePdf:        '',
                index:            count($this->previews) + $index,
            );
        }

        $this->previews        = array_merge($this->previews, $newRows);
        $this->step            = 'review';
        $this->progressMessage = '';
    }

    // ── PDF upload pipeline ───────────────────────────────────────────────────

    private function processPdfUploads(array $paths): void
    {
        $this->step            = 'extracting';
        $this->progressMessage = 'Extracting images from PDF…';
        $this->lastAction      = '';
        $this->showSummary     = false;
        $this->visibleRowCount = 10;
        $this->selectedRows    = [];
        $products              = [];
        $candidateCount        = 0;

        [$categories, $categoryOpts, $catBySlug, $earringsId] = $this->loadCategories();
        $descService = app(ProductDescriptionService::class);
        $pdfService  = app(PdfProductImportService::class);

        $errors = [];

        // Accumulate summary across all PDFs in this batch
        $totalPagesScanned  = 0;
        $totalExtracted     = 0;
        $totalDuplicates    = 0;
        $totalPlaceholders  = 0;
        $totalFailed        = 0;

        foreach (array_values($paths) as $file) {
            try {
                $absPath      = $this->resolveRealPath($file);
                $relativePath = $this->resolveStoredPath($file);
            } catch (\RuntimeException $e) {
                $errors[] = (is_string($file) ? basename($file) : 'upload') . ': ' . $e->getMessage();
                continue;
            }

            try {
                $this->progressMessage = 'Extracting: ' . basename($absPath) . '…';
                $candidates = $pdfService->extractCandidates($absPath, $relativePath);
            } catch (\RuntimeException $e) {
                $errors[] = basename($absPath) . ': ' . $e->getMessage();
                continue;
            }

            $candidateCount += count($candidates);

            $totalPagesScanned  += $pdfService->pagesScanned;
            $totalExtracted     += $pdfService->extractedCount;
            $totalDuplicates    += $pdfService->duplicateCount;
            $totalPlaceholders  += $pdfService->placeholderCount;
            $totalFailed        += $pdfService->failedCount;

            $this->progressMessage = 'Generating metadata…';

            foreach ($candidates as $i => $candidate) {
                // Generate premium name — use the one from service (already cleaned)
                $productName = $candidate['name'];

                // Re-generate if it still looks like a filename fallback
                if ($this->looksLikeFilename($productName)) {
                    $productName = $pdfService->generateProductName('', $candidate['pdf_page']);
                }

                $description = $descService->generate($productName, 'Earrings');

                // Build clean, deduplicated source info using pages_found
                $pagesFound = array_values(array_unique($candidate['pages_found'] ?? [$candidate['pdf_page']]));
                sort($pagesFound);
                if (count($pagesFound) === 1) {
                    $pageInfo = 'Page ' . $pagesFound[0];
                } else {
                    $pageInfo = 'Pages: ' . implode(', ', $pagesFound)
                        . ' (' . count($pagesFound) . ' occurrences)';
                }

                if ($candidate['is_placeholder']) {
                    $pageInfo .= ' · placeholder';
                }

                $row = $this->buildRow(
                    storedPath:       $candidate['stored_path'],
                    previewUrl:       $candidate['preview_url'],
                    name:             $productName,
                    categoryId:       $earringsId,   // guaranteed non-null
                    categoryOpts:     $categoryOpts,
                    description:      $description,
                    price:            $candidate['price'],
                    source:           'pdf',
                    pageInfo:         $pageInfo,
                    duplicatePages:   $candidate['duplicate_pages'],
                    occurrencesCount: $candidate['occurrences_count'],
                    isPlaceholder:    $candidate['is_placeholder'],
                    sourcePdf:        $candidate['source_pdf'],
                    index:            count($products) + $i,
                );

                // Keep the existing save keys, and add Blade-friendly aliases.
                $row['category'] = $categoryOpts[$earringsId] ?? 'Earrings';
                $row['image'] = $candidate['preview_url'] ?? null;

                $products[] = $row;
            }
        }

        logger()->info('PDF Parse Count', [
            'products' => count($products),
            'candidates' => $candidateCount,
        ]);

        $this->parsedProducts = $products;
        $this->previews = $products;

        // Show import summary banner
        $this->summaryPagesScanned  = $totalPagesScanned;
        $this->summaryExtracted     = $totalExtracted;
        $this->summaryDuplicates    = $totalDuplicates;
        $this->summaryPlaceholders  = $totalPlaceholders;
        $this->summaryFailed        = $totalFailed;
        $this->showSummary          = true;

        if (! empty($errors)) {
            Notification::make()
                ->title('Some PDFs could not be processed')
                ->body(implode("\n", $errors))
                ->warning()
                ->persistent()
                ->send();
        }

        $this->step            = 'review';
        $this->progressMessage = '';
    }

    // ── Category loader (guaranteed non-null earrings ID) ────────────────────

    /**
     * Load categories and always return a non-null earringsId.
     * Creates the Earrings category if it doesn't exist yet.
     *
     * @return array{0: \Illuminate\Database\Eloquent\Collection, 1: array, 2: \Illuminate\Support\Collection, 3: int}
     */
    private function loadCategories(): array
    {
        // Ensure Earrings category exists — create it if missing
        $earringsCat = Category::firstOrCreate(
            ['slug' => 'earrings'],
            ['name' => 'Earrings', 'status' => true]
        );

        $categories   = Category::orderBy('name')->get();
        $categoryOpts = $categories->pluck('name', 'id')->toArray();
        $catBySlug    = $categories->keyBy(fn ($c) => Str::slug($c->name));

        return [$categories, $categoryOpts, $catBySlug, $earringsCat->id];
    }

    // ── Row builder ───────────────────────────────────────────────────────────

    private function buildRow(
        string  $storedPath,
        string  $previewUrl,
        string  $name,
        int     $categoryId,         // always guaranteed non-null
        array   $categoryOpts,
        string  $description,
        string  $price,
        string  $source,
        ?string $pageInfo,
        array   $duplicatePages,
        int     $occurrencesCount,
        bool    $isPlaceholder,
        string  $sourcePdf,
        int     $index,
    ): array {
        $sku = $this->generateSku('earrings', $index);

        return [
            'stored_path'       => $storedPath,
            'preview_url'       => $previewUrl,
            'name'              => $name,
            'category_id'       => $categoryId,
            'description'       => $description,
            'price'             => $price,
            'stock'             => 1,
            'sku'               => $sku,
            'category_opts'     => $categoryOpts,
            'source'            => $source,
            'page_info'         => $pageInfo,
            'duplicate_pages'   => $duplicatePages,
            'occurrences_count' => $occurrencesCount,
            'is_placeholder'    => $isPlaceholder,
            'source_pdf'        => $sourcePdf,
            'selected'          => false,
        ];
    }

    // ── Inline edits ──────────────────────────────────────────────────────────

    public function updateField(int $index, string $field, mixed $value): void
    {
        if (! isset($this->previews[$index])) {
            return;
        }

        $this->previews[$index][$field] = $value;

        if ($field === 'category_id') {
            $cat = Category::find($value);
            if ($cat) {
                $this->previews[$index]['description'] = app(ProductDescriptionService::class)
                    ->generate($this->previews[$index]['name'], $cat->name);
            }
        }

        unset($this->rowErrors[$index]);
    }

    public function toggleRow(int $index): void
    {
        if (isset($this->previews[$index])) {
            $this->previews[$index]['selected'] = ! ($this->previews[$index]['selected'] ?? false);
        }
    }

    public function toggleAll(bool $value): void
    {
        foreach ($this->previews as $i => $_) {
            $this->previews[$i]['selected'] = $value;
        }
    }

    public function selectAll(): void
    {
        $this->toggleAll(true);
    }

    public function deselectAll(): void
    {
        $this->toggleAll(false);
    }

    public function removeRow(int $index): void
    {
        if (isset($this->previews[$index])) {
            Storage::disk('public')->delete($this->previews[$index]['stored_path']);
        }
        unset($this->previews[$index]);
        $this->previews  = array_values($this->previews);
        $this->rowErrors = array_values(array_filter(
            $this->rowErrors,
            fn ($k) => $k !== $index,
            ARRAY_FILTER_USE_KEY
        ));
    }

    public function clearAll(): void
    {
        foreach ($this->previews as $row) {
            Storage::disk('public')->delete($row['stored_path'] ?? '');
        }
        $this->previews         = [];
        $this->selectedRows     = [];
        $this->rowErrors        = [];
        $this->visibleRowCount  = 10;
        $this->step             = 'idle';
        $this->progressMessage  = '';
        $this->lastAction       = '';
        $this->showSummary      = false;
        $this->data = [];
        $this->form->fill();
    }

    /** Load the next batch of rows into the visible window. */
    public function showMoreRows(): void
    {
        $this->visibleRowCount = min(
            $this->visibleRowCount + 10,
            count($this->previews)
        );
    }

    // ── Save: Draft ───────────────────────────────────────────────────────────

    public function saveDraft(): void
    {
        if (! $this->validateRows()) {
            return;
        }
        $count = $this->persistRows($this->previews, 'draft');
        $this->finishSave('draft_saved', $count);
    }

    // ── Save: Publish Selected ────────────────────────────────────────────────

    public function publishSelected(): void
    {
        $selectedIndexes = array_values(array_unique(array_map('intval', $this->selectedRows)));
        $selected = array_intersect_key($this->previews, array_flip($selectedIndexes));

        if (empty($selected)) {
            Notification::make()
                ->title('No rows selected.')
                ->body('Tick at least one row before publishing.')
                ->warning()
                ->send();
            return;
        }

        if (! $this->validateRows(array_keys($selected))) {
            return;
        }

        $count = $this->persistRows($selected, 'active');

        foreach (array_keys($selected) as $i) {
            unset($this->previews[$i]);
        }
        $this->previews        = array_values($this->previews);
        $this->selectedRows    = [];
        $this->visibleRowCount = min($this->visibleRowCount, max(10, count($this->previews)));

        $this->lastAction = 'published';
        $this->lastCount  = $count;

        if (empty($this->previews)) {
            $this->step = 'done';
        }

        Notification::make()
            ->title("{$count} product(s) published.")
            ->success()
            ->send();
    }

    // ── Validation (name + category only; price optional) ────────────────────

    private function validateRows(?array $indices = null): bool
    {
        $this->rowErrors = [];
        $hasError        = false;

        $rows = $indices !== null
            ? array_intersect_key($this->previews, array_flip($indices))
            : $this->previews;

        foreach ($rows as $i => $row) {
            $errors = [];

            if (blank($row['name'])) {
                $errors[] = 'Product name is required.';
            }
            if (blank($row['category_id'])) {
                $errors[] = 'Category is required.';
            }
            // Price is intentionally NOT validated — blank price is allowed.
            // Stock default is 1, so it is never blank.

            if (! empty($errors)) {
                $this->rowErrors[$i] = $errors;
                $hasError            = true;
            }
        }

        if ($hasError) {
            Notification::make()
                ->title('Some rows have missing required fields.')
                ->danger()
                ->send();
        }

        return ! $hasError;
    }

    // ── Persist rows to DB ────────────────────────────────────────────────────

    /**
     * @param  'active'|'draft'  $status
     */
    private function persistRows(array $rows, string $status): int
    {
        $created = 0;

        DB::transaction(function () use ($rows, $status, &$created) {
            foreach ($rows as $row) {
                $name            = $row['name'];
                $description     = $row['description'] ?? '';
                $metaTitle       = $name . ' | Svaraa Jewels';
                $metaDescription = Str::limit(strip_tags($description), 160, '');
                $thumbnailPath   = $this->normalizeThumbnailPath(
                    $row['stored_path'] ?? $row['image'] ?? $row['preview_url'] ?? null,
                );

                Product::create([
                    'category_id'      => $row['category_id'],
                    'name'             => $name,
                    'price'            => blank($row['price']) ? 0 : (float) $row['price'],
                    'description'      => $description,
                    'thumbnail'        => $thumbnailPath,
                    'stock'            => (int) ($row['stock'] ?? 1),
                    'status'           => $status === 'active' ? true : false,
                    'meta_title'       => $metaTitle,
                    'meta_description' => $metaDescription,
                    // slug auto-generated by Product::boot()
                ]);
                $created++;
            }
        });

        return $created;
    }

    private function normalizeThumbnailPath(mixed $thumbnail): ?string
    {
        if (! is_string($thumbnail) || blank($thumbnail)) {
            return null;
        }

        $thumbnail = trim($thumbnail);
        $thumbnail = str_replace('\\', '/', $thumbnail);

        if (filter_var($thumbnail, FILTER_VALIDATE_URL)) {
            $path = parse_url($thumbnail, PHP_URL_PATH);
            $thumbnail = is_string($path) ? $path : $thumbnail;
        }

        if (preg_match('/^[A-Za-z]:\//', $thumbnail)) {
            $publicDiskRoot = str_replace('\\', '/', Storage::disk('public')->path(''));
            $publicDiskRoot = rtrim($publicDiskRoot, '/');

            if (str_starts_with($thumbnail, $publicDiskRoot . '/')) {
                $thumbnail = substr($thumbnail, strlen($publicDiskRoot) + 1);
            } else {
                $thumbnail = basename($thumbnail);
            }
        }

        $thumbnail = ltrim($thumbnail, '/');

        if (str_starts_with($thumbnail, 'storage/')) {
            $thumbnail = substr($thumbnail, strlen('storage/'));
        }

        return blank($thumbnail) ? null : $thumbnail;
    }

    private function finishSave(string $action, int $count): void
    {
        $this->lastAction      = $action;
        $this->lastCount       = $count;
        $this->previews        = [];
        $this->selectedRows    = [];
        $this->rowErrors       = [];
        $this->visibleRowCount = 10;
        $this->data = [];
        $this->step = 'done';
        $this->form->fill();

        $label = $action === 'draft_saved' ? 'saved as draft' : 'published';
        Notification::make()
            ->title("{$count} product(s) {$label}.")
            ->success()
            ->send();
    }

    // ── DB pipeline dispatch (Phase 5) ───────────────────────────────────

    /**
     * Dispatch a full DB-backed processing pipeline for an already-uploaded PDF.
     *
     * Creates an UploadBatch, queues ProcessPdfBulkUploadJob → GeneratePreviewMetadataJob,
     * then opens the DB review grid so the admin can monitor and review rows as they appear.
     *
     * @param string $filePath   Storage-relative path on the public disk (e.g. 'pdf-uploads/foo.pdf')
     * @param int    $totalPages Total page count of the PDF
     */
    public function dispatchPdfPipeline(string $filePath, int $totalPages = 1): void
    {
        $user  = auth()->user();
        $batch = app(BulkUploadService::class)->createBatch($user, [
            'source_file' => $filePath,
            'uploaded_at' => now()->toIso8601String(),
        ]);

        $batch->update(['total_pages' => $totalPages]);

        // Chain: extract → enrich metadata (publish is manual via review grid)
        \Illuminate\Support\Facades\Bus::chain([
            new ProcessPdfBulkUploadJob($batch->id, $filePath, 1, $totalPages),
            new GeneratePreviewMetadataJob($batch->id),
        ])->dispatch();

        $this->activeBatchUuid = $batch->id;
        $this->showDbGrid      = true;
        $this->step            = 'review';

        Notification::make()
            ->title('Processing started.')
            ->body("Batch {$batch->id} queued. Preview rows will appear in the grid below.")
            ->success()
            ->send();
    }

    /**
     * Toggle to the DB-backed review grid without re-uploading.
     * Useful for admins who want to review previously extracted batches.
     */
    public function openReviewGrid(?string $batchUuid = null): void
    {
        $this->activeBatchUuid = $batchUuid;
        $this->showDbGrid      = true;
    }

    /**
     * Close the DB review grid and return to the upload form.
     */
    public function closeReviewGrid(): void
    {
        $this->showDbGrid      = false;
        $this->activeBatchUuid = null;
    }

    // ── Path resolution (unchanged) ───────────────────────────────────────────

    public function resolveRealPath(mixed $file): string
    {
        if (is_object($file) && method_exists($file, 'getRealPath')) {
            $path = $file->getRealPath();
            if (! empty($path) && file_exists($path)) {
                return $path;
            }
        }

        if (is_string($file)) {
            if ($this->isAbsolutePath($file)) {
                if (file_exists($file)) {
                    return $file;
                }
                throw new \RuntimeException("PDF file not found at absolute path: {$file}");
            }

            $abs = Storage::disk('public')->path($file);
            if (file_exists($abs)) {
                return $abs;
            }
            throw new \RuntimeException("PDF file not found on public disk: {$file}");
        }

        throw new \RuntimeException('Cannot resolve uploaded file to a filesystem path.');
    }

    private function resolveStoredPath(mixed $file): string
    {
        if (is_object($file) && method_exists($file, 'getClientOriginalName')) {
            return 'pdf-uploads/' . $file->getClientOriginalName();
        }
        if (is_string($file) && ! $this->isAbsolutePath($file)) {
            return $file;
        }
        if (is_string($file)) {
            return 'pdf-uploads/' . basename($file);
        }
        return 'pdf-uploads/upload-' . uniqid() . '.pdf';
    }

    private function isAbsolutePath(string $path): bool
    {
        if (str_starts_with($path, '/')) {
            return true;
        }
        if (preg_match('/^[A-Za-z]:[\\\\\/]/', $path)) {
            return true;
        }
        return false;
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function filenameToTitle(string $filename): string
    {
        return Str::title(str_replace(['-', '_', '.'], ' ', $filename));
    }

    private function detectCategoryFromText(string $text): string
    {
        $lower = strtolower($text);
        foreach (self::KEYWORD_MAP as $slug => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($lower, $kw)) {
                    return $slug;
                }
            }
        }
        return 'earrings';
    }

    private function generateSku(string $categorySlug, int $index): string
    {
        $code    = self::SKU_CODES[$categorySlug] ?? self::SKU_CODES['default'];
        $counter = Product::count() + $index + 1;
        return sprintf('SVR-%s-%04d', $code, $counter);
    }

    /**
     * Detect strings that look like raw filenames rather than product names.
     * E.g. "Untitled Design 4", "New File 2", "Page 3", "My Document".
     */
    private function looksLikeFilename(string $name): bool
    {
        return (bool) preg_match(
            '/^(untitled|new[\s_]file|new[\s_]doc|page\s+\d|my\s+doc|copy\s+of|draft)/i',
            trim($name)
        );
    }
}
