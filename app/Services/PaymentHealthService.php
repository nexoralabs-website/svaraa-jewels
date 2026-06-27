<?php

namespace App\Services;

use App\Models\DailyPaymentMetric;
use App\Models\Order;
use App\Models\PaymentLog;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentHealthService
{
    public function dailySnapshot(CarbonInterface|string|null $date = null): array
    {
        $day = $date ? Carbon::parse($date) : today();
        $start = $day->copy()->startOfDay();
        $end = $day->copy()->endOfDay();

        $captureAttempts = PaymentLog::where('action', 'capture')->whereBetween('created_at', [$start, $end])->count();
        $captureSuccesses = PaymentLog::where('action', 'capture')->where('status', 'success')->whereBetween('created_at', [$start, $end])->count();
        $webhookEvents = PaymentLog::where('action', 'webhook')->whereBetween('created_at', [$start, $end])->count();
        $webhookFailures = PaymentLog::whereIn('action', ['webhook_invalid', 'webhook'])->where('status', 'failed')->whereBetween('created_at', [$start, $end])->count();
        $reconciliationRecoveries = PaymentLog::where('action', 'reconcile')->where('status', 'recovered')->whereBetween('created_at', [$start, $end])->count();
        $duplicateWebhooks = PaymentLog::where('action', 'webhook')->where('status', 'duplicate')->whereBetween('created_at', [$start, $end])->count();
        $refundCount = PaymentLog::where('action', 'refund')->where('status', 'success')->whereBetween('created_at', [$start, $end])->count();
        $capturedOrders = Order::where('payment_status', 'captured')->whereBetween('paid_at', [$start, $end])->count();

        return [
            'metric_date' => $day->toDateString(),
            'capture_attempts' => $captureAttempts,
            'capture_successes' => $captureSuccesses,
            'capture_success_rate' => $this->percent($captureSuccesses, $captureAttempts),
            'webhook_events' => $webhookEvents,
            'webhook_failures' => $webhookFailures,
            'webhook_failure_rate' => $this->percent($webhookFailures, max($webhookEvents + $webhookFailures, 1)),
            'reconciliation_recoveries' => $reconciliationRecoveries,
            'duplicate_webhooks' => $duplicateWebhooks,
            'refund_count' => $refundCount,
            'refund_ratio' => $this->percent($refundCount, max($capturedOrders + $refundCount, 1)),
            'stock_conflicts' => PaymentLog::where('action', 'stock_conflict')->whereBetween('created_at', [$start, $end])->count(),
            'queue_backlog' => DB::table('jobs')->count(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
        ];
    }

    public function aggregate(CarbonInterface|string|null $date = null): DailyPaymentMetric
    {
        $snapshot = $this->dailySnapshot($date);

        return DailyPaymentMetric::updateOrCreate(
            ['metric_date' => $snapshot['metric_date']],
            $snapshot
        );
    }

    public function dashboardMetrics(array $filters = []): array
    {
        $from = Carbon::parse($filters['from'] ?? today())->startOfDay();
        $to = Carbon::parse($filters['to'] ?? today())->endOfDay();

        $orders = Order::query()->whereBetween('created_at', [$from, $to]);

        if (!empty($filters['payment_status'])) {
            $orders->where('payment_status', $filters['payment_status']);
        }

        if (!empty($filters['order_status'])) {
            $orders->where('order_status', $filters['order_status']);
        }

        return [
            'orders_today' => Order::whereBetween('created_at', [today()->startOfDay(), today()->endOfDay()])->count(),
            'revenue_today' => (float) Order::where('payment_status', 'captured')->whereBetween('paid_at', [today()->startOfDay(), today()->endOfDay()])->sum('total'),
            'pending_payments' => Order::where('payment_status', 'pending')->count(),
            'failed_payments' => Order::where('payment_status', 'failed')->count(),
            'refund_count' => Order::where('payment_status', 'refunded')->whereBetween('updated_at', [$from, $to])->count(),
            'stock_conflicts' => PaymentLog::where('action', 'stock_conflict')->whereBetween('created_at', [$from, $to])->count(),
            'recent_webhook_events' => PaymentLog::whereIn('action', ['webhook', 'webhook_invalid'])->latest()->limit(10)->get(),
            'filtered_orders' => $orders->latest()->limit(100)->get(),
        ];
    }

    protected function percent(int $part, int $total): float
    {
        return $total > 0 ? round(($part / $total) * 100, 2) : 0.0;
    }
}
