<?php

namespace Tests\Unit;

use App\Enums\PreviewStatus;
use App\Enums\UploadBatchStatus;
use App\Models\BulkUploadJobLog;
use App\Models\BulkUploadPreview;
use App\Models\UploadBatch;
use App\Services\BulkUploadMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for BulkUploadMetricsService.
 *
 * Covers: getBatchMetrics(), getSystemMetrics(), dashboard() — correctness,
 * edge cases (empty DB, division by zero), aggregate accuracy.
 */
class BulkUploadMetricsServiceTest extends TestCase
{
    use RefreshDatabase;

    private BulkUploadMetricsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BulkUploadMetricsService();
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function makeBatch(UploadBatchStatus $status = UploadBatchStatus::COMPLETED, int $pages = 3): UploadBatch
    {
        return UploadBatch::create([
            'status'           => $status,
            'total_pages'      => $pages,
            'processed_pages'  => $pages,
        ]);
    }

    private function makePreview(UploadBatch $batch, PreviewStatus $status = PreviewStatus::PUBLISHED): BulkUploadPreview
    {
        return BulkUploadPreview::create([
            'batch_uuid'        => $batch->id,
            'status'            => $status,
            'source'            => 'pdf',
            'ai_extracted_data' => [],
        ]);
    }

    private function makeLog(UploadBatch $batch, int $status = 2, int $durationMs = 500, float $memMb = 2.0, int $attempt = 1): BulkUploadJobLog
    {
        return BulkUploadJobLog::create([
            'batch_uuid'  => $batch->id,
            'job_name'    => 'TestJob',
            'status'      => $status,
            'attempt'     => $attempt,
            'started_at'  => now()->subMilliseconds($durationMs),
            'ended_at'    => now(),
            'duration_ms' => $durationMs,
            'memory_mb'   => $memMb,
        ]);
    }

    // ── getBatchMetrics() ─────────────────────────────────────────────────

    public function test_get_batch_metrics_returns_not_found_for_missing_uuid(): void
    {
        $result = $this->service->getBatchMetrics('00000000-0000-0000-0000-000000000000');
        $this->assertSame('not_found', $result['error']);
    }

    public function test_get_batch_metrics_returns_correct_status(): void
    {
        $batch  = $this->makeBatch(UploadBatchStatus::COMPLETED);
        $result = $this->service->getBatchMetrics($batch->id);
        $this->assertSame('COMPLETED', $result['status']);
    }

    public function test_get_batch_metrics_counts_previews_by_status(): void
    {
        $batch = $this->makeBatch();
        $this->makePreview($batch, PreviewStatus::PUBLISHED);
        $this->makePreview($batch, PreviewStatus::PUBLISHED);
        $this->makePreview($batch, PreviewStatus::FAILED);

        $result = $this->service->getBatchMetrics($batch->id);

        $this->assertSame(3, $result['previews_total']);
        $this->assertSame(2, $result['previews_published']);
        $this->assertSame(1, $result['previews_failed']);
    }

    public function test_get_batch_metrics_computes_publish_success_rate(): void
    {
        $batch = $this->makeBatch();
        $this->makePreview($batch, PreviewStatus::PUBLISHED);
        $this->makePreview($batch, PreviewStatus::PUBLISHED);
        $this->makePreview($batch, PreviewStatus::FAILED);

        $result = $this->service->getBatchMetrics($batch->id);

        // 2/3 = 0.6667
        $this->assertEqualsWithDelta(0.6667, $result['publish_success_rate'], 0.001);
    }

    public function test_get_batch_metrics_success_rate_is_zero_when_no_previews(): void
    {
        $batch  = $this->makeBatch();
        $result = $this->service->getBatchMetrics($batch->id);

        $this->assertSame(0.0, $result['publish_success_rate']);
        $this->assertSame(0.0, $result['publish_failure_rate']);
    }

    public function test_get_batch_metrics_averages_processing_time(): void
    {
        $batch = $this->makeBatch();
        $this->makeLog($batch, 2, 200, 1.0);
        $this->makeLog($batch, 2, 400, 3.0);

        $result = $this->service->getBatchMetrics($batch->id);

        $this->assertEqualsWithDelta(300.0, $result['avg_processing_time_ms'], 1.0);
    }

    public function test_get_batch_metrics_averages_memory(): void
    {
        $batch = $this->makeBatch();
        $this->makeLog($batch, 2, 100, 2.0);
        $this->makeLog($batch, 2, 100, 4.0);

        $result = $this->service->getBatchMetrics($batch->id);

        $this->assertEqualsWithDelta(3.0, $result['avg_memory_mb'], 0.01);
    }

    public function test_get_batch_metrics_counts_retries(): void
    {
        $batch = $this->makeBatch();
        $this->makeLog($batch, 2, 100, 1.0, 1);
        $this->makeLog($batch, 2, 100, 1.0, 2); // retry
        $this->makeLog($batch, 2, 100, 1.0, 3); // retry

        $result = $this->service->getBatchMetrics($batch->id);

        $this->assertSame(2, $result['retry_count']); // 2 logs with attempt > 1
    }

    public function test_get_batch_metrics_returns_null_when_no_logs(): void
    {
        $batch  = $this->makeBatch();
        $result = $this->service->getBatchMetrics($batch->id);

        $this->assertNull($result['avg_processing_time_ms']);
        $this->assertNull($result['avg_memory_mb']);
        $this->assertNull($result['queue_wait_ms']);
    }

    // ── getSystemMetrics() ────────────────────────────────────────────────

