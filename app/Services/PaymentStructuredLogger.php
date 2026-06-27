<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentStructuredLogger
{
    public function log(
        string $level,
        string $eventType,
        ?Order $order = null,
        array $context = [],
        ?string $action = null,
        ?string $status = null,
        ?array $request = null,
        ?array $response = null,
        ?int $latencyMs = null,
    ): string {
        $correlationId = $context['correlation_id'] ?? request()?->headers->get('X-Correlation-ID') ?? (string) Str::uuid();
        $paymentId = $context['payment_id'] ?? $order?->razorpay_payment_id;
        $customerEmail = $context['customer_email'] ?? $order?->customer_email;

        $payload = array_merge($context, [
            'correlation_id' => $correlationId,
            'order_id' => $order?->id,
            'payment_id' => $paymentId,
            'customer_email' => $customerEmail,
            'event_type' => $eventType,
            'latency_ms' => $latencyMs,
            'environment' => app()->environment(),
        ]);

        Log::log($level, $eventType, $payload);

        if ($action || $request || $response) {
            PaymentLog::create([
                'order_id' => $order?->id,
                'actor_id' => Auth::id(),
                'action' => $action ?? $eventType,
                'provider' => 'razorpay',
                'correlation_id' => $correlationId,
                'payment_id' => $paymentId,
                'customer_email' => $customerEmail,
                'event_type' => $eventType,
                'latency_ms' => $latencyMs,
                'environment' => app()->environment(),
                'request' => $request,
                'response' => $response,
                'status' => $status ?? 'info',
            ]);
        }

        return $correlationId;
    }
}
