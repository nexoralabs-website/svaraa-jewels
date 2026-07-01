<?php

namespace App\Services;

use App\Enums\UploadBatchStatus;
use App\Jobs\ProcessPdfBulkUploadJob;
use App\Models\BulkUploadJobLog;
use App\Models\UploadBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class BulkUploadRecoveryService
{
    public function recoverBatch(string $batchUuid): bool
    {
        $batch = UploadBatch::find($batchUuid);
        if ($batch === null) {
            Log::warning("Attempted to recover non-existent batch: {$batchUuid}");
            return false;
        }

        try {
            DB::transaction(function () use ($batch) {
                $batch->update([
                    'status' => UploadBatchStatus::QUEUED,
                    'progress_message' => 'Recovery initiated. Re-queuing processing.',
                ]);

                BulkUploadJobLog::create([
                    'batch_uuid'  => $batch->id,
                    'job_name'    => __METHOD__,
                    'status'      => 1, // running
                    'attempt'     => 1,
                    'worker'      => gethostname(),
                    'started_at'  => now(),
                    'ended_at'    => null,
                    'duration_ms' => 0,
                    'memory_mb'   => 0,
                ]);
            });

            Log::info("Batch recovery initiated: {$batchUuid}");
            return true;
        } catch (\Throwable $e) {
            Log::error("Failed to recover batch {$batchUuid}: " . $e->getMessage());
            return false;
        }
    }

    public function retryBatch(string $batchUuid): bool
    {
        $batch = UploadBatch::find($batchUuid);
        if ($batch === null) {
            return false;
        }

        if (!in_array($batch->status, [UploadBatchStatus::FAILED], true)) {
            return false;
        }

        return $this->recoverBatch($batchUuid);
    }

    public function rollbackBatch(string $batchUuid): bool
    {
        $batch = UploadBatch::find($batchUuid);
        if ($batch === null) {
            return false;
        }

        try {
            DB::transaction(function () use ($batch) {
                $batch->previews()->each(function ($preview) {
                    if ($preview->published_product_id) {
                        $product = $preview->product;
                        if ($product) {
                            $product->delete();
                        }
                    }
                    $preview->update([
                        'status' => \App\Enums\PreviewStatus::NEEDS_REVIEW,
                        'published_at' => null,
                        'published_product_id' => null,
                    ]);
                });

                $batch->update([
                    'status' => UploadBatchStatus::REVIEW_READY,
                    'progress_message' => 'Batch rolled back to review state.',
                ]);
            });

            return true;
        } catch (\Throwable $e) {
            Log::error("Failed to rollback batch {$batchUuid}: " . $e->getMessage());
            return false;
        }
    }

    public function repairPreviews(string $batchUuid): int
    {
        $batch = UploadBatch::find($batchUuid);
        if ($batch === null) {
            return 0;
        }

        $repaired = 0;
        $batch->previews()->where('status', \App\Enums\PreviewStatus::FAILED)->chunkById(100, function ($previews) use (&$repaired) {
            foreach ($previews as $preview) {
                $preview->update([
                    'status' => \App\Enums\PreviewStatus::NEEDS_REVIEW,
                    'validation_result' => null,
                ]);
                $repaired++;
            }
        });

        return $repaired;
    }

    /**
     * Reprocess an existing upload batch.
     *
     * Deletes old previews and their extracted files, resets batch state,
     * and re-dispatches the extraction job.
     */
    public function reprocessBatch(string $batchUuid): bool
    {
        $batch = UploadBatch::find($batchUuid);
        if ($batch === null) {
            Log::warning("Attempted to reprocess non-existent batch: {$batchUuid}");
            return false;
        }

        // Only allow reprocess for completed or failed batches
        if (!in_array($batch->status, [
            UploadBatchStatus::COMPLETED,
            UploadBatchStatus::FAILED,
        ], true)) {
            Log::warning("Batch {$batchUuid} not in a reprocessable state: {$batch->status->name}");
            return false;
        }

        Log::info('BULK REPROCESS START', [
            'batch_uuid' => $batchUuid,
            'status' => $batch->status->name,
            'total_pages' => $batch->total_pages,
        ]);

        try {
            $sourceFile = $batch->metadata['source_file'] ?? null;
            $totalPages = $batch->total_pages;

            DB::transaction(function () use ($batch, $batchUuid) {
                // Collect preview image paths before deletion
                $oldImagePaths = $batch->previews()
                    ->whereNotNull('preview_image_path')
                    ->pluck('preview_image_path')
                    ->toArray();

                $deletedPreviews = $batch->previews()->count();

                // Hard delete all previews for clean reprocess
                $batch->previews()->each(function ($preview) {
                    $preview->forceDelete();
                });

                Log::info('OLD PREVIEWS DELETED', [
                    'batch_uuid' => $batchUuid,
                    'count' => $deletedPreviews,
                ]);

                // Delete old extracted files under products/ directory
                $deletedFiles = 0;
                foreach ($oldImagePaths as $path) {
                    if ($path && str_starts_with($path, 'products/')) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
                        $deletedFiles++;
                    }
                }

                Log::info('OLD FILES DELETED', [
                    'batch_uuid' => $batchUuid,
                    'count' => $deletedFiles,
                ]);

                // Reset batch state using raw query to bypass status regression guard
                DB::table('upload_batches')
                    ->where('id', $batchUuid)
                    ->update([
                        'status' => UploadBatchStatus::QUEUED->value,
                        'processed_pages' => 0,
                        'progress_message' => null,
                        'updated_at' => now(),
                    ]);
            });

            if ($sourceFile) {
                Bus::chain([
                    new ProcessPdfBulkUploadJob(
                        $batchUuid,
                        $sourceFile,
                        1,
                        $totalPages
                    ),
                ])->dispatch();

                Log::info('JOB REDISPATCHED', [
                    'batch_uuid' => $batchUuid,
                    'source_file' => $sourceFile,
                    'total_pages' => $totalPages,
                ]);
            }

            return true;
        } catch (\Throwable $e) {
            Log::error("Failed to reprocess batch {$batchUuid}: " . $e->getMessage(), [
                'exception' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}