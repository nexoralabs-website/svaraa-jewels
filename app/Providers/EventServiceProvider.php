<?php

namespace App\Providers;

use App\Events\UploadBatchCompleted;
use App\Events\UploadBatchFailed;
use App\Events\UploadBatchRecovered;
use App\Listeners\CleanupBatchArtifacts;
use App\Listeners\MergeCartAfterLogin;
use App\Listeners\RecordUploadMetrics;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Login::class => [
            MergeCartAfterLogin::class,
        ],

        // ── Bulk upload lifecycle events ──────────────────────────────────
        UploadBatchCompleted::class => [
            RecordUploadMetrics::class . '@handleCompleted',
            CleanupBatchArtifacts::class,
        ],
        UploadBatchFailed::class => [
            RecordUploadMetrics::class . '@handleFailed',
        ],
        UploadBatchRecovered::class => [
            RecordUploadMetrics::class . '@handleRecovered',
        ],
    ];

    public function boot(): void
    {
        //
    }
}
