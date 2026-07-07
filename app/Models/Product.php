<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
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
        $paths = [];
        $rawPaths = [];

        if (!empty($this->thumbnail)) {
            $path = preg_replace('#^storage/#', '', $this->thumbnail);
            $paths[] = $path;
            $rawPaths[$path] = $this->thumbnail;
        }

        $first = $this->images()
            ->orderBy('id')
            ->first();

        if ($first && !empty($first->image)) {
            $path = preg_replace('#^storage/#', '', $first->image);
            $paths[] = $path;
            $rawPaths[$path] = $first->image;
        }

        foreach ($paths as $path) {
            if (!empty($path)) {
                $exists = Storage::disk('public')->exists($path);
                
                logger()->info('THUMBNAIL DIAGNOSTICS', [
                    'db_value'   => $rawPaths[$path] ?? null,
                    'normalized' => $path,
                    'exists'     => $exists,
                    'url'        => Storage::disk('public')->url($path),
                    'disk_root'  => Storage::disk('public')->path(''),
                ]);

                if ($exists) {
                    return Storage::disk('public')->url($path);
                }
            }
        }

        Log::warning('THUMBNAIL MISSING', [
            'product_id' => $this->id,
            'thumbnail' => $this->thumbnail,
            'image' => $first?->image,
        ]);

        return asset('images/placeholders/product-coming-soon.svg');
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