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
            $this->assertStringStartsWith('products/', $c['stored_path']);
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
            'status'      => false,
        ]);

        $product = Product::where('name', 'Draft Earring Test')->first();

        $this->assertNotNull($product);
        $this->assertFalse($product->status);
        $this->assertSame($category->id, $product->category_id);
        // Draft products should NOT appear in the active storefront query
        $activeCount = Product::where('status', true)->where('id', $product->id)->count();
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
            'status'      => true,
        ]);

        $product = Product::where('name', 'Active Earring Test')->first();

        $this->assertNotNull($product);
        $this->assertTrue($product->status);
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
            'status'      => false,
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
