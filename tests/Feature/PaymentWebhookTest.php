<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentEvent;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_webhook_is_processed_once(): void
    {
        Mail::fake();
        config(['services.razorpay.webhook_secret' => 'test_webhook_secret']);

        [$order, $product] = $this->createPendingOrder();
        $payload = $this->capturedPayload($order, 'evt_duplicate_1');
        $body = json_encode($payload);
        $signature = hash_hmac('sha256', $body, 'test_webhook_secret');

        $this->postJson('/webhooks/razorpay', $payload, ['X-Razorpay-Signature' => $signature])
            ->assertOk()
            ->assertJson(['processed' => true]);

        $this->postJson('/webhooks/razorpay', $payload, ['X-Razorpay-Signature' => $signature])
            ->assertOk()
            ->assertJson(['processed' => false]);

        $this->assertSame('captured', $order->fresh()->payment_status);
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertSame(1, PaymentEvent::where('event_id', 'evt_duplicate_1')->count());
        Mail::assertQueuedCount(2);
    }

    public function test_refund_webhook_marks_order_refunded_and_restores_stock(): void
    {
        config(['services.razorpay.webhook_secret' => 'test_webhook_secret']);

        [$order, $product] = $this->createPendingOrder();
        $order->update([
            'payment_status' => 'captured',
            'razorpay_payment_id' => 'pay_refund_1',
        ]);
        $product->update(['stock' => 3]);

        $payload = [
            'id' => 'evt_refund_1',
            'event' => 'refund.processed',
            'payload' => [
                'refund' => [
                    'entity' => [
                        'id' => 'rfnd_1',
                        'payment_id' => 'pay_refund_1',
                    ],
                ],
            ],
        ];
        $body = json_encode($payload);
        $signature = hash_hmac('sha256', $body, 'test_webhook_secret');

        $this->postJson('/webhooks/razorpay', $payload, ['X-Razorpay-Signature' => $signature])
            ->assertOk()
            ->assertJson(['processed' => true]);

        $this->assertSame('refunded', $order->fresh()->payment_status);
        $this->assertSame(5, $product->fresh()->stock);
    }

    private function createPendingOrder(): array
    {
        $category = Category::create(['name' => 'Rings', 'slug' => 'rings']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Gold Ring',
            'slug' => 'gold-ring-' . uniqid(),
            'price' => 100,
            'stock' => 5,
        ]);

        $order = Order::create([
            'order_number' => 'SVR-TEST-' . uniqid(),
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
            'razorpay_order_id' => 'order_test_1',
        ]);

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

    private function capturedPayload(Order $order, string $eventId): array
    {
        return [
            'id' => $eventId,
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_test_1',
                        'order_id' => $order->razorpay_order_id,
                        'amount' => 20000,
                        'status' => 'captured',
                    ],
                ],
            ],
        ];
    }
}
