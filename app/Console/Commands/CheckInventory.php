<?php

namespace App\Console\Commands;

use App\Services\InventoryService;
use Illuminate\Console\Command;

class CheckInventory extends Command
{
    protected $signature   = 'inventory:check {--disable-oos : Auto-disable out-of-stock products} {--email : Send low-stock alert email}';
    protected $description = 'Check inventory levels, optionally disable OOS products and send alerts';

    public function handle(InventoryService $service): int
    {
        $summary = $service->getSummary();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Products',    $summary['total_products']],
                ['Low Stock (≤5)',    $summary['low_stock_count']],
                ['Out of Stock',      $summary['out_of_stock_count']],
                ['Disabled Products', $summary['disabled_count']],
            ]
        );

        if ($this->option('disable-oos')) {
            $disabled = $service->disableOutOfStockProducts();
            $this->info("Auto-disabled {$disabled} out-of-stock products.");
        }

        if ($this->option('email')) {
            $service->sendLowStockAlertEmail();
            $this->info('Low-stock alert email queued.');
        }

        if ($summary['out_of_stock_count'] > 0 || $summary['low_stock_count'] > 0) {
            $this->warn("⚠️  {$summary['out_of_stock_count']} out of stock, {$summary['low_stock_count']} low stock.");
        } else {
            $this->info('✅  All products are well-stocked.');
        }

        return self::SUCCESS;
    }
}
