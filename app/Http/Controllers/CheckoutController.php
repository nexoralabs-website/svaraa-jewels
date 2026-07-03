<?php

namespace App\Http\Controllers;

use App\Services\CheckoutService;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        protected CheckoutService $checkoutService,
        protected CartService $cartService
    ) {}

    public function index(): View|RedirectResponse
    {
        $data = $this->checkoutService->getCheckoutData();

        if (isset($data['redirect'])) {
            return redirect($data['redirect'])->with('error', $data['error']);
        }

        $summary = $this->checkoutService->getOrderSummary($data['subtotal']);

        return view('pages.checkout.index', [
            'cartItems'         => $data['cartItems'],
            'subtotal'          => $summary['subtotal'],
            'shipping'          => $summary['shipping'],
            'tax'               => $summary['tax'],
            'total'             => $summary['total'],
            'discount'          => $summary['discount'],
            'coupon_code'       => $summary['coupon_code'],
            'coupon_discount'   => $summary['coupon_discount'],
            'addresses'         => $data['addresses'],
            'selectedAddressId' => $data['selectedAddressId'],
        ]);
    }

    public function process(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:500',
            'address_line' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'address_id' => 'nullable|integer|exists:addresses,id',
            'payment_method' => 'required|in:cod,card,upi',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($this->cartService->getCartItems()->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        // Guard: block online payment methods when payments are disabled.
        // COD is always allowed. The frontend also hides card/UPI options,
        // but this server-side check prevents direct POST manipulation.
        if ($validated['payment_method'] !== 'cod' && ! config('services.payments.enabled')) {
            $message = 'Online payments are temporarily unavailable. '
                . 'We are currently completing our payment gateway setup. '
                . 'Please check back soon, or place your order using Cash on Delivery.';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 503);
            }

            return back()->with('error', $message);
        }

        try {
            $order = $this->checkoutService->placeOrder($validated);

            if ($validated['payment_method'] === 'cod') {
                $this->checkoutService->confirmOrder($order);

                return redirect()->route('checkout.success', $order->id);
            }

            $request->session()->put('pending_payment_order_id', $order->id);

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'payment_create_url' => route('payment.create'),
            ]);
        } catch (\RuntimeException $e) {
            $status = $request->expectsJson() ? 422 : 302;
            $message = $e->getMessage() ?: 'Something went wrong while processing your order. Please try again.';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->with('error', $message);
        } catch (\Throwable $e) {
            $status = $request->expectsJson() ? 422 : 302;
            $message = 'Something went wrong while processing your order. Please try again.';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->with('error', $message);
        }
    }

    public function success(mixed $order): View
    {
        if (is_numeric($order)) {
            $order = \App\Models\Order::findOrFail($order);
        }

        if ($order->user_id && $order->user_id !== auth()->id()) {
            abort(403);
        }

        if (!$order->user_id && $order->payment_status === 'captured') {
            $this->cartService->clearCart();
            session()->forget('pending_payment_order_id');
        }

        return view('pages.checkout.success', compact('order'));
    }
}
