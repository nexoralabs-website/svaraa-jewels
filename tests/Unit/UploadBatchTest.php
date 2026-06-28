<?php

namespace Tests\Unit;

use App\Enums\UploadBatchStatus;
use App\Models\BulkUploadJobLog;
use App\Models\BulkUploadPreview;
use App\Models\UploadBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class UploadBatchTest extends TestCase
{
    use RefreshDatabase;

    // ── Enum casting ──────────────────────────────────────────────────────

    public function test_status_is_cast_to_enum(): void
    {
        $batch = UploadBatch::create([
            'status' => UploadBatchStatus::QUEUED,
        ]);

        $fresh = $batch->fresh();
        $this->assertInstanceOf(UploadBatchStatus::class, $fresh->status);
        $this->assertSame(UploadBatchStatus::QUEUED, $fresh->status);
    }

    public function test_metadata_is_cast_to_array(): void
    {
        $batch = UploadBatch::create([
            'status'   => UploadBatchStatus::QUEUED,
            'metadata' => ['source' => 'test.pdf', 'pages' => 5],
        ]);

        $fresh = $batch->fresh();
        $this->assertIsArray($fresh->metadata);
        $this->assertSame('test.pdf', $fresh->metadata['source']);
    }

    // ── Relationships ─────────────────────────────────────────────────────

    public function test_user_relationship(): void
    {
        $user = User::factory()->create();

        $batch = UploadBatch::create([
            'user_id' => $user->id,
            'status'  => UploadBatchStatus::QUEUED,
        ]);

        $this->assertInstanceOf(User::class, $batch->user);
        $this->assertSame($user->id, $batch->user->id);
    }

    public function test_previews_relationship(): void
    {
        $batch = UploadBatch::create([
            'status' => UploadBatchStatus::REVIEW_READY,
        ]);

        BulkUploadPreview::create([
            'batch_uuid'        => $batch->id,
            'status'            => \App\Enums\PreviewStatus::NEEDS_REVIEW,
            'source'            => 'pdf',
            'ai_extracted_data' => [],
        ]);

        $this->assertCount(1, $batch->previews);
        $this->assertInstanceOf(BulkUploadPreview::class, $batch->previews->first());
    }

    public function test_job_logs_relationship(): void
    {
        $batch = UploadBatch::create([
            'status' => UploadBatchStatus::PROCESSING,
        ]);

        BulkUploadJobLog::create([
            'batch_uuid' => $batch->id,
            'job_name'   => 'ProcessPageJob',
            'status'     => 1,
        ]);

        $this->assertCount(1, $batch->jobLogs);
        $this->assertInstanceOf(BulkUploadJobLog::class, $batch->jobLogs->first());
    }

    // ── progressPercent() ─────────────────────────────────────────────────

    public function test_progress_percent_when_no_pages(): void
    {
        $batch = new UploadBatch(['total_pages' => 0, 'processed_pages' => 0]);
        $this->assertSame(0, $batch->progressPercent());
    }

    public function test_progress_percent_calculates_correctly(): void
    {
        $batch = new UploadBatch(['total_pages' => 10, 'processed_pages' => 4]);
        $this->assertSame(40, $batch->progressPercent());
    }

    public function test_progress_percent_caps_at_100(): void
    {
        $batch = new UploadBatch(['total_pages' => 5, 'processed_pages' => 10]);
        $this->assertSame(100, $batch->progressPercent());
    }

    public function test_progress_percent_returns_100_when_completed(): void
    {
        $batch = new UploadBatch([
            'status'          => UploadBatchStatus::COMPLETED,
            'total_pages'     => 0,
            'processed_pages' => 0,
        ]);
        $this->assertSame(100, $batch->progressPercent());
    }

    // ── isCompleted() ─────────────────────────────────────────────────────

    public function test_is_completed_returns_true_for_completed_status(): void
    {
        $batch = new UploadBatch(['status' => UploadBatchStatus::COMPLETED]);
        $this->assertTrue($batch->isCompleted());
    }

    public function test_is_completed_returns_false_for_other_statuses(): void
    {
        foreach ([UploadBatchStatus::QUEUED, UploadBatchStatus::PROCESSING, UploadBatchStatus::FAILED] as $status) {
            $batch = new UploadBatch(['status' => $status]);
            $this->assertFalse($batch->isCompleted(), "Expected false for {$status->name}");
        }
    }

    // ── Status regression guard ───────────────────────────────────────────

    public function test_status_regression_throws_exception(): void
    {
        $batch = UploadBatch::create([
            'status' => UploadBatchStatus::REVIEW_READY,
        ]);

        $this->expectException(LogicException::class);

        $batch->update(['status' => UploadBatchStatus::QUEUED]);
    }

    public function test_terminal_status_cannot_transition(): void
    {
        $batch = UploadBatch::create([
            'status' => UploadBatchStatus::COMPLETED,
        ]);

        $this->expectException(LogicException::class);

        $batch->update(['status' => UploadBatchStatus::PROCESSING]);
    }

    public function test_valid_forward_status_transition_works(): void
    {
        $batch = UploadBatch::create([
            'status' => UploadBatchStatus::QUEUED,
        ]);

        $batch->update(['status' => UploadBatchStatus::EXTRACTING]);

        $this->assertSame(UploadBatchStatus::EXTRACTING, $batch->fresh()->status);
    }

    // ── UUID primary key ──────────────────────────────────────────────────

    public function test_primary_key_is_uuid_string(): void
    {
        $batch = UploadBatch::create([
            'status' => UploadBatchStatus::QUEUED,
        ]);

        $this->assertIsString($batch->id);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $batch->id
        );
        $this->assertFalse($batch->incrementing);
    }
}
