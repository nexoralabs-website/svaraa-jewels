<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_value',
        'usage_limit',
        'used_count',
        'expires_at',
        'first_order_only',
        'active',
        'applicable_type',
        'applicable_id',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'active' => 'boolean',
        'first_order_only' => 'boolean',
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function isValid(float $orderTotal, ?User $user = null): bool
    {
        if (!$this->active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        if ($this->min_order_value && $orderTotal < $this->min_order_value) {
            return false;
        }

        if ($this->first_order_only && $user && $user->orders()->where('payment_status', 'captured')->exists()) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $orderTotal): float
    {
        if ($this->type === 'percentage') {
            return min($orderTotal * ($this->value / 100), $orderTotal);
        }

        return min($this->value, $orderTotal);
    }
}