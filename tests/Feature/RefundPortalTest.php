<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Refund;
use App\Models\User;
use App\Services\RefundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefundPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_request_refund(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'SVR-REF-1',
            'subtotal' => 200,
            'discount' => 0,
            'shipping' => 0,
            'tax' => 0,
            'total' => 200,
            'payment_status' => 'captured',
            'order_status' => 'paid',
            'customer_name' => 'Test Customer',
            'customer_email' => 'test@example.com',
            'customer_phone' => '9999999999',
            'shipping_address' => 'Test Address',
        ]);

        $service = new RefundService();
        $refund = $service->createRefundRequest($order, $user, 'Product damaged');

        $this->assertNotNull($refund);
        $this->assertSame('pending', $refund->status);
        $this->assertEquals(200.00, (float) $refund->amount);
    }

    public function test_admin_can_process_refund(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'SVR-REF-2',
            'subtotal' => 200,
            'discount' => 0,
            'shipping' => 0,
            'tax' => 0,
            'total' => 200,
            'payment_status' => 'captured',
            'order_status' => 'paid',
            'customer_name' => 'Test Customer',
            'customer_email' => 'test@example.com',
            'customer_phone' => '9999999999',
            'shipping_address' => 'Test Address',
        ]);

        $service = new RefundService();
        $refund = $service->createRefundRequest($order, $user, 'Reason');

        $service->processRefund($refund, 'rfnd_test_123');

        $this->assertSame('processed', $refund->fresh()->status);
        $this->assertSame('rfnd_test_123', $refund->razorpay_refund_id);
        $this->assertSame('refunded', $order->fresh()->payment_status);
    }

    public function test_admin_can_reject_refund(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'SVR-REF-3',
            'subtotal' => 200,
            'discount' => 0,
            'shipping' => 0,
            'tax' => 0,
            'total' => 200,
            'payment_status' => 'captured',
            'order_status' => 'paid',
            'customer_name' => 'Test Customer',
            'customer_email' => 'test@example.com',
            'customer_phone' => '9999999999',
            'shipping_address' => 'Test Address',
        ]);

        $service = new RefundService();
        $refund = $service->createRefundRequest($order, $user, 'Reason');

        $service->rejectRefund($refund, 'Invalid reason');

        $this->assertSame('rejected', $refund->fresh()->status);
    }
}