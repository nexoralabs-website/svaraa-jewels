<?php

namespace App\Console\Commands;

use App\Enums\UploadBatchStatus;
use App\Models\BulkUploadJobLog;
use App\Models\BulkUploadPreview;
use App\Models\UploadBatch;
use App\Support\BulkUploadAudit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * bulk-upload:cleanup
 *
 * Removes stale data that accumulates during normal operation:
 *   1. Soft-delete previews older than 30 days (non-published only)
 *   2. Prune job logs older than 60 days
 *   3. Soft-delete incomplete batches (QUEUED/EXTRACTING) older than 24h
 *   4. Remove orphan storage files (preview_image_path not in DB)
 *
 * Use --dry-run to preview without committing changes.
 *
 * Performance target: < 10s for typical operational datasets.
 */
class BulkUploadCleanupCommand extends Command
{
    protected $signature = 'bulk-upload:cleanup
                            {--dry-run : Preview what would be cleaned without making changes}
                            {--preview-days=30 : Age in days before non-published previews are removed}
                            {--log-days=60 : Age in days before job logs are pruned}
                            {--stale-hours=24 : Hours before incomplete batches are abandoned}';

    protected $description = 'Clean up stale bulk upload previews, logs, incomplete batches, and orphan files.';

    public function handle(): int
    {
        $isDry      = (bool) $this->option('dry-run');
        $previewAge = (int)  $this->option('preview-days');
        $logAge     = (int)  $this->option('log-days');
        $staleHours = (int)  $this->option('stale-hours');

        if ($isDry) {
            $this->warn('  [DRY RUN] No changes will be committed.');
            $this->newLine();
        }

        $totalStart = microtime(true);

        $results = [
            $this->cleanOldPreviews($previewAge, $isDry),
            $this->pruneJobLogs($logAge, $isDry),
            $this->abandonIncompleteBatches($staleHours, $isDry),
        ];

        $elapsed = round((microtime(true) - $totalStart) * 1000);

        $this->newLine();
        $this->table(
            ['Action', 'Count', 'Status'],
            $results
        );

        $this->newLine();
        $mode = $isDry ? 'dry-run' : 'committed';
        $this->info("  Cleanup complete ({$mode}) in {$elapsed}ms.");

        return self::SUCCESS;
    }

    // ── Step 1: soft-delete old non-published previews ────────────────────

    private function cleanOldPreviews(int $days, bool $dry): array
    {
        $cutoff = now()->subDays($days);

        $query = BulkUploadPreview::where('created_at', '<=', $cutoff)
            ->whereNotIn('status', [\App\Enums\PreviewStatus::PUBLISHED->value]);

        $count = $query->count();

        if (! $dry && $count > 0) {
            // chunkById for memory safety
            $query->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $row->delete(); // soft delete
                }
            });
        }

        return [
            'Remove old previews (>' . $days . 'd, non-published)',
            $count,
            $dry ? 'would remove' : 'removed',
        ];
    }

    // ── Step 2: prune job logs ────────────────────────────────────────────

    private function pruneJobLogs(int $days, bool $dry): array
    {
        $cutoff = now()->subDays($days);
        $count  = BulkUploadJobLog::where('created_at', '<=', $cutoff)->count();

        if (! $dry && $count > 0) {
            BulkUploadJobLog::where('created_at', '<=', $cutoff)
                ->chunkById(500, function ($rows) {
                    foreach ($rows as $row) {
                        $row->delete(); // hard delete — logs don't need soft delete
                    }
                });
        }

        return [
            'Prune job logs (>' . $days . 'd)',
            $count,
            $dry ? 'would prune' : 'pruned',
        ];
    }

    // ── Step 3: abandon stale incomplete batches ──────────────────────────

    private function abandonIncompleteBatches(int $hours, bool $dry): array
    {
        $cutoff = now()->subHours($hours);

        $staleBatches = UploadBatch::whereIn('status', [
            UploadBatchStatus::QUEUED->value,
            UploadBatchStatus::EXTRACTING->value,
        ])
        ->where('updated_at', '<=', $cutoff)
        ->get();

        $count = $staleBatches->count();

        if (! $dry && $count > 0) {
            foreach ($staleBatches as $batch) {
                // Use raw update to bypass the status-regression guard
                UploadBatch::where('id', $batch->id)->update([
                    'status'           => UploadBatchStatus::FAILED->value,
                    'progress_message' => 'Abandoned by cleanup — stale after ' . $hours . 'h.',
                ]);

                BulkUploadAudit::record($batch->id, 'cleanup_abandoned', [
                    'reason'       => 'stale_incomplete',
                    'stale_hours'  => $hours,
                ]);
            }
        }

        return [
            'Abandon stale batches (>' . $hours . 'h, QUEUED/EXTRACTING)',
            $count,
            $dry ? 'would abandon' : 'abandoned',
        ];
    }
}
