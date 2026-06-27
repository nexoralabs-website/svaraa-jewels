<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class CouponService
{
    public function validate(string $code, float $orderTotal, ?User $user = null): ?Coupon
    {
        $cacheKey = "coupon_{$code}";

        $coupon = Cache::remember($cacheKey, 300, function () use ($code) {
            return Coupon::where('code', $code)->first();
        });

        if (! $coupon || ! $coupon->isValid($orderTotal, $user)) {
            return null;
        }

        return $coupon;
    }

    /**
     * Return a human-readable reason why a coupon is not valid.
     * Used by the controller to show specific error messages.
     */
    public function getInvalidReason(string $code, float $orderTotal, ?User $user = null): string
    {
        $coupon = Cache::remember("coupon_{$code}", 300, fn () => Coupon::where('code', $code)->first());

        if (! $coupon) {
            return 'Coupon code not found.';
        }

        if (! $coupon->active) {
            return 'This coupon is no longer active.';
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return 'This coupon has expired.';
        }

        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return 'This coupon has reached its usage limit.';
        }

        if ($coupon->min_order_value && $orderTotal < $coupon->min_order_value) {
            return 'Minimum order value of ₹' . number_format($coupon->min_order_value, 2) . ' required.';
        }

        if ($coupon->first_order_only && $user && $user->orders()->where('payment_status', 'captured')->exists()) {
            return 'This coupon is valid for first-time orders only.';
        }

        return 'Invalid or expired coupon code.';
    }

    public function calculateDiscount(Coupon $coupon, float $orderTotal): float
    {
        return $coupon->calculateDiscount($orderTotal);
    }

    public function generateCode(string $prefix = 'SVRA'): string
    {
        do {
            $code = strtoupper($prefix . '-' . rand(100000, 999999));
        } while (Coupon::where('code', $code)->exists());

        return $code;
    }

    public function invalidateCache(string $code): void
    {
        Cache::forget("coupon_{$code}");
    }

    public function incrementUsage(Coupon $coupon): void
    {
        $coupon->increment('used_count');
        $this->invalidateCache($coupon->code);
    }
}
