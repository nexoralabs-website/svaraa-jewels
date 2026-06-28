<?php

namespace Tests\Unit;

use App\Enums\PreviewStatus;
use App\Enums\UploadBatchStatus;
use App\Models\BulkUploadPreview;
use App\Models\Category;
use App\Models\UploadBatch;
use App\Services\PreviewValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreviewValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    private PreviewValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PreviewValidationService();
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function makeBatch(): UploadBatch
    {
        return UploadBatch::create(['status' => UploadBatchStatus::REVIEW_READY]);
    }

    /** Build an in-memory preview (not saved) with all valid fields. */
    private function validPreview(array $overrides = []): BulkUploadPreview
    {
        return new BulkUploadPreview(array_merge([
            'id'                 => 999,
            'name'               => 'Gold Ring',
            'category_id'        => 1,
            'price'              => 4999.00,
            'preview_image_path' => 'images/gold-ring.jpg',
            'sku'                => 'SKU-UNIQUE-001',
            'slug'               => 'gold-ring-unique',
            'content_hash'       => 'abc123unique',
            'status'             => PreviewStatus::NEEDS_REVIEW,
            'source'             => 'pdf',
            'ai_extracted_data'  => [],
        ], $overrides));
    }

    /** Persist a preview to the database (used for duplicate detection tests). */
    private function persistedPreview(array $overrides = []): BulkUploadPreview
    {
        $batch = $this->makeBatch();
        $cat   = Category::create(['name' => 'Test Cat ' . uniqid(), 'slug' => 'test-cat-' . uniqid(), 'status' => true]);

        return BulkUploadPreview::create(array_merge([
            'batch_uuid'         => $batch->id,
            'name'               => 'Existing Ring',
            'category_id'        => $cat->id,
            'price'              => 1000.00,
            'preview_image_path' => 'images/existing.jpg',
            'sku'                => 'SKU-EXISTING',
            'slug'               => 'existing-ring',
            'content_hash'       => 'existinghash123',
            'status'             => PreviewStatus::NEEDS_REVIEW,
            'source'             => 'pdf',
            'ai_extracted_data'  => [],
        ], $overrides));
    }

    // ── Return structure ──────────────────────────────────────────────────

    public function test_validate_returns_required_keys(): void
    {
        $result = $this->service->validate($this->validPreview());

        $this->assertArrayHasKey('blocking', $result);
        $this->assertArrayHasKey('warnings', $result);
        $this->assertArrayHasKey('score', $result);
    }

    public function test_valid_preview_has_no_blocking_errors(): void
    {
        $result = $this->service->validate($this->validPreview());

        $this->assertEmpty($result['blocking']);
    }

    public function test_valid_preview_has_score_100(): void
    {
        $result = $this->service->validate($this->validPreview());

        $this->assertSame(100, $result['score']);
    }

    // ── Blocking: missing name ────────────────────────────────────────────

    public function test_blocking_when_name_is_empty(): void
    {
        $result = $this->service->validate($this->validPreview(['name' => '']));

        $this->assertNotEmpty($result['blocking']);
        $this->assertStringContainsString('name', strtolower($result['blocking'][0]));
    }

    public function test_blocking_when_name_is_whitespace(): void
    {
        $result = $this->service->validate($this->validPreview(['name' => '   ']));

        $this->assertNotEmpty($result['blocking']);
    }

    public function test_blocking_when_name_is_null(): void
    {
        $result = $this->service->validate($this->validPreview(['name' => null]));

        $this->assertNotEmpty($result['blocking']);
    }

    // ── Blocking: missing category ────────────────────────────────────────

    public function test_blocking_when_category_missing(): void
    {
        $result = $this->service->validate($this->validPreview(['category_id' => null]));

        $blocking = implode(' ', $result['blocking']);
        $this->assertStringContainsString('ategory', $blocking);
    }

    // ── Blocking: invalid price ───────────────────────────────────────────

    public function test_blocking_when_price_is_zero(): void
    {
        $result = $this->service->validate($this->validPreview(['price' => 0]));

        $this->assertNotEmpty($result['blocking']);
    }

    public function test_blocking_when_price_is_negative(): void
    {
        $result = $this->service->validate($this->validPreview(['price' => -10]));

        $this->assertNotEmpty($result['blocking']);
    }

    public function test_blocking_when_price_is_null(): void
    {
        $result = $this->service->validate($this->validPreview(['price' => null]));

        $this->assertNotEmpty($result['blocking']);
    }

    // ── Blocking: invalid image path ──────────────────────────────────────

    public function test_blocking_when_image_path_empty(): void
    {
        $result = $this->service->validate($this->validPreview(['preview_image_path' => '']));

        $this->assertNotEmpty($result['blocking']);
    }

    public function test_blocking_when_image_path_null(): void
    {
        $result = $this->service->validate($this->validPreview(['preview_image_path' => null]));

        $this->assertNotEmpty($result['blocking']);
    }

    // ── hasBlockingErrors() convenience wrapper ───────────────────────────

    public function test_has_blocking_errors_true_for_invalid_preview(): void
    {
        $this->assertTrue(
            $this->service->hasBlockingErrors($this->validPreview(['name' => '']))
        );
    }

    public function test_has_blocking_errors_false_for_valid_preview(): void
    {
        $this->assertFalse(
            $this->service->hasBlockingErrors($this->validPreview())
        );
    }

    // ── Score calculation ─────────────────────────────────────────────────

    public function test_score_penalised_per_blocking_error(): void
    {
        // Two blocking errors: no name + no category
        $result = $this->service->validate(
            $this->validPreview(['name' => null, 'category_id' => null])
        );

        $this->assertLessThan(100, $result['score']);
        $this->assertGreaterThanOrEqual(0, $result['score']);
    }

    public function test_score_never_goes_below_zero(): void
    {
        // All four blocking checks fail
        $result = $this->service->validate($this->validPreview([
            'name'               => null,
            'category_id'        => null,
            'price'              => 0,
            'preview_image_path' => null,
        ]));

        $this->assertSame(0, $result['score']);
    }

    public function test_compute_score_directly(): void
    {
        // 1 blocking (−25) + 1 warning (−5) = 70
        $score = $this->service->computeScore(['one blocking'], ['one warning']);
        $this->assertSame(70, $score);
    }

    public function test_compute_score_with_no_issues(): void
    {
        $this->assertSame(100, $this->service->computeScore([], []));
    }

    // ── Warnings: duplicate SKU ───────────────────────────────────────────

    public function test_warning_for_duplicate_sku(): void
    {
        $existing = $this->persistedPreview(['sku' => 'DUPE-SKU']);

        // A different unsaved preview with the same SKU
        $preview = $this->validPreview(['sku' => 'DUPE-SKU', 'id' => 0]);

        $result = $this->service->validate($preview);

        $warnings = implode(' ', $result['warnings']);
        $this->assertStringContainsString('SKU', $warnings);
    }

    public function test_no_warning_when_sku_is_unique(): void
    {
        $result = $this->service->validate($this->validPreview(['sku' => 'TOTALLY-UNIQUE-XYZ']));

        $hasSku = false;
        foreach ($result['warnings'] as $w) {
            if (stripos($w, 'sku') !== false) {
                $hasSku = true;
            }
        }
        $this->assertFalse($hasSku);
    }

    // ── Warnings: duplicate slug ──────────────────────────────────────────

    public function test_warning_for_duplicate_slug(): void
    {
        $this->persistedPreview(['slug' => 'dupe-slug']);
        $preview = $this->validPreview(['slug' => 'dupe-slug', 'id' => 0]);

        $result  = $this->service->validate($preview);
        $warnings = implode(' ', $result['warnings']);

        $this->assertStringContainsString('slug', strtolower($warnings));
    }

    // ── Warnings: duplicate content hash ─────────────────────────────────

    public function test_warning_for_duplicate_content_hash(): void
    {
        $this->persistedPreview(['content_hash' => 'samehash999']);
        $preview = $this->validPreview(['content_hash' => 'samehash999', 'id' => 0]);

        $result  = $this->service->validate($preview);
        $warnings = implode(' ', $result['warnings']);

        $this->assertStringContainsString('fingerprint', strtolower($warnings));
    }

    // ── Warnings: duplicate name + category ───────────────────────────────

    public function test_warning_for_duplicate_name_and_category(): void
    {
        $existing = $this->persistedPreview(['name' => 'Dupe Ring']);
        // Use the same category_id that persistedPreview() just created
        $catId   = $existing->category_id;
        $preview = $this->validPreview(['name' => 'Dupe Ring', 'category_id' => $catId, 'id' => 0]);

        $result  = $this->service->validate($preview);
        $warnings = implode(' ', $result['warnings']);

        $this->assertStringContainsString('name', strtolower($warnings));
    }

    // ── Score includes warning penalty ────────────────────────────────────

    public function test_score_penalised_for_warnings(): void
    {
        $this->persistedPreview(['sku' => 'WARN-SKU-001']);
        $preview = $this->validPreview(['sku' => 'WARN-SKU-001', 'id' => 0]);

        $result = $this->service->validate($preview);

        // No blocking errors, but one warning (-5) → score should be 95
        $this->assertSame(0, count($result['blocking']));
        $this->assertLessThan(100, $result['score']);
    }
}
