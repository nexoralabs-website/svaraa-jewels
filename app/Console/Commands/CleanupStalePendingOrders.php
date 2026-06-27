<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\PaymentStructuredLogger;
use Illuminate\Console\Command;

class CleanupStalePendingOrders extends Command
{
    protected $signature = 'orders:cleanup-stale-pending {--hours=24}';

    protected $description = 'Mark stale pending online orders as failed so they can be retried cleanly.';

    public function handle(PaymentStructuredLogger $logger): int
    {
        $cutoff = now()->subHours((int) $this->option('hours'));
        $count = 0;

        Order::where('payment_status', 'pending')
            ->where('payment_method', '!=', 'cod')
            ->where('created_at', '<=', $cutoff)
            ->chunkById(100, function ($orders) use (&$count, $logger) {
                foreach ($orders as $order) {
                    $order->update(['payment_status' => 'failed']);
                    $logger->log('info', 'stale_pending_order_cleanup', $order, action: 'cleanup', status: 'failed');
                    $count++;
                }
            });

        $this->info("Stale pending orders cleaned: {$count}");

        return self::SUCCESS;
    }
}
