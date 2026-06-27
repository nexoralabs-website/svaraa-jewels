<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    public const LOW_STOCK_THRESHOLD = 5;

    /**
     * Get all products with stock below the low-stock threshold.
     */
    public function getLowStockProducts(): Collection
    {
        return Product::with('category')
            ->where('stock', '>', 0)
            ->where('stock', '<=', self::LOW_STOCK_THRESHOLD)
            ->where('status', true)
            ->orderBy('stock')
            ->get();
    }

    /**
     * Get all out-of-stock products.
     */
    public function getOutOfStockProducts(): Collection
    {
        return Product::with('category')
            ->where('stock', 0)
            ->orderBy('name')
            ->get();
    }

    /**
     * Auto-disable products with zero stock.
     * Returns the number of products disabled.
     */
    public function disableOutOfStockProducts(): int
    {
        return Product::where('stock', 0)
            ->where('status', true)
            ->update(['status' => false]);
    }

    /**
     * Re-enable products that have stock replenished.
     */
    public function enableRestockedProducts(): int
    {
        return Product::where('stock', '>', 0)
            ->where('status', false)
            ->update(['status' => true]);
    }

    /**
     * Reduce stock for an order item. Throws if insufficient.
     */
    public function decrementStock(int $productId, int $quantity): void
    {
        $product = Product::lockForUpdate()->findOrFail($productId);

        if ($product->stock < $quantity) {
            throw new \RuntimeException(
                "Insufficient stock for {$product->name}. Only {$product->stock} available."
            );
        }

        $product->decrement('stock', $quantity);

        // Auto-disable if stock hits 0
        if ($product->fresh()->stock === 0) {
            $product->update(['status' => false]);
            Log::info("Product auto-disabled due to zero stock", ['product_id' => $productId]);
        }
    }

    /**
     * Restore stock when an order is refunded/cancelled.
     */
    public function restoreStock(int $productId, int $quantity): void
    {
        $product = Product::findOrFail($productId);
        $product->increment('stock', $quantity);

        // Re-enable if it was disabled
        if (! $product->status) {
            $product->update(['status' => true]);
        }
    }

    /**
     * Send a low-stock email alert to the admin.
     */
    public function sendLowStockAlertEmail(): void
    {
        $lowStock = $this->getLowStockProducts();
        $outOfStock = $this->getOutOfStockProducts();

        if ($lowStock->isEmpty() && $outOfStock->isEmpty()) {
            return;
        }

        $adminEmail = config('mail.from.address');

        try {
            \Illuminate\Support\Facades\Mail::send(
                'emails.inventory-alert',
                ['lowStock' => $lowStock, 'outOfStock' => $outOfStock],
                function ($m) use ($adminEmail, $lowStock, $outOfStock) {
                    $m->to($adminEmail)
                      ->subject(
                          'Inventory Alert: ' .
                          $outOfStock->count() . ' out of stock, ' .
                          $lowStock->count() . ' low stock'
                      );
                }
            );
        } catch (\Throwable $e) {
            Log::error('Failed to send inventory alert email', ['message' => $e->getMessage()]);
        }
    }

    /**
     * Dashboard summary stats.
     */
    public function getSummary(): array
    {
        return [
            'low_stock_count'      => Product::where('stock', '>', 0)->where('stock', '<=', self::LOW_STOCK_THRESHOLD)->count(),
            'out_of_stock_count'   => Product::where('stock', 0)->count(),
            'total_products'       => Product::count(),
            'disabled_count'       => Product::where('status', false)->count(),
        ];
    }
}
