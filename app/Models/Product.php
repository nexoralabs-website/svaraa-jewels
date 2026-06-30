<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use Searchable;

    protected $fillable = [
        'category_id', 'name', 'slug', 'price', 'description',
        'thumbnail', 'status', 'stock', 'meta_title', 'meta_description', 'og_image',
        'bulk_upload_preview_id', 'published_by', 'published_batch_uuid',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    // Eager-load approved review count + average when using withAvgAndCount()
    protected $withCount = [];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Product $product) {
            if (empty($product->slug) || $product->isDirty('name')) {
                $base = Str::slug($product->name);
                $slug = $base;
                $i    = 1;
                while (static::where('slug', $slug)->where('id', '!=', $product->id ?? 0)->exists()) {
                    $slug = "{$base}-{$i}";
                    $i++;
                }
                $product->slug = $slug;
            }
        });
    }

    // ── Relationships ────────────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('status', 'approved');
    }

    // ── Accessors ────────────────────────────────────────────────────────

    public function getThumbnailUrlAttribute(): string
    {
        // Normalize stored path
        $path = $this->thumbnail;
        // Remove possible storage prefixes and leading slashes
        $path = ltrim($path, '/');
        $path = preg_replace('#^storage/#', '', $path);
        // If thumbnail path exists in public disk, use it
        if ($path && Storage::disk('public')->exists($path)) {
            return asset('storage/' . $path);
        }

        // Fallback: first related image
        $first = $this->images()->orderBy('id')->first();
        if ($first && $first->image && Storage::disk('public')->exists($first->image)) {
            return asset('storage/' . $first->image);
        }

        // Final placeholder image
        return asset('images/placeholder.svg');
    }

    /**
     * Average rating — uses eager-loaded aggregate when available (no extra query).
     * Falls back to real-time query for single product detail page.
     */
    public function getAverageRatingAttribute(): float
    {
        // withAvg('approvedReviews', 'rating') sets approved_reviews_avg_rating
        if (array_key_exists('approved_reviews_avg_rating', $this->attributes)) {
            return round((float) $this->attributes['approved_reviews_avg_rating'], 1);
        }
        return round($this->approvedReviews()->avg('rating') ?? 0, 1);
    }

    /**
     * Review count — uses eager-loaded aggregate when available.
     */
    public function getReviewCountAttribute(): int
    {
        if (array_key_exists('approved_reviews_count', $this->attributes)) {
            return (int) $this->attributes['approved_reviews_count'];
        }
        return $this->approvedReviews()->count();
    }

    public function getSeoTitleAttribute(): string
    {
        return $this->meta_title ?: $this->name . ' | Svaraa Jewels';
    }

    public function getSeoDescriptionAttribute(): string
    {
        return $this->meta_description
            ?: (strlen($this->description ?? '') > 160
                ? substr($this->description, 0, 157) . '...'
                : ($this->description ?? ''));
    }

    // ── Scout / Search ────────────────────────────────────────────────────

    public function searchableAs(): string
    {
        return 'products';
    }

    public function toSearchableArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'category'    => $this->category?->name,
            'price'       => (float) $this->price,
            'stock'       => (int) $this->stock,
            'status'      => $this->status,
            'slug'        => $this->slug,
            'thumbnail'   => $this->thumbnail,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return (bool) $this->status;
    }
}
