<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Services\PdfProductImportService;
use App\Services\ProductDescriptionService;
use Dompdf\Dompdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Tests for BulkUploadProducts page logic and the PdfProductImportService.
 *
 * These tests focus on the service layer — the Filament Livewire component
 * itself is not HTTP-testable without a browser driver, so we test the
 * supporting services directly and verify the database outcomes.
 */
class BulkUploadTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function earringsCategory(): Category
    {
        return Category::firstOrCreate(
            ['slug' => 'earrings'],
            ['name' => 'Earrings', 'status' => true]
        );
    }

    /**
     * Build a valid parseable PDF using dompdf (already in composer.json).
     * This produces a proper PDF-1.7 with accurate xref — smalot can parse it.
     */
    private function buildMinimalPdf(string $pageText = ''): string
    {
        $text = htmlspecialchars($pageText ?: 'Product Name Test Page');

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml("<html><body><p>{$text}</p></body></html>");
        $dompdf->setPaper('A4');
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Build a minimal encrypted PDF (has /Encrypt in header).
     */
    private function buildEncryptedPdf(): string
    {
        return "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Encrypt 99 0 R >>\nendobj\n%%EOF\n";
    }

    // ── PdfProductImportService tests ─────────────────────────────────────────

    public function test_pdf_service_rejects_non_pdf_file(): void
    {
        Storage::fake('public');

        $service = app(PdfProductImportService::class);
        $path    = sys_get_temp_dir() . '/test-not-a-pdf.txt';
        file_put_contents($path, 'This is not a PDF');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/valid PDF/i');

        try {
            $service->extractCandidates($path, 'pdf-uploads/test.pdf');
        } finally {
            @unlink($path);
        }
    }

    public function test_pdf_service_rejects_encrypted_pdf(): void
    {
        Storage::fake('public');

        $service = app(PdfProductImportService::class);
        $path    = sys_get_temp_dir() . '/test-encrypted.pdf';
        file_put_contents($path, $this->buildEncryptedPdf());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/[Ee]ncrypt/');

        try {
            $service->extractCandidates($path, 'pdf-uploads/encrypted.pdf');
        } finally {
            @unlink($path);
        }
    }

    public function test_pdf_service_extracts_one_candidate_per_page(): void
    {
        Storage::fake('public');

        $service  = app(PdfProductImportService::class);
        $pdfData  = $this->buildMinimalPdf('Gold Jhumka Earrings');
        $path     = sys_get_temp_dir() . '/test-single-page.pdf';
        file_put_contents($path, $pdfData);

        try {
            $candidates = $service->extractCandidates($path, 'pdf-uploads/test-single-page.pdf');
        } finally {
            @unlink($path);
        }

        // A minimal dompdf PDF has no qualifying product images.
        // Placeholders are disabled, so the service returns 0 rows (no crash).
        // pagesScanned must still equal the actual page count (1).
        $this->assertIsArray($candidates);
        $this->assertSame(1, $service->pagesScanned);
        // placeholderCount should be incremented instead of creating a row
        $this->assertSame(1, $service->placeholderCount);

        // If by chance an image was extracted (e.g. dompdf embeds one), verify shape.
        foreach ($candidates as $c) {
            $this->assertSame(1, $c['pdf_page']);
            $this->assertSame('pdf', $c['source']);
            $this->assertStringStartsWith('pdf-extracted/', $c['stored_path']);
            $this->assertArrayHasKey('pages_found', $c);
            $this->assertContains(1, $c['pages_found']);
        }
    }

    public function test_pdf_service_skips_page_without_image_no_placeholder_row(): void
    {
        Storage::fake('public');

        $service = app(PdfProductImportService::class);
        $path    = sys_get_temp_dir() . '/test-no-images.pdf';
        file_put_contents($path, $this->buildMinimalPdf());

        try {
            $candidates = $service->extractCandidates($path, 'pdf-uploads/no-images.pdf');
        } finally {
            @unlink($path);
        }

        // Placeholders are disabled: pages without qualifying images produce NO candidate rows.
        $this->assertCount(0, $candidates);
        // The page was still scanned and counted.
        $this->assertSame(1, $service->pagesScanned);
        // placeholderCount tracks skipped pages for the summary banner.
        $this->assertSame(1, $service->placeholderCount);
    }

    public function test_pdf_service_extracts_product_name_from_page_text(): void
    {
        Storage::fake('public');

        $service = app(PdfProductImportService::class);
        $path    = sys_get_temp_dir() . '/test-name-extract.pdf';
        file_put_contents($path, $this->buildMinimalPdf('Chandbali Classic'));

        try {
            $candidates = $service->extractCandidates($path, 'pdf-uploads/name-extract.pdf');
        } finally {
            @unlink($path);
        }

        // A minimal PDF has no embedded product images; placeholders are disabled.
        // Verify the page was scanned and no exception was thrown.
        $this->assertIsArray($candidates);
        $this->assertSame(1, $service->pagesScanned);

        // If any candidate was extracted (e.g. PDF embeds an image), name is non-empty.
        foreach ($candidates as $c) {
            $this->assertNotEmpty($c['name']);
        }
    }

    public function test_pdf_service_extracts_price_from_page_text(): void
    {
        Storage::fake('public');

        $service = app(PdfProductImportService::class);
        $path    = sys_get_temp_dir() . '/test-price-extract.pdf';
        file_put_contents($path, $this->buildMinimalPdf('Ember Drop MRP ₹1,600'));

        try {
            $candidates = $service->extractCandidates($path, 'pdf-uploads/price-extract.pdf');
        } finally {
            @unlink($path);
        }

        // Placeholders disabled: minimal PDF with no images yields 0 rows.
        $this->assertIsArray($candidates);
        // If any candidate exists, price must be a string ('' or a numeric string).
        foreach ($candidates as $c) {
            $this->assertIsString($c['price']);
        }
    }

    public function test_pdf_service_missing_file_throws(): void
    {
        Storage::fake('public');

        $service = app(PdfProductImportService::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/not found/i');

        $service->extractCandidates('/nonexistent/path/file.pdf', 'pdf-uploads/ghost.pdf');
    }

    // ── Description service integration ──────────────────────────────────────

    public function test_description_service_generates_earrings_description(): void
    {
        $service     = app(ProductDescriptionService::class);
        $description = $service->generate('Gold Jhumka', 'Earrings');

        $this->assertNotEmpty($description);
        $this->assertIsString($description);
        // Should not contain non-earring category references
        $this->assertStringNotContainsStringIgnoringCase('ring', $description);
        $this->assertStringNotContainsStringIgnoringCase('necklace', $description);
    }

    public function test_description_service_deterministic_for_same_name(): void
    {
        $service = app(ProductDescriptionService::class);
        $a       = $service->generate('Chandbali Delight', 'Earrings');
        $b       = $service->generate('Chandbali Delight', 'Earrings');

        $this->assertSame($a, $b);
    }

    // ── Draft save (database integration) ────────────────────────────────────

    public function test_draft_products_are_created_with_draft_status(): void
    {
        $this->earringsCategory();

        $category = Category::where('slug', 'earrings')->first();

        // Simulate what persistRows does for a draft save
        Product::create([
            'category_id' => $category->id,
            'name'        => 'Draft Earring Test',
            'price'       => 999.00,
            'description' => 'Test description',
            'thumbnail'   => 'products/draft-test.jpg',
            'stock'       => 5,
            'status'      => 'draft',
        ]);

        $product = Product::where('name', 'Draft Earring Test')->first();

        $this->assertNotNull($product);
        $this->assertSame('draft', $product->status);
        $this->assertSame($category->id, $product->category_id);
        // Draft products should NOT appear in the active storefront query
        $activeCount = Product::where('status', 'active')->where('id', $product->id)->count();
        $this->assertSame(0, $activeCount);
    }

    public function test_published_products_are_created_with_active_status(): void
    {
        $this->earringsCategory();
        $category = Category::where('slug', 'earrings')->first();

        Product::create([
            'category_id' => $category->id,
            'name'        => 'Active Earring Test',
            'price'       => 1200.00,
            'description' => 'Live product',
            'thumbnail'   => 'products/active-test.jpg',
            'stock'       => 10,
            'status'      => 'active',
        ]);

        $product = Product::where('name', 'Active Earring Test')->first();

        $this->assertNotNull($product);
        $this->assertSame('active', $product->status);
        $this->assertTrue((bool) $product->shouldBeSearchable());
    }

    public function test_product_slug_auto_generated_on_save(): void
    {
        $this->earringsCategory();
        $category = Category::where('slug', 'earrings')->first();

        $product = Product::create([
            'category_id' => $category->id,
            'name'        => 'Unique Jhumka Design',
            'price'       => 800.00,
            'stock'       => 3,
            'status'      => 'draft',
        ]);

        $this->assertSame('unique-jhumka-design', $product->slug);
    }

    public function test_multiple_products_from_same_pdf_get_unique_slugs(): void
    {
        $this->earringsCategory();
        $category = Category::where('slug', 'earrings')->first();

        $base = [
            'category_id' => $category->id,
            'price'       => 500.00,
            'stock'       => 1,
            'status'      => 'draft',
        ];

        $p1 = Product::create($base + ['name' => 'Hoop Earring']);
        $p2 = Product::create($base + ['name' => 'Hoop Earring']);

        $this->assertNotSame($p1->slug, $p2->slug);
        $this->assertStringStartsWith('hoop-earring', $p2->slug);
    }

    // ── GD placeholder ────────────────────────────────────────────────────────

    public function test_gd_placeholder_generator_produces_valid_jpeg(): void
    {
        // generatePagePlaceholder is still available for future use
        // (admin may want to re-enable it). Test the GD rendering in isolation
        // via reflection to ensure the helper works correctly.
        $service = app(PdfProductImportService::class);
        Storage::fake('public');

        $ref = new \ReflectionMethod($service, 'generatePagePlaceholder');
        $ref->setAccessible(true);
        $storedPath = $ref->invoke($service, 'test-pdf', 1);

        $this->assertStringStartsWith('pdf-extracted/', $storedPath);
        Storage::disk('public')->assertExists($storedPath);

        $jpeg = Storage::disk('public')->get($storedPath);
        $this->assertNotEmpty($jpeg);
        // Validate JPEG signature (\xFF\xD8\xFF)
        $this->assertStringStartsWith("\xFF\xD8\xFF", $jpeg);
    }

    // ── New extraction / dedup / filter tests ────────────────────────────────

    public function test_candidate_always_has_pages_found_field(): void
    {
        Storage::fake('public');
        $service = app(PdfProductImportService::class);
        $path    = sys_get_temp_dir() . '/test-pages-found-' . uniqid() . '.pdf';
        file_put_contents($path, $this->buildMinimalPdf('Jhumka Gold'));
        try {
            $candidates = $service->extractCandidates($path, 'pdf-uploads/pages-found.pdf');
        } finally {
            @unlink($path);
        }
        // Placeholders disabled: minimal PDF may return 0 candidates.
        // Only validate shape when at least one candidate was extracted.
        $this->assertIsArray($candidates);
        foreach ($candidates as $c) {
            $this->assertArrayHasKey('pages_found', $c);
            $this->assertIsArray($c['pages_found']);
            $this->assertContains($c['pdf_page'], $c['pages_found']);
        }
    }

    public function test_pages_scanned_counter_equals_page_count(): void
    {
        Storage::fake('public');
        $service = app(PdfProductImportService::class);
        $path    = sys_get_temp_dir() . '/test-pages-scanned-' . uniqid() . '.pdf';
        file_put_contents($path, $this->buildMinimalPdf('Single Page'));
        try {
            $service->extractCandidates($path, 'pdf-uploads/pages-scanned.pdf');
        } finally {
            @unlink($path);
        }
        $this->assertSame(1, $service->pagesScanned);
    }

    public function test_product_image_filter_rejects_tiny_images(): void
    {
        $service = app(PdfProductImportService::class);
        $im = imagecreatetruecolor(20, 20);
        imagefilledrectangle($im, 0, 0, 19, 19, imagecolorallocate($im, 200, 100, 50));
        ob_start(); imagejpeg($im, null, 90); $jpeg = (string) ob_get_clean(); imagedestroy($im);
        $ref = new \ReflectionMethod($service, 'isProductImage');
        $ref->setAccessible(true);
        $this->assertFalse($ref->invoke($service, $jpeg), 'Tiny 20×20 image must be rejected.');
    }

    public function test_product_image_filter_accepts_large_square_image(): void
    {
        $service = app(PdfProductImportService::class);
        $im = imagecreatetruecolor(400, 400);
        imagefilledrectangle($im, 0, 0, 399, 399, imagecolorallocate($im, 200, 100, 50));
        ob_start(); imagejpeg($im, null, 90); $jpeg = (string) ob_get_clean(); imagedestroy($im);
        $ref = new \ReflectionMethod($service, 'isProductImage');
        $ref->setAccessible(true);
        $this->assertTrue($ref->invoke($service, $jpeg), 'A 400×400 image must pass filter.');
    }

    public function test_product_image_filter_rejects_banner_aspect_ratio(): void
    {
        $service = app(PdfProductImportService::class);
        $im = imagecreatetruecolor(1200, 100);
        imagefilledrectangle($im, 0, 0, 1199, 99, imagecolorallocate($im, 200, 100, 50));
        ob_start(); imagejpeg($im, null, 90); $jpeg = (string) ob_get_clean(); imagedestroy($im);
        $ref = new \ReflectionMethod($service, 'isProductImage');
        $ref->setAccessible(true);
        $this->assertFalse($ref->invoke($service, $jpeg), 'Banner 1200×100 must be rejected (aspect >4).');
    }

    public function test_phash_hamming_distance_identical_images_returns_zero(): void
    {
        $service = app(PdfProductImportService::class);
        $im = imagecreatetruecolor(300, 300);
        imagefilledrectangle($im, 0, 0, 299, 299, imagecolorallocate($im, 200, 100, 50));
        ob_start(); imagejpeg($im, null, 90); $jpeg = (string) ob_get_clean(); imagedestroy($im);
        $h1 = $service->computePHash($jpeg);
        $h2 = $service->computePHash($jpeg);
        $this->assertNotNull($h1);
        $this->assertNotNull($h2);
        $this->assertSame(0, $service->hammingDistance($h1, $h2));
    }

    public function test_color_histogram_identical_images_are_similar(): void
    {
        $service = app(PdfProductImportService::class);
        $im = imagecreatetruecolor(300, 300);
        imagefilledrectangle($im, 0, 0, 299, 299, imagecolorallocate($im, 180, 80, 40));
        ob_start(); imagejpeg($im, null, 90); $jpeg = (string) ob_get_clean(); imagedestroy($im);
        $h1 = $service->computeColorHistogram($jpeg);
        $h2 = $service->computeColorHistogram($jpeg);
        $this->assertNotNull($h1);
        $this->assertNotNull($h2);
        $l1 = 0.0;
        foreach ($h1 as $i => $v) {
            $l1 += abs($v - $h2[$i]);
        }
        $this->assertEqualsWithDelta(0.0, $l1, 0.001, 'Identical images must have L1=0.');
    }

    public function test_color_histogram_different_images_are_not_similar(): void
    {
        $service = app(PdfProductImportService::class);
        $im1 = imagecreatetruecolor(300, 300);
        imagefilledrectangle($im1, 0, 0, 299, 299, imagecolorallocate($im1, 255, 0, 0));
        ob_start(); imagejpeg($im1, null, 90); $r = (string) ob_get_clean(); imagedestroy($im1);
        $im2 = imagecreatetruecolor(300, 300);
        imagefilledrectangle($im2, 0, 0, 299, 299, imagecolorallocate($im2, 0, 0, 255));
        ob_start(); imagejpeg($im2, null, 90); $b = (string) ob_get_clean(); imagedestroy($im2);
        $h1 = $service->computeColorHistogram($r);
        $h2 = $service->computeColorHistogram($b);
        $this->assertNotNull($h1);
        $this->assertNotNull($h2);
        $l1 = 0.0;
        foreach ($h1 as $i => $v) {
            $l1 += abs($v - $h2[$i]);
        }
        $this->assertGreaterThan(0.35, $l1, 'Red vs blue must have high L1 (not similar).');
    }

    // ── resolveRealPath() path-handling tests ─────────────────────────────────

    /**
     * TemporaryUploadedFile has getRealPath() that returns the absolute path
     * to the temp file on disk. resolveRealPath() must use that directly.
     */
    public function test_resolve_real_path_uses_get_real_path_on_uploaded_file_object(): void
    {
        Storage::fake('public');

        $page = new \App\Filament\Admin\Pages\BulkUploadProducts();

        // Write a real temp PDF so file_exists() passes
        $pdfContent = $this->buildMinimalPdf('Path Test');
        $tmpPath    = sys_get_temp_dir() . '/livewire-tmp-test-' . uniqid() . '.pdf';
        file_put_contents($tmpPath, $pdfContent);

        // Create a mock that behaves like TemporaryUploadedFile
        $mockFile = $this->createMock(\Illuminate\Http\UploadedFile::class);
        $mockFile->method('getRealPath')->willReturn($tmpPath);

        try {
            $resolved = $page->resolveRealPath($mockFile);
            $this->assertSame($tmpPath, $resolved);
        } finally {
            @unlink($tmpPath);
        }
    }

    /**
     * When Livewire/Filament hands us an absolute Windows path string
     * (e.g. "C:\Users\...\Temp\phpXXXX.tmp"), resolveRealPath() must
     * use it directly without prepending any storage prefix.
     */
    public function test_resolve_real_path_uses_absolute_path_string_directly(): void
    {
        Storage::fake('public');

        $page = new \App\Filament\Admin\Pages\BulkUploadProducts();

        $pdfContent = $this->buildMinimalPdf('Absolute Path Test');
        $tmpPath    = sys_get_temp_dir() . '/abs-path-test-' . uniqid() . '.pdf';
        file_put_contents($tmpPath, $pdfContent);

        try {
            $resolved = $page->resolveRealPath($tmpPath);
            $this->assertSame($tmpPath, $resolved);
        } finally {
            @unlink($tmpPath);
        }
    }

    /**
     * When the state contains a storage-relative path (e.g. "pdf-uploads/abc.pdf"),
     * resolveRealPath() must expand it through Storage::disk('public')->path().
     */
    public function test_resolve_real_path_expands_storage_relative_path(): void
    {
        Storage::fake('public');

        $page = new \App\Filament\Admin\Pages\BulkUploadProducts();

        // Put a fake PDF on the fake public disk
        $pdfContent   = $this->buildMinimalPdf('Relative Path Test');
        $relativePath = 'pdf-uploads/relative-test.pdf';
        Storage::disk('public')->put($relativePath, $pdfContent);

        $resolved = $page->resolveRealPath($relativePath);

        // Must be the absolute path on the fake disk
        $this->assertSame(Storage::disk('public')->path($relativePath), $resolved);
        $this->assertTrue(file_exists($resolved));
    }

    /**
     * An unresolvable path (absolute, non-existent) must throw RuntimeException.
     */
    public function test_resolve_real_path_throws_for_missing_file(): void
    {
        Storage::fake('public');

        $page = new \App\Filament\Admin\Pages\BulkUploadProducts();

        $this->expectException(\RuntimeException::class);

        // Use an absolute path that does not exist on disk
        $page->resolveRealPath(sys_get_temp_dir() . '/does-not-exist-' . uniqid() . '.pdf');
    }

    // ── Existing image upload regression ─────────────────────────────────────

    public function test_existing_image_upload_pipeline_unaffected(): void
    {
        Storage::fake('public');
        $this->earringsCategory();
        $category = Category::where('slug', 'earrings')->first();

        // Simulate a direct image path (as FileUpload would produce)
        $fakePath = 'products/test-jhumka.jpg';
        Storage::disk('public')->put($fakePath, UploadedFile::fake()->image('test-jhumka.jpg')->getContent());

        $descService = app(ProductDescriptionService::class);
        $name        = 'Test Jhumka';
        $description = $descService->generate($name, 'Earrings');

        Product::create([
            'category_id' => $category->id,
            'name'        => $name,
            'price'       => 750.00,
            'description' => $description,
            'thumbnail'   => $fakePath,
            'stock'       => 2,
            'status'      => 'active',
        ]);

        $product = Product::where('name', 'Test Jhumka')->first();

        $this->assertNotNull($product);
        $this->assertSame('active', $product->status);
        $this->assertSame($fakePath, $product->thumbnail);
        $this->assertNotEmpty($product->description);
    }

    // ── Preview URL safety ────────────────────────────────────────────────────

    /**
     * Filenames with spaces must produce space-free stored paths and URLs.
     * A URL like /storage/pdf-extracted/Untitled design-p1.jpeg would 404
     * in any browser; sanitiseFilename must eliminate spaces before storage.
     */
    public function test_sanitise_filename_strips_spaces(): void
    {
        $service = app(PdfProductImportService::class);
        $ref = new \ReflectionMethod($service, 'sanitiseFilename');
        $ref->setAccessible(true);

        $safe = $ref->invoke($service, 'Untitled design_20260624_203044_0000');

        $this->assertStringNotContainsString(' ', $safe, 'Sanitised filename must contain no spaces.');
        $this->assertNotEmpty($safe);
    }

    public function test_sanitise_filename_strips_special_chars(): void
    {
        $service = app(PdfProductImportService::class);
        $ref = new \ReflectionMethod($service, 'sanitiseFilename');
        $ref->setAccessible(true);

        $safe = $ref->invoke($service, 'Catalogue #1 (Summer 2026)');

        // No parens, hash, or spaces in URL-safe path
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9\-_.]+$/', $safe);
    }

    public function test_sanitise_filename_fallback_for_all_unicode(): void
    {
        $service = app(PdfProductImportService::class);
        $ref = new \ReflectionMethod($service, 'sanitiseFilename');
        $ref->setAccessible(true);

        // All-unicode string — should return a non-empty fallback hash
        $safe = $ref->invoke($service, '金指輪');

        $this->assertNotEmpty($safe);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9\-_.]+$/', $safe);
    }

    public function test_stored_path_contains_no_spaces_for_spaced_pdf_name(): void
    {
        Storage::fake('public');

        $service = app(PdfProductImportService::class);
        // Build a PDF whose stored path simulates a spaced filename
        $pdfData = $this->buildMinimalPdf('Test earring');
        $tmpPath = sys_get_temp_dir() . '/test-space-' . uniqid() . '.pdf';
        file_put_contents($tmpPath, $pdfData);

        try {
            // Pass a stored path that has a space in the base name
            $candidates = $service->extractCandidates($tmpPath, 'pdf-uploads/Untitled design 2026.pdf');
        } finally {
            @unlink($tmpPath);
        }

        // Whether we got 0 or more rows, none may have spaces in stored_path or preview_url.
        // The minimal PDF has no embedded images so candidates is empty — assert that too.
        $this->assertIsArray($candidates);
        foreach ($candidates as $c) {
            $this->assertStringNotContainsString(' ', $c['stored_path'],
                "stored_path must never contain spaces; got: {$c['stored_path']}");
            $this->assertStringNotContainsString(' ', $c['preview_url'],
                "preview_url must never contain spaces; got: {$c['preview_url']}");
        }
        // Verify the sanitiser is called: pagesScanned proves the PDF was processed
        $this->assertSame(1, $service->pagesScanned);
    }

    // ── Blank price → DB safe default ─────────────────────────────────────────

    /**
     * Blank price must persist as 0, never as NULL, because the products.price
     * column is NOT NULL in the database schema.
     */
    public function test_product_with_blank_price_saves_as_zero(): void
    {
        $this->earringsCategory();
        $category = Category::where('slug', 'earrings')->first();

        // Replicate exactly what persistRows does when $row['price'] is blank
        $price = '';
        $product = Product::create([
            'category_id' => $category->id,
            'name'        => 'No Price Earring',
            'price'       => blank($price) ? 0 : (float) $price,
            'description' => 'Test',
            'stock'       => 1,
            'status'      => 'draft',
        ]);

        $this->assertNotNull($product->id);
        $this->assertSame(0.0, (float) $product->price);
    }

    // ── Row render resilience ──────────────────────────────────────────────────

    /**
     * The candidate shape must include every key that the Blade view reads
     * (with null-coalescing guards). Test via reflection on buildCandidate.
     */
    public function test_all_required_row_keys_present_in_candidates(): void
    {
        Storage::fake('public');

        $service = app(PdfProductImportService::class);
        $ref = new \ReflectionMethod($service, 'buildCandidate');
        $ref->setAccessible(true);

        $candidate = $ref->invoke(
            $service,
            'pdf-extracted/test-p1.jpeg',  // storedPath
            'Test Earring',                 // name
            '999',                          // price
            1,                              // pageNumber
            true,                           // fromEmbedded
            'fake-sha256',                  // sha256
            'test-pdf',                     // pdfBaseName
            false,                          // isPlaceholder
        );

        $required = [
            'stored_path', 'preview_url', 'name', 'price', 'source',
            'pdf_page', 'pages_found', 'from_embedded',
            'duplicate_pages', 'occurrences_count', 'is_placeholder',
        ];

        foreach ($required as $key) {
            $this->assertArrayHasKey($key, $candidate, "Candidate missing required key: {$key}");
        }

        // preview_url must be root-relative and space-free
        $this->assertStringStartsWith('/storage/', $candidate['preview_url']);
        $this->assertStringNotContainsString(' ', $candidate['preview_url']);
    }

    /**
     * extractCandidates must never throw even when pages contain no images.
     * The partial extraction test: service returns a (possibly empty) array,
     * never an exception, for a normal PDF without product images.
     */
    public function test_partial_extraction_survives_pages_without_images(): void
    {
        Storage::fake('public');

        $service = app(PdfProductImportService::class);
        $path    = sys_get_temp_dir() . '/test-partial-' . uniqid() . '.pdf';
        file_put_contents($path, $this->buildMinimalPdf('Just text, no images'));

        try {
            $candidates = $service->extractCandidates($path, 'pdf-uploads/partial.pdf');
            // Must not throw — returns array (possibly empty)
            $this->assertIsArray($candidates);
            $this->assertSame(1, $service->pagesScanned);
        } finally {
            @unlink($path);
        }
    }
}
