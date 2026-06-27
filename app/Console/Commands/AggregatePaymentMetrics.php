<?php

namespace App\Console\Commands;

use App\Services\PaymentHealthService;
use Illuminate\Console\Command;

class AggregatePaymentMetrics extends Command
{
    protected $signature = 'payments:metrics:aggregate {--date=}';

    protected $description = 'Aggregate daily payment health metrics.';

    public function handle(PaymentHealthService $paymentHealthService): int
    {
        $metric = $paymentHealthService->aggregate($this->option('date'));

        $this->info('Payment metrics aggregated for ' . $metric->metric_date->toDateString());

        return self::SUCCESS;
    }
}
