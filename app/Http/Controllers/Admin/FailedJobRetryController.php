<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminActivityLogger;
use App\Services\PaymentRecoveryService;
use Illuminate\Support\Facades\Auth;

class FailedJobRetryController extends Controller
{
    public function __invoke(string $uuid, PaymentRecoveryService $recoveryService, AdminActivityLogger $activityLogger)
    {
        $exitCode = $recoveryService->retryFailedJob($uuid, Auth::user());

        if ($exitCode === 0) {
            $activityLogger->log('failed_job_retry_success', Auth::user(), metadata: ['uuid' => $uuid]);
            return back()->with('status', 'Job requeued successfully');
        }

        $activityLogger->log('failed_job_retry_failed', Auth::user(), metadata: ['uuid' => $uuid, 'exit_code' => $exitCode]);
        return back()->with('error', 'Failed to requeue job');
    }
}