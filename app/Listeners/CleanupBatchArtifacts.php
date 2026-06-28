<?php

namespace App\Listeners;

use App\Events\UploadBatchCompleted;
use App\Models\BulkUploadPreview;

/**
 * CleanupBatchArtifacts
 *
 * On batch completion, clears large transient JSON blobs from preview rows
 * that are no longer needed once the batch is done:
 *  - processing_metadata is trimmed to just the essential keys
 *
 * Does NOT delete files, does NOT touch published products.
 * Storage file deletion is handled by BulkUploadCleanupCommand.
 */
class CleanupBatchArtifacts
{
    public function handle(UploadBatchCompleted $event): void
    {
        $batch = $event->batch;

        // Trim processing_metadata on PUBLISHED previews — the heavy AI payload
        // is no longer needed once the product is live.
        BulkUploadPreview::where('batch_uuid', $batch->id)
            ->where('status', \App\Enums\PreviewStatus::PUBLISHED->value)
            ->chunkById(100, function ($previews) {
                foreach ($previews as $preview) {
                    $meta = $preview->processing_metadata ?? [];

                    // Preserve audit keys, discard bulky intermediate data
                    $trimmed = array_intersect_key($meta, array_flip([
                        'extraction_source',
                        'page',
                        'file',
                        'metadata_generated_at',
                        'validation_score',
                        'description_regenerated_at',
                    ]));

                    $trimmed['cleaned_at'] = now()->toIso8601String();

                    $preview->forceFill(['processing_metadata' => $trimmed])->save();
                }
            });
    }
}
