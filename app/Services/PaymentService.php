<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Mail\AdminOrderNotificationMail;
use App\Mail\OrderPlacedMail;
use App\Mail\PaymentFailedMail;
use App\Models\Cart;
use App\Models\Order;
use App\Models\PaymentEvent;
use App\Models\PaymentLog;
use App\Models\Product;
use Illuminate\Support\Facades\Mail;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PaymentService
{
    protected Api $razorpay;

    public function __construct()
    {
        $key = config('services.razorpay.key');
        $secret = config('services.razorpay.secret');
        $this->razorpay = new Api($key, $secret);
    }

    public function createRazorpayOrder(Order $order): array
    {
        if ($order->payment_method === 'cod') {
            throw new \RuntimeException('Cash on delivery orders do not need online payment.');
        }

        if (!in_array($order->payment_status, ['pending', 'failed'])) {
            throw new \RuntimeException('This order is not awaiting payment. Current status: ' . $order->payment_status);
        }

        $amount = $this->amountInPaise($order);

        $requestPayload = [
            'receipt' => $order->order_number,
            'amount' => $amount,
            'currency' => 'INR',
            'payment_capture' => 1,
            'notes' => [
                'internal_order_id' => $order->id,
                'order_number' => $order->order_number,
                'customer_email' => $order->customer_email,
                'customer_name' => $order->customer_name,
            ],
        ];

        try {
            $razorpayOrder = $this->razorpay->order->create($requestPayload);
        } catch (\Throwable $e) {
            $this->logPaymentForOrder($order, 'create_failed', $requestPayload, ['message' => $e->getMessage()], 'failed');
            \Log::error('razorpay_order_create_failed', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Unable to create Razorpay order. Please try again.', 0, $e);
        }

        $order->update([
            'razorpay_order_id' => $razorpayOrder->id,
            'payment_status' => 'pending',
            'order_status' => \App\Enums\OrderStatus::PENDING,
            'payment_meta' => array_merge($order->payment_meta ?? [], [
                'razorpay_order' => $razorpayOrder->toArray(),
            ]),
        ]);

        \App\Models\Payment::updateOrCreate(
            [
                'order_id' => $order->id,
                'payment_gateway' => 'razorpay',
                'gateway_order_id' => $razorpayOrder->id,
            ],
            [
                'payment_method' => $order->payment_method,
                'amount' => $order->total,
                'currency' => 'INR',
                'status' => 'pending',
                'payment_meta' => ['gateway_order' => $razorpayOrder->toArray()],
            ]
        );

        $this->logPaymentForOrder($order, 'create', $requestPayload, $razorpayOrder->toArray(), 'success');

        return [
            'key' => config('services.razorpay.key'),
            'amount' => (int) $razorpayOrder->amount,
            'currency' => $razorpayOrder->currency,
            'order_id' => $razorpayOrder->id,
            'name' => config('app.name'),
            'description' => 'Order #' . $order->order_number,
            'prefill' => [
                'name' => $order->customer_name,
                'email' => $order->customer_email,
                'contact' => $order->customer_phone,
            ],
            'theme' => ['color' => '#6E0F12'],
            'handler' => 'razorpay_response',
            'modal' => [
                'confirm_close' => true,
                'ondismiss' => 'razorpay_dismissed',
            ],
        ];
    }

    public function amountInPaise(Order $order): int
    {
        return (int) round((float) $order->total * 100);
    }

    public function verifySignature(array $attributes): bool
    {
        $order = Order::where('razorpay_order_id', $attributes['razorpay_order_id'] ?? null)->first();

        try {
            $this->razorpay->utility->verifyPaymentSignature([
                'razorpay_order_id' => $attributes['razorpay_order_id'],
                'razorpay_payment_id' => $attributes['razorpay_payment_id'],
                'razorpay_signature' => $attributes['razorpay_signature'],
            ]);

            $this->logPayment($order, 'verify', $attributes, ['verified' => true], 'success');

            return true;
        } catch (SignatureVerificationError $e) {
            $this->logPayment($order, 'verify', $attributes, ['message' => $e->getMessage()], 'failed');
            Log::error('payment_verify_failed', [
                'razorpay_order_id' => $attributes['razorpay_order_id'] ?? null,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function verifyWebhookSignature(string $payload, ?string $signature): bool
    {
        $webhookSecret = config('services.razorpay.webhook_secret');

        if (!$webhookSecret || !$signature) {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $payload, $webhookSecret), $signature);
    }

    public function capturePayment(string $paymentId, Order $order): array
    {
        $request = ['payment_id' => $paymentId, 'amount' => $this->amountInPaise($order)];

        try {
            $payment = $this->razorpay->payment->fetch($paymentId);
            $captured = $payment->capture(['amount' => $request['amount']]);
            $response = $captured->toArray();
        } catch (\Throwable $e) {
            $this->logPayment($order, 'capture', $request, ['message' => $e->getMessage()], 'failed');
            throw $e;
        }

        $this->logPayment($order, 'capture', $request, $response, 'success');

        return $response;
    }

    public function refundPayment(Order $order): array
    {
        if (!$order->razorpay_payment_id || $order->payment_status !== 'captured') {
            throw new RuntimeException('Only captured Razorpay payments can be refunded.');
        }

        $request = [
            'payment_id' => $order->razorpay_payment_id,
            'amount' => $this->amountInPaise($order),
        ];

        try {
            $payment = $this->razorpay->payment->fetch($order->razorpay_payment_id);
            $refund = $payment->refund(['amount' => $request['amount']]);
            $response = $refund->toArray();
        } catch (\Throwable $e) {
            $this->logPayment($order, 'refund', $request, ['message' => $e->getMessage()], 'failed');
            throw $e;
        }

        $this->logPayment($order, 'refund', $request, $response, 'success');

        return $response;
    }

    public function processWebhook(array $payload, ?string $rawPayload = null): bool
    {
        $event = $payload['event'] ?? null;
        $data = $payload['payload'] ?? [];
        $eventId = $payload['id'] ?? hash('sha256', json_encode($payload));
        $payloadHash = hash('sha256', $rawPayload ?? json_encode($payload));

        return DB::transaction(function () use ($event, $data, $eventId, $payloadHash, $payload) {
            $paymentEvent = PaymentEvent::where('provider', 'razorpay')
                ->where('event_id', $eventId)
                ->lockForUpdate()
                ->first();

            if ($paymentEvent?->processed_at) {
                $this->logPayment(null, 'webhook', $payload, ['duplicate' => true], 'duplicate');
                return false;
            }

            if (!$paymentEvent) {
                $paymentEvent = PaymentEvent::create([
                    'event_id' => $eventId,
                    'provider' => 'razorpay',
                    'payload_hash' => $payloadHash,
                ]);
            }

            if ($event === 'payment.captured') {
                $this->handlePaymentCaptured($data);
            } elseif ($event === 'payment.authorized') {
                $this->handlePaymentAuthorized($data);
            } elseif ($event === 'payment.failed') {
                $this->handlePaymentFailed($data);
            } elseif ($event === 'refund.processed') {
                $this->handleRefundProcessed($data);
            }

            $paymentEvent->update(['processed_at' => now()]);
            $this->logPayment(null, 'webhook', $payload, ['event' => $event], 'success');

            return true;
        });
    }

    public function reconcilePendingPayments(): array
    {
        $summary = ['checked' => 0, 'updated' => 0, 'failed' => 0];

        Order::query()
            ->whereIn('payment_status', ['pending', 'authorized'])
            ->whereNotNull('razorpay_order_id')
            ->chunkById(50, function ($orders) use (&$summary) {
                foreach ($orders as $order) {
                    $summary['checked']++;

                    try {
                        if ($this->reconcileOrder($order)) {
                            $summary['updated']++;
                        }
                    } catch (\Throwable $e) {
                        $summary['failed']++;
                        $this->logPayment($order, 'reconcile', ['order_id' => $order->id], ['message' => $e->getMessage()], 'failed');
                    }
                }
            });

        return $summary;
    }

    public function reconcileOrder(Order $order): bool
    {
        $payments = $this->fetchRazorpayPaymentsForOrder($order);
        $items = $payments['items'] ?? [];

        foreach ($items as $payment) {
            $status = $payment['status'] ?? null;

            if (($payment['amount_refunded'] ?? 0) >= ($payment['amount'] ?? 0) && ($payment['amount'] ?? 0) > 0) {
                return $this->markReconciledRefunded($order, $payment);
            }

            if ($status === 'captured') {
                $this->handlePaymentCaptured([
                    'payment' => ['entity' => $payment],
                ]);
                $this->logPayment($order, 'reconcile', ['razorpay_order_id' => $order->razorpay_order_id], $payment, 'recovered');
                return true;
            }

            if ($status === 'failed') {
                $this->handlePaymentFailed([
                    'payment' => ['entity' => $payment],
                ]);
                $this->logPayment($order, 'reconcile', ['razorpay_order_id' => $order->razorpay_order_id], $payment, 'recovered');
                return true;
            }
        }

        $this->logPayment($order, 'reconcile', ['razorpay_order_id' => $order->razorpay_order_id], $payments, 'success');

        return false;
    }

    protected function handlePaymentCaptured(array $data): void
    {
        $paymentId = $data['payment']['entity']['id'];
        $orderId = $data['payment']['entity']['order_id'];
        $amount = (int) $data['payment']['entity']['amount'];
        $paymentStatus = $data['payment']['entity']['status'] ?? 'captured';

        $order = Order::where('razorpay_order_id', $orderId)
            ->with('items.product')
            ->lockForUpdate()
            ->first();

        if (!$order) {
            \Log::warning('payment_captured_unknown_order', ['razorpay_order_id' => $orderId]);
            return;
        }

        if (in_array($order->payment_status, ['captured', 'refunded'])) {
            $this->logPaymentForOrder($order, 'captured_duplicate', $data, ['existing_status' => $order->payment_status], 'duplicate');
            return;
        }

        if ($amount !== $this->amountInPaise($order)) {
            $order->update([
                'razorpay_payment_id' => $paymentId,
                'payment_status' => 'failed',
                'order_status' => \App\Enums\OrderStatus::FAILED,
                'payment_meta' => array_merge($order->payment_meta ?? [], [
                    'amount_mismatch' => [
                        'expected' => $this->amountInPaise($order),
                        'received' => $amount,
                        'currency' => $data['payment']['entity']['currency'] ?? 'INR',
                    ],
                    'webhook' => $data,
                ]),
            ]);

            \Log::critical('razorpay_payment_amount_mismatch', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'razorpay_order_id' => $orderId,
                'razorpay_payment_id' => $paymentId,
                'expected_paise' => $this->amountInPaise($order),
                'received_paise' => $amount,
            ]);

            \App\Models\Payment::where('order_id', $order->id)->where('gateway_order_id', $orderId)->update([
                'status' => 'failed',
                'transaction_id' => $paymentId,
                'payment_meta' => array_merge(
                    \App\Models\Payment::where('order_id', $order->id)->first()?->payment_meta ?? [],
                    ['amount_mismatch' => true, 'reason' => 'amount_mismatch']
                ),
            ]);

            return;
        }

        foreach ($order->items as $item) {
            $product = Product::whereKey($item->product_id)->lockForUpdate()->first();

            if (!$product) {
                throw new \RuntimeException("Product not found while capturing order {$order->order_number}.");
            }

            if ($product->stock < $item->quantity) {
                $this->logPaymentForOrder($order, 'stock_conflict', [
                    'product_id' => $item->product_id,
                    'required' => $item->quantity,
                    'available' => $product->stock,
                ], $data, 'failed');

                \Log::error('razorpay_stock_conflict', [
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'required' => $item->quantity,
                    'available' => $product->stock,
                ]);

                throw new \RuntimeException("Insufficient stock for product in order {$order->order_number}.");
            }
        }

        foreach ($order->items as $item) {
            Product::whereKey($item->product_id)->decrement('stock', $item->quantity);
        }

        $order->update([
            'razorpay_payment_id' => $paymentId,
            'payment_status' => 'captured',
            'order_status' => \App\Enums\OrderStatus::PAID,
            'paid_at' => now(),
            'payment_meta' => array_merge($order->payment_meta ?? [], ['captured_via' => 'webhook', 'webhook' => $data]),
        ]);

        \App\Models\Payment::where('order_id', $order->id)
            ->where('gateway_order_id', $orderId)
            ->each(function ($payment) use ($paymentId, $data, $paymentStatus, $order) {
                $payment->markAsCompleted($paymentId);
                $payment->update([
                    'payment_meta' => array_merge($payment->payment_meta ?? [], [
                        'webhook' => $data,
                        'payment_status_gateway' => $paymentStatus,
                        'razorpay_fee' => $data['payment']['entity']['fee'] ?? null,
                        'razorpay_tax' => $data['payment']['entity']['tax'] ?? null,
                    ]),
                ]);
            });

        $this->logPaymentForOrder($order, 'captured', ['source' => 'webhook', 'payment_id' => $paymentId], $data, 'success');

        $this->clearCartForOrder($order);

        try {
            \App\Models\PaymentEvent::create([
                'event_id' => 'captured_' . $orderId . '_' . $paymentId,
                'provider' => 'razorpay',
                'payload_hash' => hash('sha256', json_encode($data)),
                'processed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // event already tracked or unique constraint hit
        }

        Mail::to($order->customer_email)->queue(new OrderPlacedMail($order));
        Mail::to(config('mail.from.address'))->queue(new AdminOrderNotificationMail($order));
    }

    protected function handlePaymentAuthorized(array $data): void
    {
        $paymentId = $data['payment']['entity']['id'];
        $orderId = $data['payment']['entity']['order_id'];

        $order = Order::where('razorpay_order_id', $orderId)
            ->lockForUpdate()
            ->first();

        if (!$order) {
            \Log::warning('payment_authorized_unknown_order', ['razorpay_order_id' => $orderId]);
            return;
        }

        if (in_array($order->payment_status, ['captured', 'refunded'])) {
            return;
        }

        $order->update([
            'razorpay_payment_id' => $paymentId,
            'payment_status' => 'authorized',
            'payment_meta' => array_merge($order->payment_meta ?? [], ['webhook' => $data]),
        ]);

        \App\Models\Payment::where('order_id', $order->id)
            ->where('gateway_order_id', $orderId)
            ->update([
                'status' => 'authorized',
                'transaction_id' => $paymentId,
            ]);

        $this->logPaymentForOrder($order, 'authorized', ['payment_id' => $paymentId], $data, 'success');
    }

    protected function handlePaymentFailed(array $data): void
    {
        $orderId = $data['payment']['entity']['order_id'];
        $paymentId = $data['payment']['entity']['id'] ?? null;
        $failureReason = $data['payment']['entity']['error_description'] ?? 'Payment failed';

        $order = Order::where('razorpay_order_id', $orderId)
            ->lockForUpdate()
            ->first();

        if (!$order) {
            \Log::warning('payment_failed_unknown_order', ['razorpay_order_id' => $orderId]);
            return;
        }

        if (in_array($order->payment_status, ['captured', 'refunded', 'failed'])) {
            return;
        }

        $order->update([
            'payment_status' => 'failed',
            'order_status' => \App\Enums\OrderStatus::FAILED,
            'payment_meta' => array_merge($order->payment_meta ?? [], [
                'failure_reason' => $failureReason,
                'webhook' => $data,
            ]),
        ]);

        \App\Models\Payment::where('order_id', $order->id)
            ->where('gateway_order_id', $orderId)
            ->each(function ($payment) use ($failureReason, $data) {
                $payment->markAsFailed($failureReason);
                $payment->update([
                    'payment_meta' => array_merge($payment->payment_meta ?? [], ['webhook' => $data]),
                ]);
            });

        $this->logPaymentForOrder($order, 'payment_failed', [
            'payment_id' => $paymentId,
            'reason' => $failureReason,
        ], $data, 'failed');

        try {
            \App\Models\PaymentEvent::create([
                'event_id' => 'failed_' . $orderId . '_' . $paymentId,
                'provider' => 'razorpay',
                'payload_hash' => hash('sha256', json_encode($data)),
                'processed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // duplicate event — ignore
        }

        if ($order->customer_email && !str_contains($order->customer_email, 'null')) {
            \Illuminate\Support\Facades\Mail::to($order->customer_email)
                ->later(now()->addSeconds(10), new \App\Mail\PaymentFailedMail($order));
        }
    }

    protected function handleRefundProcessed(array $data): void
    {
        $paymentId = $data['refund']['entity']['payment_id'];

        $order = Order::where('razorpay_payment_id', $paymentId)
            ->lockForUpdate()
            ->first();

        if (!$order || $order->payment_status !== 'captured') {
            return;
        }

        $refundAmount = $data['refund']['entity']['amount'] ?? 0;

        $order->update([
            'payment_status' => 'refunded',
            'order_status' => \App\Enums\OrderStatus::CANCELLED,
            'payment_meta' => array_merge($order->payment_meta ?? [], ['webhook' => $data]),
        ]);

        \App\Models\Payment::where('order_id', $order->id)
            ->where('transaction_id', $paymentId)
            ->update([
                'status' => 'refunded',
                'refund_status' => 'processed',
                'refund_amount' => $refundAmount / 100,
                'payment_meta' => array_merge(
                    \App\Models\Payment::where('order_id', $order->id)->first()?->payment_meta ?? [],
                    ['webhook' => $data]
                ),
            ]);

        $this->logPaymentForOrder($order, 'refund_processed', ['refund_id' => $data['refund']['entity']['id'] ?? null], $data, 'success');

        foreach ($order->items as $item) {
            $item->product->increment('stock', $item->quantity);
        }
    }

    protected function fetchRazorpayPaymentsForOrder(Order $order): array
    {
        $request = ['razorpay_order_id' => $order->razorpay_order_id];

        try {
            $response = $this->razorpay->order
                ->fetch($order->razorpay_order_id)
                ->payments()
                ->toArray();
        } catch (\Throwable $e) {
            $this->logPayment($order, 'reconcile', $request, ['message' => $e->getMessage()], 'failed');
            throw $e;
        }

        $this->logPayment($order, 'reconcile', $request, $response, 'success');

        return $response;
    }

    protected function markReconciledRefunded(Order $order, array $payment): bool
    {
        return DB::transaction(function () use ($order, $payment) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->payment_status === 'refunded') {
                return false;
            }

            $lockedOrder->update([
                'razorpay_payment_id' => $payment['id'] ?? $lockedOrder->razorpay_payment_id,
                'payment_status' => 'refunded',
                'order_status' => OrderStatus::CANCELLED,
                'payment_meta' => array_merge($lockedOrder->payment_meta ?? [], ['reconciled_payment' => $payment]),
            ]);

            $this->logPayment($lockedOrder, 'refund', ['source' => 'reconcile'], $payment, 'success');

            return true;
        });
    }

    protected function logPayment(?Order $order, string $action, ?array $request, ?array $response, string $status): void
    {
        $payment = null;
        if ($order) {
            $payment = \App\Models\Payment::where('order_id', $order->id)->latest()->first();
        }

        PaymentLog::create([
            'order_id' => $order?->id,
            'payment_id' => $payment?->id,
            'actor_id' => auth()->id(),
            'action' => $action,
            'provider' => 'razorpay',
            'correlation_id' => request()?->headers->get('X-Correlation-ID'),
            'payment_id' => $response['id'] ?? $request['payment_id'] ?? $order?->razorpay_payment_id,
            'customer_email' => $order?->customer_email,
            'event_type' => $action,
            'latency_ms' => null,
            'environment' => app()->environment(),
            'request' => $request,
            'response' => $response,
            'status' => $status,
        ]);
    }

    private function logPaymentForOrder(?Order $order, string $action, ?array $request, ?array $response, string $status): void
    {
        $this->logPayment($order, $action, $request, $response, $status);
    }

    private function clearCartForOrder(Order $order): void
    {
        if ($order->user_id) {
            \App\Models\Cart::where('user_id', $order->user_id)->delete();
        } else {
            \Illuminate\Support\Facades\Session::forget('cart');
        }
    }
}
