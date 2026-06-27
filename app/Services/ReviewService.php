<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    /**
     * Check if a user has purchased a product (has a captured order containing it).
     */
    public function hasUserPurchasedProduct(User $user, Product $product): bool
    {
        return Order::where('user_id', $user->id)
            ->where('payment_status', 'captured')
            ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
            ->exists();
    }

    /**
     * Check if the user has already reviewed this product.
     */
    public function hasUserReviewedProduct(User $user, Product $product): bool
    {
        return Review::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->exists();
    }

    /**
     * Get the order ID for the first purchase of this product by the user.
     * Used to link the review to a verified purchase.
     */
    public function getVerifiedOrderId(User $user, Product $product): ?int
    {
        $order = Order::where('user_id', $user->id)
            ->where('payment_status', 'captured')
            ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
            ->oldest('paid_at')
            ->first();

        return $order?->id;
    }

    /**
     * Submit a new review. Throws on validation failures.
     */
    public function submit(User $user, Product $product, array $data): Review
    {
        if ($this->hasUserReviewedProduct($user, $product)) {
            throw new \RuntimeException('You have already reviewed this product.');
        }

        if (! $this->hasUserPurchasedProduct($user, $product)) {
            throw new \RuntimeException('You can only review products you have purchased.');
        }

        return DB::transaction(function () use ($user, $product, $data) {
            $orderId = $this->getVerifiedOrderId($user, $product);

            return Review::create([
                'user_id'              => $user->id,
                'product_id'           => $product->id,
                'order_id'             => $orderId,
                'rating'               => (int) $data['rating'],
                'title'                => $data['title'] ?? null,
                'review'               => $data['review'] ?? null,
                'status'               => 'pending',   // admin must approve
                'is_verified_purchase' => true,
            ]);
        });
    }

    /**
     * Get approved reviews for a product with user eager-loaded.
     */
    public function getProductReviews(Product $product): \Illuminate\Database\Eloquent\Collection
    {
        return $product->approvedReviews()
            ->with('user:id,name')
            ->latest()
            ->get();
    }

    /**
     * Rating distribution for a product (1–5 stars, count each).
     */
    public function getRatingDistribution(Product $product): array
    {
        $counts = $product->approvedReviews()
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $distribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $distribution[$i] = $counts[$i] ?? 0;
        }
        return $distribution;
    }
}
