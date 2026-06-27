<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentLog;
use App\Models\User;
use App\Services\PaymentRecoveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Concerns\CreatesPaymentFixtures;
use Tests\TestCase;

class AdminRetryActionsTest extends TestCase
{
    use CreatesPaymentFixtures;
    use RefreshDatabase;

    public function test_retry_payment_verification_skips_without_signature(): void
    {
        [$order] = $this->createPaymentOrder([
            'payment_status' => 'pending',
            'razorpay_order_id' => 'order_test',
            'razorpay_payment_id' => null,
            'razorpay_signature' => null,
        ]);

        $admin = $this->createAdmin();
        $service = app(PaymentRecoveryService::class);

        $result = $service->retryPaymentVerification($order, $admin);

        $this->assertFalse($result);
        $this->assertTrue(\App\Models\AdminActivityLog::where('action', 'retry_payment_verification_skipped')->exists());
    }

    public function test_retry_email_dispatch_queues_emails(): void
    {
        Mail::fake();

        [$order] = $this->createPaymentOrder([
            'payment_status' => 'captured',
            'paid_at' => now(),
        ]);

        $admin = $this->createAdmin();
        $service = app(PaymentRecoveryService::class);

        $result = $service->retryEmailDispatch($order, $admin);

        $this->assertTrue($result);
        Mail::assertQueued(\App\Mail\OrderPlacedMail::class);
        Mail::assertQueued(\App\Mail\AdminOrderNotificationMail::class);
        $this->assertTrue(\App\Models\AdminActivityLog::where('action', 'retry_email_dispatch')->exists());
    }

    public function test_retry_email_skips_unfinalized_order(): void
    {
        Mail::fake();

        [$order] = $this->createPaymentOrder([
            'payment_status' => 'pending',
        ]);

        $admin = $this->createAdmin();
        $service = app(PaymentRecoveryService::class);

        $result = $service->retryEmailDispatch($order, $admin);

        $this->assertFalse($result);
        Mail::assertNothingQueued();
        $this->assertTrue(\App\Models\AdminActivityLog::where('action', 'retry_email_dispatch_skipped')->exists());
    }

    public function test_retry_webhook_processing_requires_valid_payload(): void
    {
        [$order] = $this->createPaymentOrder();
        $admin = $this->createAdmin();

        $service = app(PaymentRecoveryService::class);

        $logWithoutRequest = PaymentLog::create([
            'order_id' => $order->id,
            'action' => 'other',
            'provider' => 'razorpay',
            'status' => 'failed',
        ]);

        $result = $service->retryWebhookProcessing($logWithoutRequest, $admin);
        $this->assertFalse($result);
        $this->assertTrue(\App\Models\AdminActivityLog::where('action', 'retry_webhook_processing_skipped')->exists());
    }

    public function test_retry_order_finalization_skips_captured_orders(): void
    {
        [$order] = $this->createPaymentOrder([
            'payment_status' => 'captured',
            'paid_at' => now(),
        ]);

        $admin = $this->createAdmin();
        $service = app(PaymentRecoveryService::class);

        $result = $service->retryOrderFinalization($order, $admin);

        $this->assertFalse($result);
        $this->assertTrue(\App\Models\AdminActivityLog::where('action', 'retry_order_finalization_skipped')->exists());
    }

    public function test_retry_actions_log_actor_and_timestamp(): void
    {
        [$order] = $this->createPaymentOrder([
            'payment_status' => 'captured',
            'paid_at' => now(),
        ]);

        $admin = $this->createAdmin();
        $service = app(PaymentRecoveryService::class);

        $service->retryEmailDispatch($order, $admin);

        $log = \App\Models\AdminActivityLog::where('action', 'retry_email_dispatch')->first();
        $this->assertNotNull($log->actor_id);
        $this->assertNotNull($log->acted_at);
    }
}