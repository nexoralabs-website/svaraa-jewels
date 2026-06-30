<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Support\Collection;

class RecommendationService
{
    public function relatedProducts(Product $product, int $limit = 4): Collection
    {
        return cache()->remember(
            "related_{$product->id}_{$limit}",
            1800,
            fn () => Product::where('status', true)
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->inRandomOrder()
                ->take($limit)
                ->get()
        );
    }

    public function frequentlyBoughtTogether(Product $product, int $limit = 4): Collection
    {
        $orderIds = Order::whereHas('items', fn ($q) => $q->where('product_id', $product->id))->pluck('id');

        return cache()->remember(
            "fbt_{$product->id}_{$limit}",
            3600,
            fn () => Product::where('status', true)
                ->whereHas('orderItems', fn ($q) => $q->whereIn('order_id', $orderIds))
                ->where('id', '!=', $product->id)
                ->inRandomOrder()
                ->take($limit)
                ->get()
        );
    }

    public function personalized(User $user, int $limit = 8): Collection
    {
        $purchasedCategoryIds = $user->orders()
            ->with('items.product')
            ->get()
            ->pluck('items')
            ->flatten()
            ->pluck('product.category_id')
            ->unique()
            ->filter();

        return cache()->remember(
            "personalized_{$user->id}_{$limit}",
            900,
            fn () => Product::where('status', true)
                ->whereIn('category_id', $purchasedCategoryIds)
                ->inRandomOrder()
                ->take($limit)
                ->get()
        );
    }

    public function recentlyViewed(User $user, int $limit = 6): Collection
    {
        if (!$user->id) {
            return collect();
        }

        $viewedIds = cache()->get("recently_viewed_{$user->id}", []);

        return Product::whereIn('id', $viewedIds)
            ->where('status', true)
            ->take($limit)
            ->get();
    }

    public function trackView(Product $product, ?User $user = null): void
    {
        $key = "recently_viewed_" . ($user?->id ?? session_id());
        $viewed = cache()->get($key, []);

        if (!in_array($product->id, $viewed)) {
            array_unshift($viewed, $product->id);
            $viewed = array_unique(array_slice($viewed, 0, 20));
            cache()->put($key, $viewed, 86400);
        }
    }
}