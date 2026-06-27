<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentEvent extends Model
{
    protected $fillable = [
        'event_id',
        'provider',
        'payload_hash',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];
}
