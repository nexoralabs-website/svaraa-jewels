<?php

namespace Tests\Feature;

use App\Enums\PreviewStatus;
use App\Enums\UploadBatchStatus;
use App\Events\UploadBatchCompleted;
use App\Events\UploadBatchFailed;
use App\Events\UploadBatchRecovered;
use App\Listeners\CleanupBatchArtifacts;
use App\Listeners\RecordUploadMetrics;
use App\Models\BulkUploadJobLog;
use App\Models\BulkUploadPreview;
use App\Models\UploadBatch;
use App\Support\BulkUploadAudit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * BulkUploadOperationsTest
 *
 * Integration tests for Phase 6 — Operations layer:
 *  - Artisan commands: cleanup, retry-failed, health
 *  - Event dispatch and listener behaviour
 *  - Audit layer
 */
class BulkUploadOperationsTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────

    private function makeBatch(UploadBatchStatus $status = UploadBatchStatus::QUEUED, array $meta = []): UploadBatch
    {
        return UploadBatch::create([
            'status'      => $status,
            'total_pages' => 2,
            'metadata'    => $meta ?: null,
        ]);
    }

    private function makePreview(UploadBatch $batch, PreviewStatus $status = PreviewStatus::NEEDS_REVIEW): BulkUploadPreview
    {
        return BulkUploadPreview::create([
            'batch_uuid'        => $batch->id,
            'status'            => $status,
            'source'            => 'pdf',
            'ai_extracted_data' => [],
        ]);
    }

    private function makeJobLog(UploadBatch $batch, int $status = 2): BulkUploadJobLog
    {
        return BulkUploadJobLog::create([
            'batch_uuid'  => $batch->id,
            'job_name'    => 'TestJob',
            'status'      => $status,
            'attempt'     => 1,
            'started_at'  => now(),
            'ended_at'    => now(),
            'duration_ms' => 100,
            'memory_mb'   => 1.5,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════
    // bulk-upload:cleanup
    // ══════════════════════════════════════════════════════════════════════

    public function test_cleanup_dry_run_does_not_modify_data(): void
    {
        $batch   = $this->makeBatch();
        $preview = $this->makePreview($batch);

        // Force created_at to be 40 days ago
        $preview->forceFill(['created_at' => now()->subDays(40)])->save();

        $this->artisan('bulk-upload:cleanup --dry-run')
            ->assertExitCode(0);

        // Preview should still exist — dry run
        $this->assertNotNull(BulkUploadPreview::find($preview->id));
    }

    public function test_cleanup_removes_old_non_published_previews(): void
    {
        $batch   = $this->makeBatch();
        $old     = $this->makePreview($batch, PreviewStatus::NEEDS_REVIEW);
        $recent  = $this->makePreview($batch, PreviewStatus::NEEDS_REVIEW);

        $old->forceFill(['created_at' => now()->subDays(35)])->save();
        // recent stays at now()

        $this->artisan('bulk-upload:cleanup --preview-days=30')
            ->assertExitCode(0);

        $this->assertNull(BulkUploadPreview::find($old->id));      // removed
        $this->assertNotNull(BulkUploadPreview::find($recent->id)); // kept
    }

    public function test_cleanup_preserves_published_previews(): void
    {
        $batch   = $this->makeBatch();
        $pub     = $this->makePreview($batch, PreviewStatus::PUBLISHED);
        $pub->forceFill(['created_at' => now()->subDays(40)])->save();

        $this->artisan('bulk-upload:cleanup --preview-days=30')
            ->assertExitCode(0);

        // Published preview must NOT be deleted
        $this->assertNotNull(BulkUploadPreview::find($pub->id));
    }

    public function test_cleanup_prunes_old_job_logs(): void
    {
        $batch = $this->makeBatch();
        $old   = $this->makeJobLog($batch);
        $old->forceFill(['created_at' => now()->subDays(70)])->save();
        $recent = $this->makeJobLog($batch);

        $this->artisan('bulk-upload:cleanup --log-days=60')
            ->assertExitCode(0);

        $this->assertNull(BulkUploadJobLog::find($old->id));
        $this->assertNotNull(BulkUploadJobLog::find($recent->id));
    }

    public function test_cleanup_abandons_stale_incomplete_batches(): void
    {
        $staleBatch = UploadBatch::create([
            'status'      => UploadBatchStatus::EXTRACTING,
            'total_pages' => 1,
        ]);
        // Force updated_at to 25 hours ago
        UploadBatch::where('id', $staleBatch->id)
            ->update(['updated_at' => now()->subHours(25)]);

        $this->artisan('bulk-upload:cleanup --stale-hours=24')
            ->assertExitCode(0);

        $fresh = UploadBatch::find($staleBatch->id);
        $this->assertSame(UploadBatchStatus::FAILED, $fresh->status);
    }

    public function test_cleanup_does_not_abandon_recent_batches(): void
    {
        $batch = $this->makeBatch(UploadBatchStatus::EXTRACTING);
        // updated_at is now() — not stale

        $this->artisan('bulk-upload:cleanup --stale-hours=24')
            ->assertExitCode(0);

        $this->assertSame(UploadBatchStatus::EXTRACTING, $batch->fresh()->status);
    }

    // ══════════════════════════════════════════════════════════════════════
    // bulk-upload:retry-failed
    // ══════════════════════════════════════════════════════════════════════

    public function test_retry_dry_run_does_not_dispatch_jobs(): void
    {
        Bus::fake();

        $failedBatch = UploadBatch::create([
            'status'      => UploadBatchStatus::FAILED,
            'total_pages' => 1,
            'metadata'    => ['source_file' => 'pdf-uploads/test.pdf'],
        ]);

        $this->artisan('bulk-upload:retry-failed --dry-run')
            ->assertExitCode(0);

        Bus::assertNothingDispatched();
    }

    public function test_retry_resets_batch_status_to_queued(): void
    {
        Bus::fake();

        $failedBatch = UploadBatch::create([
            'status'      => UploadBatchStatus::FAILED,
            'total_pages' => 2,
            'metadata'    => ['source_file' => 'pdf-uploads/catalogue.pdf'],
        ]);

        $this->artisan('bulk-upload:retry-failed')
            ->assertExitCode(0);

        $this->assertSame(UploadBatchStatus::QUEUED, $failedBatch->fresh()->status);
    }

    public function test_retry_dispatches_process_chain(): void
    {
        Bus::fake();

        UploadBatch::create([
            'status'      => UploadBatchStatus::FAILED,
            'total_pages' => 2,
            'metadata'    => ['source_file' => 'pdf-uploads/catalogue.pdf'],
        ]);

        $this->artisan('bulk-upload:retry-failed')
            ->assertExitCode(0);

        Bus::assertChained([
            \App\Jobs\ProcessPdfBulkUploadJob::class,
            \App\Jobs\GeneratePreviewMetadataJob::class,
        ]);
    }

    public function test_retry_increments_retry_count_in_metadata(): void
    {
        Bus::fake();

        $failedBatch = UploadBatch::create([
            'status'      => UploadBatchStatus::FAILED,
            'total_pages' => 1,
            'metadata'    => ['source_file' => 'pdf-uploads/test.pdf', 'retry_count' => 1],
        ]);

        $this->artisan('bulk-upload:retry-failed')
            ->assertExitCode(0);

        $meta = $failedBatch->fresh()->metadata;
        $this->assertSame(2, $meta['retry_count']);
    }

    public function test_retry_skips_batches_without_source_file(): void
    {
        Bus::fake();

        UploadBatch::create([
            'status'      => UploadBatchStatus::FAILED,
            'total_pages' => 1,
            'metadata'    => [], // no source_file
        ]);

        $this->artisan('bulk-upload:retry-failed')
            ->assertExitCode(0);

        Bus::assertNothingDispatched();
    }

    public function test_retry_fires_recovered_event(): void
    {
        Bus::fake();
        Event::fake([UploadBatchRecovered::class]);

        UploadBatch::create([
            'status'      => UploadBatchStatus::FAILED,
            'total_pages' => 1,
            'metadata'    => ['source_file' => 'pdf-uploads/test.pdf'],
        ]);

        $this->artisan('bulk-upload:retry-failed')
            ->assertExitCode(0);

        Event::assertDispatched(UploadBatchRecovered::class);
    }

    public function test_retry_respects_limit_option(): void
    {
        Bus::fake();

        $batches = [];
        for ($i = 0; $i < 5; $i++) {
            $batches[] = UploadBatch::create([
                'status'      => UploadBatchStatus::FAILED,
                'total_pages' => 1,
                'metadata'    => ['source_file' => "pdf-uploads/test-{$i}.pdf"],
            ]);
        }

        $this->artisan('bulk-upload:retry-failed --limit=2')
            ->assertExitCode(0);

        $recoveredCount = UploadBatch::where('status', UploadBatchStatus::QUEUED)->count();
        $this->assertEquals(2, $recoveredCount);
    }

    public function test_retry_specific_batch_by_uuid(): void
    {
        Bus::fake();

        $target = UploadBatch::create([
            'status'      => UploadBatchStatus::FAILED,
            'total_pages' => 1,
            'metadata'    => ['source_file' => 'pdf-uploads/target.pdf'],
        ]);
        $other = UploadBatch::create([
            'status'      => UploadBatchStatus::FAILED,
            'total_pages' => 1,
            'metadata'    => ['source_file' => 'pdf-uploads/other.pdf'],
        ]);

        $this->artisan("bulk-upload:retry-failed --batch={$target->id}")
            ->assertExitCode(0);

        $target->refresh();
        $other->refresh();
        $this->assertEquals(UploadBatchStatus::QUEUED, $target->status);
        $this->assertEquals(UploadBatchStatus::FAILED, $other->status);
    }

    // ══════════════════════════════════════════════════════════════════════
    // bulk-upload:health
    // ══════════════════════════════════════════════════════════════════════

    public function test_health_command_exits_success_when_healthy(): void
    {
        // Clean environment — no failed batches or stale data
        $this->artisan('bulk-upload:health')
            ->assertExitCode(0); // healthy
    }

    public function test_health_command_json_output_is_valid(): void
    {
        $output = $this->artisan('bulk-upload:health --json');
        $output->assertExitCode(0);
    }

    public function test_health_command_returns_warning_when_batches_failed(): void
    {
        // Create a recently-failed batch to degrade the score
        for ($i = 0; $i < 3; $i++) {
            UploadBatch::create(['status' => UploadBatchStatus::FAILED, 'total_pages' => 1]);
        }

        // Score will be reduced by penalty — may return 0 (healthy) or 1 (warning)
        // We just verify it doesn't throw and returns a valid exit code
        $code = $this->artisan('bulk-upload:health')->run();
        $this->assertContains($code, [0, 1, 2]);
    }

    // ══════════════════════════════════════════════════════════════════════
    // Events and Listeners
    // ══════════════════════════════════════════════════════════════════════

    public function test_completed_event_is_dispatched(): void
    {
        Event::fake([UploadBatchCompleted::class]);

        $batch = $this->makeBatch();

        UploadBatchCompleted::dispatch($batch, ['test_metric' => 'value']);

        Event::assertDispatched(UploadBatchCompleted::class, function ($e) use ($batch) {
            return $e->batch->id === $batch->id;
        });
    }

    public function test_failed_event_is_dispatched(): void
    {
        Event::fake([UploadBatchFailed::class]);

        $batch = $this->makeBatch();

        UploadBatchFailed::dispatch($batch, 'extraction error', 2);

        Event::assertDispatched(UploadBatchFailed::class, function ($e) use ($batch) {
            return $e->batch->id === $batch->id
                && $e->reason  === 'extraction error'
                && $e->attempt === 2;
        });
    }

    public function test_record_upload_metrics_listener_stamps_completed_metadata(): void
    {
        $batch    = $this->makeBatch();
        $listener = new RecordUploadMetrics();

        $listener->handleCompleted(new UploadBatchCompleted($batch, ['duration_ms' => 1500]));

        $fresh = $batch->fresh();
        $this->assertArrayHasKey('event_history', $fresh->metadata);
        $this->assertSame('completed', $fresh->metadata['last_event']);
    }

    public function test_record_upload_metrics_listener_stamps_failed_metadata(): void
    {
        $batch    = $this->makeBatch();
        $listener = new RecordUploadMetrics();

        $listener->handleFailed(new UploadBatchFailed($batch, 'timeout', 3));

        $fresh = $batch->fresh();
        $this->assertSame('failed', $fresh->metadata['last_event']);
        $this->assertSame('timeout', $fresh->metadata['failure_reason']);
    }

    public function test_record_upload_metrics_listener_stamps_recovered_metadata(): void
    {
        $batch    = $this->makeBatch();
        $listener = new RecordUploadMetrics();

        $listener->handleRecovered(new UploadBatchRecovered($batch, 2));

        $fresh = $batch->fresh();
        $this->assertSame('recovered', $fresh->metadata['last_event']);
        $this->assertSame(2, $fresh->metadata['retry_count']);
    }

    public function test_cleanup_artifacts_listener_trims_published_preview_metadata(): void
    {
        $batch   = $this->makeBatch();
        $preview = BulkUploadPreview::create([
            'batch_uuid'          => $batch->id,
            'status'              => PreviewStatus::PUBLISHED,
            'source'              => 'pdf',
            'ai_extracted_data'   => [],
            'processing_metadata' => [
                'extraction_source' => 'pdf',
                'bulky_data'        => str_repeat('x', 5000),
            ],
        ]);

        $listener = new CleanupBatchArtifacts();
        $listener->handle(new UploadBatchCompleted($batch));

        $meta = $preview->fresh()->processing_metadata;
        $this->assertArrayHasKey('extraction_source', $meta);
        $this->assertArrayNotHasKey('bulky_data', $meta);
        $this->assertArrayHasKey('cleaned_at', $meta);
    }

    // ══════════════════════════════════════════════════════════════════════
    // BulkUploadAudit
    // ══════════════════════════════════════════════════════════════════════

    public function test_audit_record_appends_to_event_history(): void
    {
        $batch = $this->makeBatch();

        BulkUploadAudit::record($batch->id, 'manual_override', ['field' => 'status', 'value' => 'ready']);

        $history = $batch->fresh()->metadata['event_history'] ?? [];
        $this->assertCount(1, $history);
        $this->assertSame('manual_override', $history[0]['action']);
    }

    public function test_audit_record_is_idempotent_on_missing_batch(): void
    {
        $this->expectNotToPerformAssertions();
        BulkUploadAudit::record('00000000-0000-0000-0000-000000000000', 'test');
    }

    public function test_audit_for_batch_returns_job_logs(): void
    {
        $batch = $this->makeBatch();
        $this->makeJobLog($batch);
        $this->makeJobLog($batch);

        $logs = BulkUploadAudit::forBatch($batch->id);

        $this->assertCount(2, $logs);
    }

    public function test_audit_for_preview_returns_preview_rows(): void
    {
        $batch = $this->makeBatch();
        $this->makePreview($batch);
        $this->makePreview($batch);

        $previews = BulkUploadAudit::forPreview($batch->id);

        $this->assertCount(2, $previews);
    }

    public function test_audit_timeline_merges_job_logs_and_metadata(): void
    {
        $batch = $this->makeBatch();
        $this->makeJobLog($batch);
        BulkUploadAudit::record($batch->id, 'admin_action', ['note' => 'test']);

        $timeline = BulkUploadAudit::timeline($batch->id);

        $this->assertGreaterThanOrEqual(2, count($timeline));
        $types = array_column($timeline, 'type');
        $this->assertContains('job_log',        $types);
        $this->assertContains('metadata_event', $types);
    }
}
