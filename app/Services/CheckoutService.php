<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Mail\AdminOrderNotificationMail;
use App\Mail\OrderPlacedMail;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CheckoutService
{
    public function __construct(protected CartService $cartService)
    {
    }

    public function getCheckoutData(): array
    {
        $cartItems = $this->cartService->getCartItems();

        if ($cartItems->isEmpty()) {
            return ['redirect' => route('cart.index'), 'error' => 'Your cart is empty.'];
        }

        $addresses = $this->getUserAddresses();
        $selectedAddressId = session('checkout_address_id', $addresses->first()?->id);

        return [
            'cartItems' => $cartItems,
            'subtotal' => $this->cartService->getCartTotal(),
            'addresses' => $addresses,
            'selectedAddressId' => $selectedAddressId,
        ];
    }

    public function getOrderSummary(float $subtotal = null): array
    {
        $subtotal  = $subtotal ?? $this->cartService->getCartTotal();
        $discount  = (float) session('coupon_discount', 0);
        $shipping  = $this->calculateShipping($subtotal - $discount);
        $tax       = $this->calculateTax($subtotal);
        $total     = max(0, $subtotal - $discount + $shipping + $tax);

        return [
            'subtotal'        => $subtotal,
            'shipping'        => $shipping,
            'tax'             => $tax,
            'discount'        => $discount,
            'coupon_code'     => session('coupon_code'),
            'coupon_id'       => session('coupon_id'),
            'coupon_discount' => $discount,
            'total'           => $total,
        ];
    }

    public function placeOrder(array $validatedData): Order
    {
        $cartItems = $this->cartService->getCartItems();

        if ($cartItems->isEmpty()) {
            throw new \RuntimeException('Your cart is empty. Please add items before checkout.');
        }

        $this->validateStock($cartItems);

        $userId = Auth::id();
        $addressId = $validatedData['address_id'] ?? null;
        $address = $this->resolveAddress($addressId, $validatedData);
        $summary = $this->getOrderSummary();

        return DB::transaction(function () use ($validatedData, $cartItems, $userId, $address, $summary) {
            $orderNumber = $this->generateOrderNumber();

            $order = Order::create([
                'user_id' => $userId,
                'order_number' => $orderNumber,
                'subtotal' => $summary['subtotal'],
                'discount' => $summary['discount'],
                'shipping' => $summary['shipping'],
                'tax' => $summary['tax'],
                'total' => $summary['total'],
                'payment_method' => $validatedData['payment_method'],
                'payment_status' => $validatedData['payment_method'] === 'cod' ? 'pending' : 'pending',
                'order_status' => OrderStatus::PENDING,
                'customer_name' => $validatedData['customer_name'],
                'customer_email' => $validatedData['customer_email'],
                'customer_phone' => $validatedData['customer_phone'],
                'shipping_address' => $this->formatShippingAddress($address ?? $validatedData),
                'notes' => $validatedData['notes'] ?? null,
                'coupon_id'       => $summary['coupon_id'] ?? null,
                'coupon_code'     => $summary['coupon_code'] ?? null,
                'coupon_discount' => $summary['coupon_discount'] ?? 0,
                'payment_meta' => [],
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product->id,
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                    'subtotal' => $item->product->price * $item->quantity,
                ]);
            }

            if ($validatedData['payment_method'] === 'cod') {
                $order->update(['order_status' => OrderStatus::PROCESSING]);
                $this->reduceStock($cartItems);
                $this->cartService->clearCart();
            }

            return $order;
        });
    }

    public function confirmOrder(Order $order): void
    {
        // For online payments: stock was NOT reduced in placeOrder(), do it now.
        // For COD: stock was already reduced in placeOrder(), skip.
        if ($order->payment_method !== 'cod') {
            $cartItems = $this->cartService->getCartItems();
            if ($cartItems->isNotEmpty()) {
                $this->reduceStock($cartItems);
            }
        }

        $this->cartService->clearCart();

        if ($order->user_id) {
            Cart::where('user_id', $order->user_id)->delete();
        }

        // Increment coupon usage if applied
        if ($order->coupon_id) {
            $coupon = \App\Models\Coupon::find($order->coupon_id);
            if ($coupon) {
                app(\App\Services\CouponService::class)->incrementUsage($coupon);
            }
        }

        // Clear coupon session
        session()->forget(['coupon_code', 'coupon_id', 'coupon_discount']);

        $order->update([
            'payment_status' => 'captured',
            'order_status' => OrderStatus::PAID,
            'paid_at' => now(),
        ]);

        Mail::to($order->customer_email)->queue(new OrderPlacedMail($order));
        Mail::to(config('mail.from.address'))->queue(new AdminOrderNotificationMail($order));
    }

    public function markOrderFailed(Order $order): void
    {
        $order->update([
            'payment_status' => 'failed',
            'order_status' => OrderStatus::FAILED,
        ]);
    }

    private function validateStock(Collection $cartItems): void
    {
        foreach ($cartItems as $item) {
            $product = $item->product;
            if (!$product || $product->stock < $item->quantity) {
                $available = (int) ($product?->stock ?? 0);
                $productName = $product?->name ?? 'a removed product';
                throw new \RuntimeException(
                    "Insufficient stock for {$productName}. Only {$available} available."
                );
            }
        }
    }

    private function reduceStock(Collection $cartItems): void
    {
        foreach ($cartItems as $item) {
            $product = Product::whereKey($item->product->id)->lockForUpdate()->first();
            if ($product && $product->stock >= $item->quantity) {
                $product->decrement('stock', $item->quantity);
            }
        }
    }

    private function getUserAddresses(): Collection
    {
        if (!Auth::check()) {
            return collect();
        }

        return Address::where('user_id', Auth::id())
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();
    }

    private function resolveAddress(?int $addressId, array $validatedData): ?Address
    {
        if ($addressId && Auth::check()) {
            return Address::where('user_id', Auth::id())
                ->where('id', $addressId)
                ->first();
        }

        if (!Auth::check() && ($addressId || !empty($validatedData['full_name']))) {
            return new Address([
                'full_name' => $validatedData['customer_name'],
                'phone' => $validatedData['customer_phone'],
                'address_line' => $validatedData['shipping_address'],
                'city' => $validatedData['city'] ?? '',
                'state' => $validatedData['state'] ?? '',
                'pincode' => $validatedData['pincode'] ?? '',
                'country' => $validatedData['country'] ?? 'India',
            ]);
        }

        return null;
    }

    private function formatShippingAddress(?Address $address, array $validatedData = []): string
    {
        if ($address) {
            return trim(
                ($address->full_name ?? '') . "\n" .
                ($address->phone ?? '') . "\n" .
                ($address->address_line ?? '') . "\n" .
                ($address->city ?? '') . ($address->state ? ', ' . $address->state : '') .
                ($address->pincode ? ' - ' . $address->pincode : '') . "\n" .
                ($address->country ?? 'India')
            );
        }

        $lines = [
            $validatedData['customer_name'] ?? '',
            $validatedData['customer_phone'] ?? '',
            $validatedData['shipping_address'] ?? '',
        ];

        $addressDetail = trim(($validatedData['address_line'] ?? ''));

        if ($addressDetail) {
            $lines[] = $addressDetail;
        }

        return implode("\n", array_filter($lines));
    }

    private function generateOrderNumber(): string
    {
        $prefix = 'SVR-' . date('Ymd') . '-';
        $random = strtoupper(str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT));

        $exists = Order::where('order_number', $prefix . $random)->exists();

        if ($exists) {
            $random = strtoupper(str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT));
        }

        return $prefix . $random;
    }

    private function calculateShipping(float $subtotal): float
    {
        return $subtotal > 50000 ? 0 : 500;
    }

    private function calculateTax(float $subtotal): float
    {
        return 0;
    }
}

