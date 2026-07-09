<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Product;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'image', 'is_primary'];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ProductImage $productImage) {
            Log::error('[PRODUCT_IMAGE DEBUG] CREATING event:', [
                'image' => $productImage->image,
                'product_id' => $productImage->product_id,
                'attributes' => $productImage->attributesToArray(),
                'dirty' => $productImage->getDirty(),
            ]);
        });

        static::created(function (ProductImage $productImage) {
            Log::error('[PRODUCT_IMAGE DEBUG] CREATED event:', [
                'image' => $productImage->image,
                'product_id' => $productImage->product_id,
                'attributes' => $productImage->attributesToArray(),
                'dirty' => $productImage->getDirty(),
            ]);
        });

        static::saving(function (ProductImage $productImage) {
            Log::error('[PRODUCT_IMAGE DEBUG] SAVING event:', [
                'image' => $productImage->image,
                'product_id' => $productImage->product_id,
                'attributes' => $productImage->attributesToArray(),
                'dirty' => $productImage->getDirty(),
            ]);
        });

        static::saved(function (ProductImage $productImage) {
            Log::error('[PRODUCT_IMAGE DEBUG] SAVED event:', [
                'image' => $productImage->image,
                'product_id' => $productImage->product_id,
                'attributes' => $productImage->attributesToArray(),
                'dirty' => $productImage->getDirty(),
            ]);
        });
    }

    /**
     * Get the full URL for the image, with fallback to placeholder.
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            $path = preg_replace('#^storage/#', '', $this->image);
            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->url($path);
            }
        }
        return asset('images/placeholders/product-coming-soon.svg');
    }

    /**
     * Relationship to parent product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
