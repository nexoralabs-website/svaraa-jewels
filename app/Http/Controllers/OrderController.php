<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('pages.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        // Gate via policy (auto-discovered: OrderPolicy::view)
        $this->authorize('view', $order);

        $order->load(['items.product.images', 'refund']);

        // Can the user request a refund?
        $canRefund = $order->payment_status === 'captured'
            && is_null($order->refund)
            && ! in_array($order->order_status->value, ['cancelled', 'failed', 'refunded']);

        return view('pages.orders.show', compact('order', 'canRefund'));
    }
}
