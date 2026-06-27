<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class OrderReconciliationTest extends TestCase
{
    use RefreshDatabase;

    public function test_reconciliation_command_runs_payment_service(): void
    {
        $service = Mockery::mock(PaymentService::class);
        $service->shouldReceive('reconcilePendingPayments')
            ->once()
            ->andReturn(['checked' => 1, 'updated' => 1, 'failed' => 0]);

        $this->app->instance(PaymentService::class, $service);

        $this->artisan('payments:reconcile')
            ->expectsOutput('Payments reconciled. Checked: 1, Updated: 1, Failed: 0')
            ->assertSuccessful();
    }

    public function test_reconciliation_refreshes_pending_order_after_captured_payment(): void
    {
        Mail::fake();

        [$order, $product] = $this->createPendingOrder();

        $service = new class ([
            [
                'id' => 'pay_reconcile_1',
                'order_id' => 'order_reconcile_1',
                'amount' => 20000,
                'status' => 'captured',
                'amount_refunded' => 0,
            ],
        ]) extends PaymentService {
            public function __construct(private array $payments)
            {
            }

            protected function fetchRazorpayPaymentsForOrder(Order $order): array
            {
                return ['items' => $this->payments];
            }
        };

        $this->assertTrue($service->reconcileOrder($order));
        $this->assertSame('captured', $order->fresh()->payment_status);
        $this->assertSame(3, $product->fresh()->stock);
        Mail::assertQueuedCount(2);
    }

    private function createPendingOrder(): array
    {
        $category = Category::create(['name' => 'Necklaces', 'slug' => 'necklaces']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Pearl Necklace',
            'slug' => 'pearl-necklace-' . uniqid(),
            'price' => 100,
            'stock' => 5,
        ]);

        $order = Order::create([
            'order_number' => 'SVR-REC-' . uniqid(),
            'subtotal' => 200,
            'discount' => 0,
            'shipping' => 0,
            'tax' => 0,
            'total' => 200,
            'payment_method' => 'upi',
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '9999999999',
            'shipping_address' => 'Test Address',
            'razorpay_order_id' => 'order_reconcile_1',
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
}
