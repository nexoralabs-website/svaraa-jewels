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

    /**
     * Get the full URL for the image, with fallback to placeholder.
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            $exists = Storage::disk('public')->exists($this->image);
            
            logger()->info('PRODUCT_IMAGE DIAGNOSTICS', [
                'db_value'   => $this->image,
                'normalized' => $this->image,
                'exists'     => $exists,
                'url'        => Storage::disk('public')->url($this->image),
                'disk_root'  => Storage::disk('public')->path(''),
            ]);

            if ($exists) {
                return Storage::disk('public')->url($this->image);
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
