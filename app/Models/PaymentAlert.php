<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentAlert extends Model
{
    protected $fillable = [
        'type',
        'severity',
        'status',
        'context',
        'triggered_at',
        'resolved_at',
    ];

    protected $casts = [
        'context' => 'array',
        'triggered_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];
}
