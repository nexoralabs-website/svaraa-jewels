<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PaymentsEnabled middleware
 *
 * Guards every customer-facing payment initiation route.
 * When PAYMENTS_ENABLED=false:
 *   - JSON requests receive HTTP 503 with a structured error body.
 *   - Browser requests are redirected to checkout with a flash message.
 *
 * What is NOT affected:
 *   - Webhooks (/webhooks/razorpay) — must always receive Razorpay callbacks.
 *   - Payment status polling (/payment/status/{order}) — read-only.
 *   - COD orders — handled entirely in CheckoutController, never touch this middleware.
 *   - Admin panel, order history, payment logs, reconciliation jobs.
 *
 * To re-enable payments: set PAYMENTS_ENABLED=true in .env, then run:
 *   php artisan config:clear
 */
class PaymentsEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('services.payments.enabled')) {
            return $next($request);
        }

        $message = 'Online payments are temporarily unavailable. '
            . 'We are currently completing our payment gateway setup. '
            . 'Please check back soon, or place your order using Cash on Delivery.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'code'    => 'payments_disabled',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return redirect()
            ->route('checkout.index')
            ->with('error', $message);
    }
}
