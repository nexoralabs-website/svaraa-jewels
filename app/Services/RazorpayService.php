<?php

namespace App\Services;

use Razorpay\Api\Api;
use Illuminate\Support\Facades\Log;

class RazorpayService
{
    protected Api $api;

    public function __construct()
    {
        $this->api = new Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );
    }

    /**
     * Create a Razorpay order.
     *
     * @param int $amount Amount in smallest currency unit (paise)
     * @param string $currency
     * @return array
     */
    public function createOrder(int $amount, string $currency = 'INR'): array
    {
        try {
            $order = $this->api->order->create([
                'receipt' => uniqid('order_'),
                'amount'  => $amount,
                'currency'=> $currency,
                'payment_capture' => 1,
            ]);
            return $order->toArray();
        } catch (\Exception $e) {
            Log::error('Razorpay order creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verify Razorpay signature.
     */
    public function verifySignature(string $orderId, string $paymentId, string $signature): bool
    {
        $generated = hash_hmac('sha256', $orderId . '|' . $paymentId, config('services.razorpay.secret'));
        return hash_equals($generated, $signature);
    }

    /**
     * Capture payment if needed (when payment_capture=0).
     */
    public function capturePayment(string $paymentId, int $amount): array
    {
        try {
            $payment = $this->api->payment->fetch($paymentId);
            $captured = $payment->capture(['amount' => $amount]);
            return $captured->toArray();
        } catch (\Exception $e) {
            Log::error('Razorpay capture failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
