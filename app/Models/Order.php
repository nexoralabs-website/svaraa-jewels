<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'user_id', 
        'order_number',
        'invoice_number',
        'invoice_generated_at',
        'subtotal', 
        'discount', 
        'shipping', 
        'tax', 
        'total', 
        'payment_method', 
        'payment_status', 
        'order_status', 
        'customer_name', 
        'customer_email', 
        'customer_phone', 
        'shipping_address', 
        'notes', 
        'razorpay_order_id', 
        'razorpay_payment_id', 
        'razorpay_signature', 
        'paid_at', 
        'payment_meta',
        'coupon_id',
        'coupon_code',
        'coupon_discount',
        'gift_card_id',
        'gift_card_amount',
        'payment_id',
    ];

    protected $casts = [
        'order_status'         => OrderStatus::class,
        'payment_meta'         => 'array',
        'paid_at'              => 'datetime',
        'invoice_generated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function paymentLogs(): HasMany
    {
        return $this->hasMany(PaymentLog::class);
    }

    public function refund(): HasOne
    {
        return $this->hasOne(Refund::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function giftCard(): BelongsTo
    {
        return $this->belongsTo(GiftCard::class);
    }
}
