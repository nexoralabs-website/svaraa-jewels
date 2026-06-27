<?php

namespace App\Console\Commands;

use App\Services\PaymentAlertService;
use Illuminate\Console\Command;

class CheckPaymentAlerts extends Command
{
    protected $signature = 'payments:alerts:check';

    protected $description = 'Check payment health thresholds and create alerts.';

    public function handle(PaymentAlertService $paymentAlertService): int
    {
        $alerts = $paymentAlertService->check();

        $this->info('Payment alerts checked. Triggered: ' . count($alerts));

        return self::SUCCESS;
    }
}
