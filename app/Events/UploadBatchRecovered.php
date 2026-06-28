<?php

namespace App\Events;

use App\Models\UploadBatch;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired after the retry command successfully re-chains a FAILED batch.
 * Listeners update metrics and reset retry counters.
 */
class UploadBatchRecovered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly UploadBatch $batch,
        public readonly int         $retryCount = 1,
    ) {}
}
