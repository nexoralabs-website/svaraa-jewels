<?php

namespace App\Events;

use App\Models\UploadBatch;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when an UploadBatch transitions to FAILED status.
 * Allows listeners to log, alert, and schedule recovery.
 */
class UploadBatchFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly UploadBatch $batch,
        public readonly string      $reason  = '',
        public readonly int         $attempt = 1,
    ) {}
}
