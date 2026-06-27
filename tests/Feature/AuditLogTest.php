<?php

namespace Tests\Feature;

use App\Models\AdminActivityLog;
use App\Models\Order;
use App\Models\PaymentLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesPaymentFixtures;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use CreatesPaymentFixtures;
    use RefreshDatabase;

    public function test_payment_logs_are_immutable(): void
    {
        [$order] = $this->createPaymentOrder();

        $log = PaymentLog::create([
            'order_id' => $order->id,
            'action' => 'create',
            'provider' => 'razorpay',
            'status' => 'success',
        ]);

        $log->update(['status' => 'failed']);

        $this->assertSame('success', $log->fresh()->status);
    }

    public function test_admin_activity_is_logged_with_actor_and_timestamp(): void
    {
        $admin = $this->createAdmin();

        $log = AdminActivityLog::create([
            'actor_id' => $admin->id,
            'action' => 'test_action',
            'metadata' => ['test' => true],
            'acted_at' => now(),
        ]);

        $this->assertNotNull($log->actor_id);
        $this->assertNotNull($log->acted_at);
        $this->assertSame($admin->id, $log->actor->id);
    }

    public function test_payment_logs_contain_observability_fields(): void
    {
        [$order] = $this->createPaymentOrder();

        $log = PaymentLog::create([
            'order_id' => $order->id,
            'action' => 'webhook',
            'provider' => 'razorpay',
            'correlation_id' => 'corr-test-123',
            'payment_id' => 'pay-test-123',
            'customer_email' => $order->customer_email,
            'event_type' => 'payment.captured',
            'latency_ms' => 200,
            'environment' => 'production',
            'status' => 'success',
            'request' => ['event' => 'payment.captured'],
            'response' => ['processed' => true],
        ]);

        $this->assertSame('corr-', substr($log->correlation_id, 0, 5));
        $this->assertSame($order->customer_email, $log->customer_email);
        $this->assertSame(200, $log->latency_ms);
        $this->assertSame('production', $log->environment);
    }

    public function test_payment_log_actor_relation_works(): void
    {
        [$order] = $this->createPaymentOrder();
        $admin = $this->createAdmin();

        $log = PaymentLog::create([
            'order_id' => $order->id,
            'actor_id' => $admin->id,
            'action' => 'admin_test',
            'provider' => 'razorpay',
            'status' => 'success',
        ]);

        $this->assertNotNull($log->actor);
        $this->assertSame($admin->email, $log->actor->email);
    }

    public function test_null_actor_payment_logs_work(): void
    {
        [$order] = $this->createPaymentOrder();

        $log = PaymentLog::create([
            'order_id' => $order->id,
            'action' => 'system_event',
            'provider' => 'razorpay',
            'status' => 'success',
        ]);

        $this->assertNull($log->actor);
        $this->assertNull($log->actor_id);
    }

    public function test_replay_detection_via_payment_event(): void
    {
        $event = \App\Models\PaymentEvent::create([
            'event_id' => 'evt_replay_test',
            'provider' => 'razorpay',
            'payload_hash' => 'hash123',
            'processed_at' => now(),
        ]);

        $this->assertNotNull($event->processed_at);
        $this->assertTrue(\App\Models\PaymentEvent::where('event_id', 'evt_replay_test')->exists());
    }
}