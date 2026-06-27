<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'payment_method',
        'payment_gateway',
        'transaction_id',
        'gateway_order_id',
        'amount',
        'currency',
        'status',
        'paid_at',
        'payment_meta',
        'refund_status',
        'refund_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'payment_meta' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PaymentLog::class, 'payment_id');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'captured');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'captured';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function markAsCompleted(string $transactionId, ?string $paidAt = null): void
    {
        $this->update([
            'status' => 'captured',
            'transaction_id' => $transactionId,
            'paid_at' => $paidAt ?? now(),
        ]);
    }

    public function markAsFailed(string $reason = null): void
    {
        $meta = $this->payment_meta ?? [];
        if ($reason) {
            $meta['failure_reason'] = $reason;
        }
        $this->update([
            'status' => 'failed',
            'payment_meta' => $meta,
        ]);
    }
}
