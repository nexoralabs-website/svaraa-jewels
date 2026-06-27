<?php

namespace Tests\Feature;

use App\Models\PaymentLog;
use App\Services\PaymentHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesPaymentFixtures;
use Tests\TestCase;

class DashboardFeatureTest extends TestCase
{
    use CreatesPaymentFixtures;
    use RefreshDatabase;

    public function test_operations_metrics_and_csv_export_are_available(): void
    {
        $admin = $this->createAdmin();
        [$capturedOrder] = $this->createPaymentOrder([
            'payment_status' => 'captured',
            'paid_at' => now(),
        ]);
        $this->createPaymentOrder(['payment_status' => 'failed']);

        PaymentLog::create([
            'order_id' => $capturedOrder->id,
            'action' => 'stock_conflict',
            'provider' => 'razorpay',
            'status' => 'failed',
        ]);

        $metrics = app(PaymentHealthService::class)->dashboardMetrics();

        $this->assertSame(2, $metrics['orders_today']);
        $this->assertSame(200.0, $metrics['revenue_today']);
        $this->assertSame(1, $metrics['failed_payments']);
        $this->assertSame(1, $metrics['stock_conflicts']);

        $this->actingAs($admin)
            ->get(route('admin.operations.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_dashboard_metrics_filters_work(): void
    {
        [$capturedOrder] = $this->createPaymentOrder([
            'payment_status' => 'captured',
            'order_status' => 'paid',
            'paid_at' => now(),
        ]);
        $this->createPaymentOrder(['payment_status' => 'failed', 'order_status' => 'failed']);
        $this->createPaymentOrder(['payment_status' => 'pending', 'order_status' => 'pending']);

        $metrics = app(PaymentHealthService::class)->dashboardMetrics([
            'payment_status' => 'captured',
        ]);

        $this->assertCount(1, $metrics['filtered_orders']);
        $this->assertSame($capturedOrder->id, $metrics['filtered_orders']->first()->id);

        $metrics = app(PaymentHealthService::class)->dashboardMetrics([
            'payment_status' => 'failed',
        ]);

        $this->assertCount(1, $metrics['filtered_orders']);
    }

    public function test_dashboard_metrics_returns_recent_webhook_events(): void
    {
        [$order] = $this->createPaymentOrder();

        PaymentLog::create([
            'order_id' => $order->id,
            'action' => 'webhook',
            'provider' => 'razorpay',
            'status' => 'success',
        ]);

        $metrics = app(PaymentHealthService::class)->dashboardMetrics();

        $this->assertNotNull($metrics['recent_webhook_events']);
        $this->assertCount(1, $metrics['recent_webhook_events']);
    }
}
