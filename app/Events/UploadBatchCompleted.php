<?php

namespace App\Events;

use App\Models\UploadBatch;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when an UploadBatch reaches COMPLETED status.
 * Listeners: RecordUploadMetrics, CleanupBatchArtifacts.
 */
class UploadBatchCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly UploadBatch $batch,
        public readonly array       $metrics = [],
    ) {}
}
