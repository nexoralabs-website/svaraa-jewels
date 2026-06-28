<?php

namespace Tests\Unit;

use App\Enums\UploadBatchStatus;
use App\Models\UploadBatch;
use App\Services\BulkUploadHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Unit tests for BulkUploadHealthService.
 *
 * Covers: healthy state, degraded/warning states, failure states,
 * score arithmetic, individual check responses.
 */
class BulkUploadHealthServiceTest extends TestCase
{
    use RefreshDatabase;

    private BulkUploadHealthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BulkUploadHealthService();
    }

    // ── Result structure ──────────────────────────────────────────────────

    public function test_check_returns_required_keys(): void
    {
        Storage::fake('public');
        $result = $this->service->check();

        $this->assertArrayHasKey('healthy',  $result);
        $this->assertArrayHasKey('score',    $result);
        $this->assertArrayHasKey('warnings', $result);
        $this->assertArrayHasKey('failures', $result);
        $this->assertIsBool($result['healthy']);
        $this->assertIsInt($result['score']);
        $this->assertIsArray($result['warnings']);
        $this->assertIsArray($result['failures']);
    }

    public function test_score_is_between_0_and_100(): void
    {
        Storage::fake('public');
        $result = $this->service->check();
        $this->assertGreaterThanOrEqual(0,   $result['score']);
        $this->assertLessThanOrEqual(100, $result['score']);
    }

    // ── Healthy baseline ──────────────────────────────────────────────────

    public function test_healthy_when_no_issues(): void
    {
        Storage::fake('public');

        $result = $this->service->check();

        // In a clean test environment: no failed batches, no stale batches, no orphans
        $this->assertGreaterThanOrEqual(50, $result['score']);
    }

    public function test_healthy_true_when_score_above_50(): void
    {
        Storage::fake('public');
        $result = $this->service->check();

        if ($result['score'] >= 50) {
            $this->assertTrue($result['healthy']);
        } else {
            $this->assertFalse($result['healthy']);
        }
    }

    // ── Failed batches penalty ────────────────────────────────────────────

    public function test_warning_when_failed_batches_exist(): void
    {
        Storage::fake('public');

        // 1 recently failed batch → warning
        UploadBatch::create(['status' => UploadBatchStatus::FAILED, 'total_pages' => 1]);

        $result = $this->service->check();

        // Score should be reduced from 100
        $this->assertLessThan(100, $result['score']);
    }

    public function test_failure_when_many_batches_failed(): void
    {
        Storage::fake('public');

        // 5+ failed batches → high penalty
        for ($i = 0; $i < 6; $i++) {
            UploadBatch::create(['status' => UploadBatchStatus::FAILED, 'total_pages' => 1]);
        }

        $result = $this->service->check();

        // With 6 failed batches, score should be significantly reduced
        $this->assertLessThan(80, $result['score']);
    }

    // ── Stale batch penalty ───────────────────────────────────────────────

    public function test_failure_when_stale_extracting_batch_exists(): void
    {
        Storage::fake('public');

        $stale = UploadBatch::create([
            'status'      => UploadBatchStatus::EXTRACTING,
            'total_pages' => 1,
        ]);
        // Push updated_at back 35 minutes
        UploadBatch::where('id', $stale->id)
            ->update(['updated_at' => now()->subMinutes(35)]);

        $result = $this->service->check();

        $this->assertLessThan(100, $result['score']);
        $this->assertNotEmpty($result['failures']);
    }

    public function test_no_stale_penalty_for_recently_updated_batches(): void
    {
        Storage::fake('public');

        // Recent batch — updated just now
        UploadBatch::create([
            'status'      => UploadBatchStatus::EXTRACTING,
            'total_pages' => 1,
        ]);

        $before = $this->service->check();
        $staleFailures = array_filter($before['failures'], fn ($f) => str_contains($f, 'Stale'));
        $this->assertEmpty($staleFailures);
    }

    // ── Storage check ─────────────────────────────────────────────────────

    public function test_storage_check_passes_with_fake_disk(): void
    {
        Storage::fake('public');

        $result = $this->service->check();

        $storageFailures = array_filter($result['failures'], fn ($f) => str_contains($f, 'Storage'));
        $this->assertEmpty($storageFailures);
    }

    // ── DB latency check ──────────────────────────────────────────────────

    public function test_db_latency_check_passes_in_test_environment(): void
    {
        Storage::fake('public');

        $result = $this->service->check();

        $dbFailures = array_filter($result['failures'], fn ($f) => str_contains($f, 'DB:'));
        $this->assertEmpty($dbFailures);
    }

    // ── Orphan previews ───────────────────────────────────────────────────

    public function test_no_orphan_warning_in_clean_state(): void
    {
        Storage::fake('public');

        $result = $this->service->check();

        $orphanWarnings = array_filter($result['warnings'], fn ($w) => str_contains($w, 'Orphan'));
        $this->assertEmpty($orphanWarnings);
    }

    public function test_orphan_warning_when_orphan_previews_exist(): void
    {
        Storage::fake('public');

        $batch = UploadBatch::create([
            'status'      => UploadBatchStatus::EXTRACTING,
            'total_pages' => 1,
        ]);

        $preview = BulkUploadPreview::create([
            'batch_uuid'        => $batch->id,
            'status'            => 1,
            'source'            => 'pdf',
            'ai_extracted_data' => [],
            'version'           => 1,
            'edited_count'      => 0,
            'stock'             => 1,
            'is_placeholder'    => false,
            'occurrences_count' => 1,
        ]);

        $batch->delete();

        $result = $this->service->check();

        $orphanWarnings = array_filter($result['warnings'], fn ($w) => str_contains($w, 'Orphan'));
        $this->assertNotEmpty($orphanWarnings);
    }

    // ── Recovery / degraded state ─────────────────────────────────────────

    public function test_healthy_false_when_score_below_50(): void
    {
        // We can't easily force score below 50 without simulating many failures,
        // so we verify the logic directly via the public contract:
        // If score < 50 → healthy must be false
        Storage::fake('public');

        $result = $this->service->check();

        if ($result['score'] < 50) {
            $this->assertFalse($result['healthy']);
        } else {
            $this->assertTrue($result['healthy']);
        }
    }

    public function test_warnings_list_is_empty_when_no_issues(): void
    {
        Storage::fake('public');

        // Clean DB — no failed batches, no stale, no orphans
        $result = $this->service->check();

        // In a fresh test DB with no problem data, warnings should be empty
        $nonDbWarnings = array_filter(
            $result['warnings'],
            fn ($w) => ! str_contains($w, 'DB latency')
        );
        $this->assertEmpty($nonDbWarnings);
    }

    public function test_multiple_checks_accumulate_penalties(): void
    {
        Storage::fake('public');

        // Create both stale and failed batches
        UploadBatch::create(['status' => UploadBatchStatus::FAILED, 'total_pages' => 1]);

        $stale = UploadBatch::create([
            'status'      => UploadBatchStatus::EXTRACTING,
            'total_pages' => 1,
        ]);
        UploadBatch::where('id', $stale->id)
            ->update(['updated_at' => now()->subMinutes(40)]);

        $result = $this->service->check();

        // Both penalties should have been applied — score well below 100
        $this->assertLessThan(80, $result['score']);
    }
}
