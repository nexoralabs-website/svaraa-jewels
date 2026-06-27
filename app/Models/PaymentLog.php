<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentLog extends Model
{
    protected $fillable = [
        'order_id',
        'actor_id',
        'action',
        'provider',
        'correlation_id',
        'payment_id',
        'customer_email',
        'event_type',
        'latency_ms',
        'environment',
        'request',
        'response',
        'status',
    ];

    protected $casts = [
        'request' => 'array',
        'response' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    protected static function booted(): void
    {
        static::updating(function (): false {
            return false;
        });

        static::deleting(function (): false {
            return false;
        });
    }
}
