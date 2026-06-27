<?php

namespace App\Http\Controllers;

use App\Services\CouponService;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function __construct(
        protected CouponService   $couponService,
        protected CartService     $cartService,
        protected CheckoutService $checkoutService
    ) {}

    /**
     * Apply a coupon code to the session.
     * Rate-limited to 20/min via throttle:coupon middleware.
     */
    public function apply(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:50|regex:/^[A-Za-z0-9_\-]+$/',
        ]);

        $code     = strtoupper(trim($request->input('code')));
        $subtotal = (float) $this->cartService->getCartTotal();

        if ($subtotal <= 0) {
            return response()->json(['success' => false, 'message' => 'Your cart is empty.'], 422);
        }

        $coupon = $this->couponService->validate($code, $subtotal, auth()->user());

        if (! $coupon) {
            $reason = $this->couponService->getInvalidReason($code, $subtotal, auth()->user());
            return response()->json(['success' => false, 'message' => $reason], 422);
        }

        $discount = $this->couponService->calculateDiscount($coupon, $subtotal);

        session([
            'coupon_code'     => $coupon->code,
            'coupon_id'       => $coupon->id,
            'coupon_discount' => $discount,
        ]);

        $summary = $this->checkoutService->getOrderSummary($subtotal);

        return response()->json([
            'success'        => true,
            'message'        => 'Coupon applied successfully!',
            'code'           => $coupon->code,
            'discount'       => $discount,
            'discount_fmt'   => '&#8377;' . number_format($discount, 2),
            'coupon_type'    => $coupon->type,
            'coupon_value'   => $coupon->value,
            'summary'        => [
                'subtotal' => $summary['subtotal'],
                'discount' => $summary['discount'],
                'shipping' => $summary['shipping'],
                'total'    => $summary['total'],
            ],
        ]);
    }

    /**
     * Remove the applied coupon from the session.
     */
    public function remove(Request $request): JsonResponse
    {
        session()->forget(['coupon_code', 'coupon_id', 'coupon_discount']);

        $subtotal = (float) $this->cartService->getCartTotal();
        $summary  = $this->checkoutService->getOrderSummary($subtotal);

        return response()->json([
            'success' => true,
            'message' => 'Coupon removed.',
            'summary' => [
                'subtotal' => $summary['subtotal'],
                'discount' => 0,
                'shipping' => $summary['shipping'],
                'total'    => $summary['total'],
            ],
        ]);
    }
}
