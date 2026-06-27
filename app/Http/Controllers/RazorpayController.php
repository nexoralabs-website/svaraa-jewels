<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Models\PaymentLog;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RazorpayController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    public function createOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
        ]);

        $order = Order::lockForUpdate()->findOrFail($validated['order_id']);

        $this->authorizePaymentAccess($order);

        if (isset($order->payment_id)) {
            $payment = Payment::find($order->payment_id);
            if ($payment && in_array($payment->status, ['captured', 'authorized'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment already processed. Please check your order status.',
                ], 409);
            }
        }

        if (in_array($order->payment_status, ['captured', 'refunded'])) {
            return response()->json([
                'success' => false,
                'message' => 'This order is already finalized.',
            ], 409);
        }

        try {
            $razorpayPayload = $this->paymentService->createRazorpayOrder($order);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Unable to create Razorpay order.',
            ], 422);
        } catch (\Throwable $e) {
            Log::error('razorpay_create_order_exception', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Unable to initiate payment. Please try again later.',
            ], 500);
        }

        return response()->json($razorpayPayload);
    }

    public function verifyPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $order = Order::where('razorpay_order_id', $validated['razorpay_order_id'])->lockForUpdate()->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        $this->authorizePaymentAccess($order);

        $isValid = $this->paymentService->verifySignature($validated);

        if (!$isValid) {
            $payment = Payment::where('order_id', $order->id)
                ->where('gateway_order_id', $validated['razorpay_order_id'])
                ->latest()
                ->first();

            if ($payment) {
                $payment->markAsFailed('Invalid payment signature');
            }

            $order->update([
                'payment_status' => 'failed',
                'order_status' => \App\Enums\OrderStatus::FAILED,
                'payment_meta' => array_merge($order->payment_meta ?? [], [
                    'signature_verification_failed' => true,
                    'attempted_at' => now()->toIso8601String(),
                ]),
            ]);

            $this->paymentService->logPaymentForOrder($order, 'verify_failed', $validated, [
                'reason' => 'invalid_signature',
            ], 'failed');

            return response()->json(['success' => false, 'message' => 'Invalid payment signature.'], 400);
        }

        $order->update([
            'razorpay_payment_id' => $validated['razorpay_payment_id'],
            'razorpay_signature' => $validated['razorpay_signature'],
            'payment_meta' => array_merge($order->payment_meta ?? [], [
                'checkout_callback' => [
                    'razorpay_order_id' => $validated['razorpay_order_id'],
                    'razorpay_payment_id' => $validated['razorpay_payment_id'],
                    'verified_at' => now()->toIso8601String(),
                    'verified_by' => 'client_callback',
                ],
            ]),
        ]);

        Payment::where('order_id', $order->id)
            ->where('gateway_order_id', $validated['razorpay_order_id'])
            ->update([
                'transaction_id' => $validated['razorpay_payment_id'],
                'payment_meta' => array_merge(
                    Payment::where('order_id', $order->id)->latest()->first()?->payment_meta ?? [],
                    ['client_verified' => true]
                ),
            ]);

        $this->paymentService->logPaymentForOrder($order, 'verify_success', $validated, [
            'verified' => true,
        ], 'success');

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'redirect_url' => route('checkout.success', $order),
            'message' => 'Payment signature verified. We are confirming with Razorpay.',
        ]);
    }

    public function retryPayment(Request $request, Order $order): JsonResponse
    {
        $this->authorizePaymentAccess($order);

        if (in_array($order->payment_status, ['captured'])) {
            return response()->json([
                'success' => false,
                'message' => 'This order is already paid.',
            ], 409);
        }

        if (!in_array($order->payment_status, ['failed', 'pending'])) {
            return response()->json([
                'success' => false,
                'message' => 'This order cannot be retried. Current status: ' . $order->payment_status,
            ], 422);
        }

        $order->update([
            'payment_status' => 'pending',
            'order_status' => \App\Enums\OrderStatus::PENDING,
            'payment_meta' => array_merge($order->payment_meta ?? [], [
                'retry_initiated_at' => now()->toIso8601String(),
                'retry_count' => ($order->payment_meta['retry_count'] ?? 0) + 1,
            ]),
        ]);

        \App\Models\Payment::where('order_id', $order->id)
            ->whereIn('status', ['failed', 'pending'])
            ->update(['status' => 'pending']);

        $this->paymentService->logPaymentForOrder($order, 'retry_initiated', [
            'retry_count' => $order->payment_meta['retry_count'] ?? 1,
        ], null, 'info');

        try {
            $razorpayPayload = $this->paymentService->createRazorpayOrder($order);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retry payment. Please try again.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment retry initiated.',
            'razorpay' => $razorpayPayload,
        ]);
    }

    public function webhook(Request $request)
    {
        $signature = $request->header('X-Razorpay-Signature');

        if (!$signature) {
            PaymentLog::create([
                'action' => 'webhook_rejected',
                'provider' => 'razorpay',
                'request' => ['error' => 'missing_signature_header'],
                'status' => 'failed',
            ]);
            return response()->json(['error' => 'Missing signature'], 403);
        }

        try {
            $rawPayload = $request->getContent();
            $isValidWebhook = $this->paymentService->verifyWebhookSignature($rawPayload, $signature);

            if (!$isValidWebhook) {
                PaymentLog::create([
                    'action' => 'webhook_rejected',
                    'provider' => 'razorpay',
                    'request' => ['signature' => $signature],
                    'status' => 'failed',
                ]);
                Log::warning('webhook_rejected_invalid_signature');
                return response()->json(['error' => 'Invalid signature'], 403);
            }
        } catch (\Throwable $e) {
            PaymentLog::create([
                'action' => 'webhook_rejected',
                'provider' => 'razorpay',
                'request' => ['exception' => $e->getMessage()],
                'status' => 'failed',
            ]);
            Log::error('webhook_signature_exception', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Signature check failed'], 403);
        }

        $payload = $request->all();
        $rawBody = $request->getContent();

        try {
            $processed = $this->paymentService->processWebhook($payload, $rawBody);

            return response()->json([
                'status' => 'ok',
                'processed' => $processed,
            ]);
        } catch (\Throwable $e) {
            Log::error('webhook_processing_failed', [
                'event_id' => $payload['id'] ?? null,
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);

            PaymentLog::create([
                'action' => 'webhook_error',
                'provider' => 'razorpay',
                'request' => ['event_id' => $payload['id'] ?? null],
                'response' => ['message' => $e->getMessage()],
                'status' => 'failed',
            ]);

            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function paymentStatus(Request $request, Order $order): JsonResponse
    {
        $this->authorizePaymentAccess($order);

        $payment = Payment::where('order_id', $order->id)->latest()->first();

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'order_status' => $order->order_status?->value ?? $order->order_status,
            'payment_status' => $order->payment_status,
            'payment' => $payment ? [
                'id' => $payment->id,
                'status' => $payment->status,
                'amount' => (float) $payment->amount,
                'paid_at' => $payment->paid_at?->toIso8601String(),
                'transaction_id' => $payment->transaction_id,
            ] : null,
            'can_retry' => in_array($order->payment_status, ['failed', 'pending']),
        ]);
    }

    protected function authorizePaymentAccess(Order $order): void
    {
        if (Auth::check()) {
            abort_unless($order->user_id === Auth::id(), 403);
            return;
        }

        $sessionOrderId = session('pending_payment_order_id');
        if (!$sessionOrderId || $sessionOrderId !== $order->id) {
            abort(403, 'Unauthorized payment access.');
        }
    }
}
