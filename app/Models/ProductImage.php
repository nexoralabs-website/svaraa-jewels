<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'image', 'is_primary'];

    /**
     * Get the full URL for the image, with fallback to placeholder.
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return asset('storage/' . $this->image);
        }
        return asset('images/placeholder.svg');
    }

    /**
     * Relationship to parent product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
