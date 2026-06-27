<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CartRecoveryService
{
    public function detectAbandonedCarts(int $minutesSince = 30): array
    {
        $cutoff = now()->subMinutes($minutesSince);

        return Cart::where('updated_at', '<=', $cutoff)
            ->whereHas('user')
            ->with('user')
            ->get()
            ->map(fn ($cart) => [
                'user_id' => $cart->user_id,
                'cart_items' => $cart->items()->with('product')->get(),
                'last_updated' => $cart->updated_at,
                'value' => $cart->items()->sum('product.price'),
            ])
            ->toArray();
    }

    public function generateRecoveryCoupon(User $user): ?string
    {
        $code = 'RECOVER-' . rand(1000, 9999);

        try {
            DB::table('coupons')->insert([
                'code' => $code,
                'type' => 'flat',
                'value' => 100,
                'min_order_value' => 500,
                'usage_limit' => 1,
                'used_count' => 0,
                'expires_at' => now()->addDays(3),
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $code;
        } catch (\Exception $e) {
            Log::error('Failed to generate recovery coupon', ['user_id' => $user->id]);
            return null;
        }
    }

    public function getRecoveryStats(): array
    {
        $recovered = DB::table('coupons')
            ->where('code', 'like', 'RECOVER-%')
            ->where('used_count', '>', 0)
            ->sum('used_count');

        return [
            'abandoned_carts' => $this->detectAbandonedCarts(30),
            'recovered_carts' => $recovered,
            'recovery_rate' => 0,
        ];
    }
}