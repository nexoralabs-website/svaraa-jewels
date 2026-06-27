<?php

namespace Tests\Feature\Concerns;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;

trait CreatesPaymentFixtures
{
    protected function createPaymentOrder(array $orderOverrides = [], array $productOverrides = []): array
    {
        $category = Category::create([
            'name' => 'Test Category ' . uniqid(),
            'slug' => 'test-category-' . uniqid(),
        ]);

        $product = Product::create(array_merge([
            'category_id' => $category->id,
            'name' => 'Test Jewel',
            'slug' => 'test-jewel-' . uniqid(),
            'price' => 100,
            'stock' => 5,
        ], $productOverrides));

        $order = Order::create(array_merge([
            'user_id' => $orderOverrides['user_id'] ?? null,
            'order_number' => 'SVR-OPS-' . uniqid(),
            'subtotal' => 200,
            'discount' => 0,
            'shipping' => 0,
            'tax' => 0,
            'total' => 200,
            'payment_method' => 'card',
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '9999999999',
            'shipping_address' => 'Test Address',
            'razorpay_order_id' => 'order_' . uniqid(),
        ], $orderOverrides));

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'price' => 100,
            'subtotal' => 200,
        ]);

        return [$order, $product];
    }

    protected function createAdmin(): User
    {
        return User::factory()->create();
    }
}
