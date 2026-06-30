<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SearchService
{
    /**
     * Full product search via Scout (Meilisearch if configured, DB otherwise).
     */
    public function searchProducts(string $query, array $filters = [], int $perPage = 12): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $builder = Product::search($query)->where('status', true)->where('category', 'Earrings');

        if (! empty($filters['category'])) {
            $builder->where('category', $filters['category']);
        }
        if (! empty($filters['min_price'])) {
            $builder->where('price', '>=', (float) $filters['min_price']);
        }
        if (! empty($filters['max_price'])) {
            $builder->where('price', '<=', (float) $filters['max_price']);
        }
        if (! empty($filters['in_stock'])) {
            $builder->where('stock', '>', 0);
        }

        return $builder
            ->query(fn ($q) => $q->with(['category', 'images']))
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Instant search suggestions for autocomplete (≤8 results, cached 60s).
     * Returns plain price strings with correct rupee symbol.
     */
    public function suggest(string $query): Collection
    {
        if (strlen(trim($query)) < 2) {
            return collect();
        }

        $cacheKey = 'search_suggest_' . md5(strtolower($query));

        return Cache::remember($cacheKey, 60, function () use ($query) {
            try {
                $products = Product::search($query)
                    ->where('status', true)
                    ->where('category', 'Earrings')
                    ->query(fn ($q) => $q->select('id', 'name', 'slug', 'price', 'thumbnail')
                        ->with('category:id,name'))
                    ->take(8)
                    ->get();
            } catch (\Throwable $e) {
                // Meilisearch unavailable — DB fallback
                $term = '%' . $query . '%';
                $products = Product::select('id', 'name', 'slug', 'price', 'thumbnail')
                    ->with('category:id,name')
                    ->where('status', true)
                    ->whereHas('category', fn ($cq) => $cq->where('slug', 'earrings'))
                    ->where(fn ($q) => $q->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term))
                    ->take(8)
                    ->get();
            }

            return $products->map(fn ($p) => [
                'id'       => $p->id,
                'name'     => $p->name,
                'slug'     => $p->slug,
                'price'    => '\u20b9' . number_format($p->price, 2),
                'category' => $p->category?->name ?? '',
                'image'    => $p->thumbnail
                    ? Storage::url($p->thumbnail)
                    : asset('images/placeholder.jpg'),
                'url'      => route('products.show', $p->slug),
            ]);
        });
    }

    /**
     * Fallback DB search (when Meilisearch is unavailable).
     */
    public function dbSearch(string $query, array $filters = [], int $perPage = 12): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $term = '%' . $query . '%';

        return Product::with(['category', 'images'])
            ->where('status', true)
            ->whereHas('category', fn ($cq) => $cq->where('slug', 'earrings'))
            ->where(fn ($q) => $q
                ->where('name', 'like', $term)
                ->orWhere('description', 'like', $term)
                ->orWhereHas('category', fn ($cq) => $cq->where('name', 'like', $term))
            )
            ->when(! empty($filters['category']),
                fn ($q) => $q->whereHas('category', fn ($cq) => $cq->where('slug', $filters['category']))
            )
            ->when(! empty($filters['in_stock']), fn ($q) => $q->where('stock', '>', 0))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }
}
