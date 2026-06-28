<?php

namespace Tests\Unit;

use App\Enums\UploadBatchStatus;
use App\Models\BulkUploadJobLog;
use App\Models\UploadBatch;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkUploadJobLogTest extends TestCase
{
    use RefreshDatabase;

    private function makeBatch(): UploadBatch
    {
        return UploadBatch::create([
            'status' => UploadBatchStatus::PROCESSING,
        ]);
    }

    // ── Casts ─────────────────────────────────────────────────────────────

    public function test_started_at_is_cast_to_datetime(): void
    {
        $batch = $this->makeBatch();
        $log   = BulkUploadJobLog::create([
            'batch_uuid' => $batch->id,
            'job_name'   => 'TestJob',
            'status'     => 1,
            'started_at' => '2026-01-01 10:00:00',
        ]);

        $this->assertInstanceOf(Carbon::class, $log->fresh()->started_at);
    }

    public function test_ended_at_is_cast_to_datetime(): void
    {
        $batch = $this->makeBatch();
        $log   = BulkUploadJobLog::create([
            'batch_uuid' => $batch->id,
            'job_name'   => 'TestJob',
            'status'     => 2,
            'ended_at'   => '2026-01-01 10:00:05',
        ]);

        $this->assertInstanceOf(Carbon::class, $log->fresh()->ended_at);
    }

    // ── Relationships ─────────────────────────────────────────────────────

    public function test_batch_relationship(): void
    {
        $batch = $this->makeBatch();
        $log   = BulkUploadJobLog::create([
            'batch_uuid' => $batch->id,
            'job_name'   => 'TestJob',
            'status'     => 0,
        ]);

        $this->assertInstanceOf(UploadBatch::class, $log->batch);
        $this->assertSame($batch->id, $log->batch->id);
    }

    // ── durationSeconds() ────────────────────────────────────────────────

    public function test_duration_seconds_returns_null_when_not_set(): void
    {
        $log = new BulkUploadJobLog(['duration_ms' => null]);
        $this->assertNull($log->durationSeconds());
    }

    public function test_duration_seconds_converts_ms_to_seconds(): void
    {
        $log = new BulkUploadJobLog(['duration_ms' => 3500]);
        $this->assertSame(3.5, $log->durationSeconds());
    }

    public function test_duration_seconds_rounds_to_three_decimals(): void
    {
        $log = new BulkUploadJobLog(['duration_ms' => 1234]);
        $this->assertSame(1.234, $log->durationSeconds());
    }

    // ── isFailed() ────────────────────────────────────────────────────────

    public function test_is_failed_returns_true_for_status_3(): void
    {
        $log = new BulkUploadJobLog(['status' => 3]);
        $this->assertTrue($log->isFailed());
    }

    public function test_is_failed_returns_false_for_non_failed_statuses(): void
    {
        foreach ([0, 1, 2] as $status) {
            $log = new BulkUploadJobLog(['status' => $status]);
            $this->assertFalse($log->isFailed(), "Expected false for status {$status}");
        }
    }

    // ── Fillable coverage ─────────────────────────────────────────────────

    public function test_fillable_fields_persist(): void
    {
        $batch = $this->makeBatch();
        $log   = BulkUploadJobLog::create([
            'batch_uuid'  => $batch->id,
            'job_name'    => 'ExtractPdfJob',
            'status'      => 2,
            'attempt'     => 2,
            'worker'      => 'worker-1',
            'duration_ms' => 1500,
            'memory_mb'   => 64.5,
            'error'       => null,
        ]);

        $fresh = $log->fresh();
        $this->assertSame('ExtractPdfJob', $fresh->job_name);
        $this->assertSame(2, $fresh->attempt);
        $this->assertSame('worker-1', $fresh->worker);
        $this->assertSame(1500, $fresh->duration_ms);
    }
}
