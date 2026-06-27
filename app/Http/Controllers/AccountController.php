<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Address;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $recentOrders = Order::where('user_id', $user->id)
            ->with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();
            
        $addresses = Address::where('user_id', $user->id)->get();

        return view('pages.account.index', compact('user', 'recentOrders', 'addresses'));
    }

    public function orders(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('pages.account.orders', compact('orders'));
    }

    public function showOrder(Request $request, Order $order)
    {
        // Ensure user owns this order
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        $order->load(['items.product', 'address']);

        return view('pages.account.order-detail', compact('order'));
    }
}
