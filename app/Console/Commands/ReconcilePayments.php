<?php

namespace App\Console\Commands;

use App\Services\PaymentService;
use Illuminate\Console\Command;

class ReconcilePayments extends Command
{
    protected $signature = 'payments:reconcile';

    protected $description = 'Reconcile pending Razorpay payments with the provider.';

    public function handle(PaymentService $paymentService): int
    {
        $summary = $paymentService->reconcilePendingPayments();

        $this->info(sprintf(
            'Payments reconciled. Checked: %d, Updated: %d, Failed: %d',
            $summary['checked'] ?? 0,
            $summary['updated'] ?? 0,
            $summary['failed'] ?? 0,
        ));

        return self::SUCCESS;
    }
}
