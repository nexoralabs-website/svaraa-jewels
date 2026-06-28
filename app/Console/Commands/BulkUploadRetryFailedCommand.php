<?php

namespace App\Console\Commands;

use App\Enums\UploadBatchStatus;
use App\Events\UploadBatchRecovered;
use App\Jobs\GeneratePreviewMetadataJob;
use App\Jobs\ProcessPdfBulkUploadJob;
use App\Models\BulkUploadJobLog;
use App\Models\UploadBatch;
use App\Support\BulkUploadAudit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

/**
 * bulk-upload:retry-failed
 *
 * Finds FAILED batches and re-chains the processing pipeline.
 *
 * Behaviour:
 *  - Reads the source_file from batch metadata
 *  - Resets batch status to QUEUED (via raw update, bypassing regression guard)
 *  - Increments a retry_count field in batch metadata
 *  - Re-chains: ProcessPdfBulkUploadJob → GeneratePreviewMetadataJob
 *  - Fires UploadBatchRecovered event
 *  - Preserves all existing previews (idempotent extraction skips existing pages)
 *
 * Options:
 *   --batch=<uuid>   Retry a specific batch only
 *   --limit=<n>      Max batches to retry in one run (default: 10)
 *   --dry-run        Preview without dispatching jobs
 */
class BulkUploadRetryFailedCommand extends Command
{
    protected $signature = 'bulk-upload:retry-failed
                            {--batch= : UUID of a specific batch to retry}
                            {--limit=10 : Maximum number of batches to retry}
                            {--dry-run : Show what would be retried without dispatching}';

    protected $description = 'Re-chain the processing pipeline for FAILED upload batches.';

    public function handle(): int
    {
        $isDry     = (bool)  $this->option('dry-run');
        $limit     = (int)   $this->option('limit');
        $batchUuid = $this->option('batch');

        if ($isDry) {
            $this->warn('  [DRY RUN] No jobs will be dispatched.');
            $this->newLine();
        }

        $query = UploadBatch::where('status', UploadBatchStatus::FAILED->value);

        if ($batchUuid) {
            $query->where('id', $batchUuid);
        }

        $batches = $query->latest()->limit($limit)->get();

        if ($batches->isEmpty()) {
            $this->info('  No FAILED batches found.');
            return self::SUCCESS;
        }

        $this->info("  Found {$batches->count()} FAILED batch(es) to retry.");
        $this->newLine();

        $rows = [];

        foreach ($batches as $batch) {
            $meta       = $batch->metadata ?? [];
            $sourceFile = $meta['source_file'] ?? null;
            $totalPages = $batch->total_pages ?: 1;
            $retryCount = (int) ($meta['retry_count'] ?? 0) + 1;

            if (! $sourceFile) {
                $rows[] = [
                    substr($batch->id, 0, 8) . '…',
                    'SKIPPED',
                    'No source_file in metadata',
                ];
                continue;
            }

            if (! $isDry) {
                // Reset status to QUEUED via raw update (bypasses regression guard)
                UploadBatch::where('id', $batch->id)->update([
                    'status'           => UploadBatchStatus::QUEUED->value,
                    'progress_message' => "Retry #{$retryCount}…",
                    'processed_pages'  => 0,
                    'metadata'         => json_encode(array_merge($meta, [
                        'retry_count'    => $retryCount,
                        'last_retry_at'  => now()->toIso8601String(),
                    ])),
                ]);

                // Re-chain the pipeline
                Bus::chain([
                    new ProcessPdfBulkUploadJob($batch->id, $sourceFile, 1, $totalPages),
                    new GeneratePreviewMetadataJob($batch->id),
                ])->dispatch();

                // Audit trail
                BulkUploadAudit::record($batch->id, 'retry_dispatched', [
                    'retry_count' => $retryCount,
                    'source_file' => $sourceFile,
                ]);

                // Fire recovery event
                UploadBatchRecovered::dispatch($batch->fresh(), $retryCount);

                // Record retry attempt in job log
                BulkUploadJobLog::create([
                    'batch_uuid'  => $batch->id,
                    'job_name'    => static::class,
                    'status'      => 1, // running (chain just dispatched)
                    'attempt'     => $retryCount,
                    'worker'      => gethostname(),
                    'started_at'  => now(),
                ]);
            }

            $rows[] = [
                substr($batch->id, 0, 8) . '…',
                $isDry ? 'WOULD RETRY' : "RETRIED #{$retryCount}",
                $sourceFile,
            ];
        }

        $this->table(['Batch', 'Action', 'Source File'], $rows);
        $this->newLine();

        return self::SUCCESS;
    }
}
