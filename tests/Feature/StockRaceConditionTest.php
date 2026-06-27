<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class StockRaceConditionTest extends TestCase
{
    use RefreshDatabase;

    public function test_capture_aborts_when_stock_is_no_longer_available(): void
    {
        Mail::fake();

        $category = Category::create(['name' => 'Bracelets', 'slug' => 'bracelets']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Silver Bracelet',
            'slug' => 'silver-bracelet-' . uniqid(),
            'price' => 100,
            'stock' => 1,
        ]);

        $order = Order::create([
            'order_number' => 'SVR-STOCK-' . uniqid(),
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
            'razorpay_order_id' => 'order_stock_1',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'price' => 100,
            'subtotal' => 200,
        ]);

        try {
            app(PaymentService::class)->processWebhook([
                'id' => 'evt_stock_conflict_1',
                'event' => 'payment.captured',
                'payload' => [
                    'payment' => [
                        'entity' => [
                            'id' => 'pay_stock_1',
                            'order_id' => 'order_stock_1',
                            'amount' => 20000,
                            'status' => 'captured',
                        ],
                    ],
                ],
            ]);

            $this->fail('Expected stock conflict exception was not thrown.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Insufficient stock', $exception->getMessage());
        }

        $this->assertSame(1, $product->fresh()->stock);
        $this->assertSame('pending', $order->fresh()->payment_status);
        Mail::assertNothingQueued();
    }
}
