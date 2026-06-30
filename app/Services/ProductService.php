<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ProductService
{
    public function getFilteredProducts(array $filters = []): LengthAwarePaginator
    {
        $perPage = min((int) ($filters['per_page'] ?? 12), 12);

        $query = Product::query()
            ->select([
                'id',
                'name',
                'slug',
                'price',
                'status',
                'category_id',
                'created_at'
            ])
            ->with([
                'category:id,name,slug'
            ])
            ->where('status', 'active')
            ->whereHas('category', fn (Builder $q) => $q->where('slug', 'earrings'));

        if (! empty($filters['category'])) {
            $query->whereHas('category', fn (Builder $q) => $q->where('slug', $filters['category']));
        }

        if (! empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $query->where(fn (Builder $q) => $q
                ->where('name', 'like', $term)
                ->orWhere('description', 'like', $term)
                ->orWhereHas('category', fn (Builder $cq) => $cq->where('name', 'like', $term))
            );
        }

        if (isset($filters['min_price']) && $filters['min_price'] !== null) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price']) && $filters['max_price'] !== null) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (! empty($filters['in_stock'])) {
            $query->where('stock', '>', 0);
        }

        $sortBy = $filters['sort'] ?? 'newest';
        match ($sortBy) {
            'price-low'  => $query->orderBy('price', 'asc'),
            'price-high' => $query->orderBy('price', 'desc'),
            'name-asc'   => $query->orderBy('name', 'asc'),
            default      => $query->orderBy('created_at', 'desc'),
        };

        return $query->paginate($perPage)->withQueryString();
    }

    public function getProductBySlug(string $slug): Product
    {
        return Cache::remember("product_{$slug}", 3600, function () use ($slug) {
            return Product::with(['category', 'images'])
                ->withCount(['approvedReviews as approved_reviews_count'])
                ->withAvg(['approvedReviews as approved_reviews_avg_rating'], 'rating')
                ->where('slug', $slug)
                ->where('status', 'active')
                ->firstOrFail();
        });
    }

    public function getActiveCategories(): Collection
    {
        return Cache::remember('active_categories', 3600, function () {
            return Category::where('status', 'active')->where('slug', 'earrings')->orderBy('name')->get();
        });
    }

    public function getFeaturedProducts(int $limit = 4): Collection
    {
        return Cache::remember("featured_products_{$limit}", 1800, function () use ($limit) {
            return Product::with(['category:id,name,slug', 'images' => fn ($q) => $q->where('is_primary', true)])
                ->withCount(['approvedReviews as approved_reviews_count'])
                ->withAvg(['approvedReviews as approved_reviews_avg_rating'], 'rating')
                ->where('status', 'active')
                ->whereHas('category', fn (Builder $q) => $q->where('slug', 'earrings'))
                ->inRandomOrder()
                ->take($limit)
                ->get();
        });
    }

    public function getRelatedProducts(Product $product, int $limit = 4): Collection
    {
        return Cache::remember("related_{$product->id}_{$limit}", 1800, function () use ($product, $limit) {
            return Product::with(['category:id,name,slug', 'images' => fn ($q) => $q->where('is_primary', true)])
                ->where('status', 'active')
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->inRandomOrder()
                ->take($limit)
                ->get();
        });
    }

    public function clearProductCache(string $slug): void
    {
        Cache::forget("product_{$slug}");
    }

    public function clearCategoryCache(): void
    {
        Cache::forget('active_categories');
    }
}