    public function test_get_system_metrics_returns_zeros_when_empty(): void
    {
        $result = $this->service->getSystemMetrics();

        $this->assertSame(0, $result['total_batches']);
        $this->assertSame(0.0, $result['batch_completion_rate']);
        $this->assertSame(0.0, $result['publish_success_rate']);
    }

    public function test_get_system_metrics_counts_batches_by_status(): void
    {
        $this->makeBatch(UploadBatchStatus::COMPLETED);
        $this->makeBatch(UploadBatchStatus::COMPLETED);
        $this->makeBatch(UploadBatchStatus::FAILED);

        $result = $this->service->getSystemMetrics();

        $this->assertSame(3, $result['total_batches']);
        $this->assertSame(2, $result['completed_batches']);
        $this->assertSame(1, $result['failed_batches']);
    }

    public function test_get_system_metrics_computes_completion_rate(): void
    {
        $this->makeBatch(UploadBatchStatus::COMPLETED);
        $this->makeBatch(UploadBatchStatus::COMPLETED);
        $this->makeBatch(UploadBatchStatus::FAILED);

        $result = $this->service->getSystemMetrics();

        $this->assertEqualsWithDelta(0.6667, $result['batch_completion_rate'], 0.001);
    }

    public function test_get_system_metrics_computes_publish_success_rate(): void
    {
        $batch = $this->makeBatch();
        $this->makePreview($batch, PreviewStatus::PUBLISHED);
        $this->makePreview($batch, PreviewStatus::FAILED);

        $result = $this->service->getSystemMetrics();

        // 1/2 = 0.5
        $this->assertEqualsWithDelta(0.5, $result['publish_success_rate'], 0.001);
    }

    public function test_get_system_metrics_computes_avg_previews_per_batch(): void
    {
        $b1 = $this->makeBatch();
        $b2 = $this->makeBatch();
        $this->makePreview($b1);
        $this->makePreview($b1);
        $this->makePreview($b2);

        $result = $this->service->getSystemMetrics();

        // 3 previews / 2 batches = 1.5
        $this->assertEqualsWithDelta(1.5, $result['avg_previews_per_batch'], 0.01);
    }

    public function test_get_system_metrics_counts_retries(): void
    {
        $batch = $this->makeBatch();
        $this->makeLog($batch, 2, 100, 1.0, 1);
        $this->makeLog($batch, 2, 100, 1.0, 2);

        $result = $this->service->getSystemMetrics();

        $this->assertSame(1, $result['retry_count']); // 1 log with attempt > 1
    }

    // ── dashboard() ───────────────────────────────────────────────────────

    public function test_dashboard_returns_all_required_keys(): void
    {
        $result = $this->service->dashboard();

        $required = [
            'today_uploads', 'today_success_pct', 'avg_pages',
            'queue_load', 'retry_rate', 'health_score',
            'active_batches', 'total_published_today',
        ];

        foreach ($required as $key) {
            $this->assertArrayHasKey($key, $result, "dashboard() missing key: {$key}");
        }
    }

    public function test_dashboard_today_uploads_counts_only_todays_batches(): void
    {
        $this->makeBatch(); // today
        $old = $this->makeBatch();
        UploadBatch::where('id', $old->id)->update(['created_at' => now()->subDays(2)]);

        $result = $this->service->dashboard();

        $this->assertSame(1, $result['today_uploads']);
    }

    public function test_dashboard_active_batches_excludes_terminal_states(): void
    {
        $this->makeBatch(UploadBatchStatus::QUEUED);
        $this->makeBatch(UploadBatchStatus::EXTRACTING);
        $this->makeBatch(UploadBatchStatus::COMPLETED); // terminal
        $this->makeBatch(UploadBatchStatus::FAILED);    // terminal

        $result = $this->service->dashboard();

        $this->assertSame(2, $result['active_batches']);
    }

    public function test_dashboard_today_success_pct_is_100_when_all_complete(): void
    {
        $this->makeBatch(UploadBatchStatus::COMPLETED);
        $this->makeBatch(UploadBatchStatus::COMPLETED);

        $result = $this->service->dashboard();

        $this->assertSame(100, $result['today_success_pct']);
    }

    public function test_dashboard_health_score_is_in_valid_range(): void
    {
        $result = $this->service->dashboard();

        $this->assertGreaterThanOrEqual(0,   $result['health_score']);
        $this->assertLessThanOrEqual(100, $result['health_score']);
    }

    public function test_dashboard_retry_rate_is_between_zero_and_one(): void
    {
        $batch = $this->makeBatch();
        $this->makeLog($batch, 2, 100, 1.0, 1);
        $this->makeLog($batch, 2, 100, 1.0, 2);

        $result = $this->service->dashboard();

        $this->assertGreaterThanOrEqual(0.0, $result['retry_rate']);
        $this->assertLessThanOrEqual(1.0,    $result['retry_rate']);
    }

    public function test_dashboard_published_today_counts_only_todays_publishes(): void
    {
        $batch = $this->makeBatch();
        $today = BulkUploadPreview::create([
            'batch_uuid'        => $batch->id,
            'status'            => PreviewStatus::PUBLISHED,
            'source'            => 'pdf',
            'ai_extracted_data' => [],
            'published_at'      => now(),
        ]);
        $old = BulkUploadPreview::create([
            'batch_uuid'        => $batch->id,
            'status'            => PreviewStatus::PUBLISHED,
            'source'            => 'pdf',
            'ai_extracted_data' => [],
            'published_at'      => now()->subDays(2),
        ]);

        $result = $this->service->dashboard();

        $this->assertSame(1, $result['total_published_today']);
    }
}
