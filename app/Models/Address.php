<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = ['user_id', 'full_name', 'phone', 'address_line', 'city', 'state', 'pincode', 'country', 'is_default'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
