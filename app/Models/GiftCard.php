<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GiftCard extends Model
{
    protected $fillable = [
        'code',
        'initial_balance',
        'current_balance',
        'recipient_email',
        'recipient_name',
        'purchased_by',
        'order_id',
        'expires_at',
        'active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'active' => 'boolean',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(GiftCardTransaction::class);
    }

    public function purchaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'purchased_by');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isValid(): bool
    {
        return $this->active && 
            ($this->expires_at === null || !$this->expires_at->isPast()) &&
            $this->current_balance > 0;
    }
}