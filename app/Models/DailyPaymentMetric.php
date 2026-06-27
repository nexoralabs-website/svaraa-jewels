<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyPaymentMetric extends Model
{
    protected $fillable = [
        'metric_date',
        'capture_attempts',
        'capture_successes',
        'capture_success_rate',
        'webhook_events',
        'webhook_failures',
        'webhook_failure_rate',
        'reconciliation_recoveries',
        'duplicate_webhooks',
        'refund_count',
        'refund_ratio',
        'stock_conflicts',
        'queue_backlog',
        'failed_jobs',
    ];

    protected $casts = [
        'metric_date' => 'date',
    ];
}
