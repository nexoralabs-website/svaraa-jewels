<?php

namespace App\Services;

use App\Enums\UploadBatchStatus;
use App\Models\BulkUploadJobLog;
use App\Models\UploadBatch;
use Illuminate\Support\Facades\DB;
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
}
