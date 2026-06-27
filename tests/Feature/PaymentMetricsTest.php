<?php

namespace Tests\Feature;

use App\Mail\PaymentAlertMail;
use App\Models\DailyPaymentMetric;
use App\Models\PaymentAlert;
use App\Models\PaymentLog;
use App\Services\PaymentAlertService;
use App\Services\PaymentHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Concerns\CreatesPaymentFixtures;
use Tests\TestCase;

class PaymentMetricsTest extends TestCase
{
    use CreatesPaymentFixtures;
    use RefreshDatabase;

    public function test_daily_payment_metrics_are_aggregated(): void
    {
        [$order] = $this->createPaymentOrder([
            'payment_status' => 'captured',
            'paid_at' => now(),
        ]);

        PaymentLog::create(['order_id' => $order->id, 'action' => 'capture', 'provider' => 'razorpay', 'status' => 'success']);
        PaymentLog::create(['action' => 'webhook', 'provider' => 'razorpay', 'status' => 'duplicate']);
        PaymentLog::create(['action' => 'refund', 'provider' => 'razorpay', 'status' => 'success']);
        PaymentLog::create(['action' => 'reconcile', 'provider' => 'razorpay', 'status' => 'recovered']);

        $metric = app(PaymentHealthService::class)->aggregate();

        $this->assertInstanceOf(DailyPaymentMetric::class, $metric);
        $this->assertSame(1, $metric->capture_successes);
        $this->assertSame(100.0, (float) $metric->capture_success_rate);
        $this->assertSame(1, $metric->duplicate_webhooks);
        $this->assertSame(1, $metric->reconciliation_recoveries);
        $this->assertSame(1, $metric->refund_count);
    }

    public function test_alerts_are_created_and_queued_when_thresholds_are_exceeded(): void
    {
        Mail::fake();
        config([
            'payment-monitoring.alert_email' => 'ops@example.com',
            'payment-monitoring.thresholds.webhook_failures' => 1,
            'payment-monitoring.thresholds.stock_conflicts' => 1,
            'payment-monitoring.thresholds.reconciliation_failures' => 99,
            'payment-monitoring.thresholds.queue_backlog' => 99,
            'payment-monitoring.thresholds.failed_jobs' => 99,
            'payment-monitoring.thresholds.capture_success_rate_min' => 10,
        ]);

        PaymentLog::create(['action' => 'webhook_invalid', 'provider' => 'razorpay', 'status' => 'failed']);
        PaymentLog::create(['action' => 'stock_conflict', 'provider' => 'razorpay', 'status' => 'failed']);

        $alerts = app(PaymentAlertService::class)->check();

        $this->assertCount(2, $alerts);
        $this->assertTrue(PaymentAlert::where('type', 'webhook_failure_spike')->exists());
        $this->assertTrue(PaymentAlert::where('type', 'stock_conflicts')->exists());
        Mail::assertQueued(PaymentAlertMail::class);
    }
}
