<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\RefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    public function __construct(protected RefundService $refundService) {}

    /**
     * Show refund request form.
     */
    public function create(Order $order)
    {
        $this->authorize('requestRefund', $order);

        return view('pages.refunds.create', compact('order'));
    }

    /**
     * Submit a refund request.
     */
    public function store(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('requestRefund', $order);

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $this->refundService->createRefundRequest(
                $order,
                auth()->user(),
                $validated['reason']
            );

            return redirect()
                ->route('orders.show', $order)
                ->with('success', 'Your refund request has been submitted. We will process it within 3–5 business days.');

        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
