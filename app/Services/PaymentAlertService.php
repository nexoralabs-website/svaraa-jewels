<?php

namespace App\Services;

use App\Mail\PaymentAlertMail;
use App\Models\PaymentAlert;
use App\Models\PaymentLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PaymentAlertService
{
    public function __construct(
        protected PaymentHealthService $healthService,
        protected PaymentStructuredLogger $logger,
    ) {
    }

    public function check(): array
    {
        $snapshot = $this->healthService->dailySnapshot();
        $thresholds = config('payment-monitoring.thresholds');
        $alerts = [];

        $alerts[] = $this->triggerIf('webhook_failure_spike', $snapshot['webhook_failures'] >= $thresholds['webhook_failures'], [
            'count' => $snapshot['webhook_failures'],
            'threshold' => $thresholds['webhook_failures'],
        ]);

        $reconciliationFailures = PaymentLog::where('action', 'reconcile')->where('status', 'failed')->where('created_at', '>=', now()->subDay())->count();
        $alerts[] = $this->triggerIf('reconciliation_failures', $reconciliationFailures >= $thresholds['reconciliation_failures'], [
            'count' => $reconciliationFailures,
            'threshold' => $thresholds['reconciliation_failures'],
        ]);

        $alerts[] = $this->triggerIf('stock_conflicts', $snapshot['stock_conflicts'] >= $thresholds['stock_conflicts'], [
            'count' => $snapshot['stock_conflicts'],
            'threshold' => $thresholds['stock_conflicts'],
        ]);

        $queueBacklog = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();
        $alerts[] = $this->triggerIf('queue_backlog', $queueBacklog >= $thresholds['queue_backlog'] || $failedJobs >= $thresholds['failed_jobs'], [
            'queue_backlog' => $queueBacklog,
            'failed_jobs' => $failedJobs,
            'queue_threshold' => $thresholds['queue_backlog'],
            'failed_jobs_threshold' => $thresholds['failed_jobs'],
        ]);

        $alerts[] = $this->triggerIf('payment_capture_anomaly', $snapshot['capture_attempts'] > 0 && $snapshot['capture_success_rate'] < $thresholds['capture_success_rate_min'], [
            'success_rate' => $snapshot['capture_success_rate'],
            'threshold' => $thresholds['capture_success_rate_min'],
        ]);

        return array_values(array_filter($alerts));
    }

    protected function triggerIf(string $type, bool $condition, array $context): ?PaymentAlert
    {
        if (!$condition) {
            return null;
        }

        $alert = PaymentAlert::firstOrCreate(
            ['type' => $type, 'status' => 'open'],
            [
                'severity' => 'warning',
                'context' => $context,
                'triggered_at' => Carbon::now(),
            ]
        );

        if ($alert->wasRecentlyCreated) {
            $this->logger->log('warning', $type, context: $context, action: 'alert', status: 'open', response: $context);

            if ($email = config('payment-monitoring.alert_email')) {
                Mail::to($email)->queue(new PaymentAlertMail($alert));
            }
        }

        return $alert;
    }
}
