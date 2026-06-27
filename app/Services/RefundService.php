<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;

class RefundService
{
    public function createRefundRequest(Order $order, User $user, string $reason, ?string $proofUrl = null): Refund
    {
        if ($order->user_id !== $user->id) {
            throw new \RuntimeException('You are not authorized to request a refund for this order.');
        }

        if (! in_array($order->payment_status, ['captured'])) {
            throw new \RuntimeException('Only paid orders can be refunded. Current status: ' . $order->payment_status);
        }

        if ($order->refund()->where('status', '!=', 'rejected')->exists()) {
            throw new \RuntimeException('A refund request already exists for this order.');
        }

        return DB::transaction(function () use ($order, $user, $reason, $proofUrl) {
            $refund = Refund::create([
                'order_id'     => $order->id,
                'user_id'      => $user->id,
                'status'       => 'pending',
                'reason'       => $reason,
                'proof_url'    => $proofUrl,
                'amount'       => $order->total,
                'requested_at' => now(),
            ]);

            $this->logActivity('refund_requested', $user, $order, [
                'refund_id' => $refund->id,
                'amount'    => $refund->amount,
            ]);

            return $refund;
        });
    }

    /**
     * Admin approves a refund — triggers Razorpay, restores stock, updates order.
     */
    public function approveAndProcess(Refund $refund): bool
    {
        if ($refund->status !== 'pending') {
            throw new \RuntimeException("Refund is already {$refund->status}.");
        }

        $order = $refund->order()->with('items.product')->firstOrFail();

        if (! $order->razorpay_payment_id) {
            throw new \RuntimeException('No Razorpay payment ID found on this order. Cannot issue refund.');
        }

        return DB::transaction(function () use ($refund, $order) {
            // 1. Call Razorpay refund API
            $razorpayRefundId = $this->callRazorpayRefund($order);

            // 2. Restore stock
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                    // Re-enable product if it was disabled due to zero stock
                    if (! $item->product->status) {
                        $item->product->update(['status' => true]);
                    }
                }
            }

            // 3. Update refund record
            $refund->update([
                'status'             => 'processed',
                'razorpay_refund_id' => $razorpayRefundId,
                'refunded_amount'    => $refund->amount,
                'processed_at'       => now(),
            ]);

            // 4. Update order and payment status
            $order->update([
                'payment_status' => 'refunded',
                'order_status'   => \App\Enums\OrderStatus::CANCELLED,
            ]);

            if ($order->payment) {
                $order->payment->update([
                    'status'        => 'refunded',
                    'refund_status' => 'processed',
                    'refund_amount' => $refund->amount,
                ]);
            }

            $this->logActivity('refund_processed', Auth::user(), $order, [
                'refund_id'          => $refund->id,
                'razorpay_refund_id' => $razorpayRefundId,
            ]);

            return true;
        });
    }

    /**
     * Trigger the Razorpay refund API and return the refund ID.
     */
    private function callRazorpayRefund(Order $order): string
    {
        try {
            $api     = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
            $payment = $api->payment->fetch($order->razorpay_payment_id);
            $refund  = $payment->refund(['amount' => (int) round($order->total * 100)]);
            return $refund->id;
        } catch (\Throwable $e) {
            Log::error('Razorpay refund API call failed', [
                'order_id'   => $order->id,
                'payment_id' => $order->razorpay_payment_id,
                'error'      => $e->getMessage(),
            ]);
            throw new \RuntimeException('Razorpay refund failed: ' . $e->getMessage(), 0, $e);
        }
    }

    public function processRefund(Refund $refund, string $razorpayRefundId): bool
    {
        return DB::transaction(function () use ($refund, $razorpayRefundId) {
            $refund->update([
                'status'             => 'processed',
                'razorpay_refund_id' => $razorpayRefundId,
                'refunded_amount'    => $refund->amount,
                'processed_at'       => now(),
            ]);

            $refund->order->update(['payment_status' => 'refunded']);

            $this->logActivity('refund_processed', null, $refund->order, [
                'refund_id'          => $refund->id,
                'razorpay_refund_id' => $razorpayRefundId,
            ]);

            return true;
        });
    }

    public function rejectRefund(Refund $refund, string $adminNotes): bool
    {
        $refund->update([
            'status'      => 'rejected',
            'admin_notes' => $adminNotes,
        ]);

        $this->logActivity('refund_rejected', null, $refund->order, [
            'refund_id'   => $refund->id,
            'admin_notes' => $adminNotes,
        ]);

        return true;
    }

    protected function logActivity(string $action, ?User $actor, Order $order, array $metadata = []): void
    {
        try {
            \App\Models\AdminActivityLog::create([
                'actor_id'  => $actor?->id,
                'order_id'  => $order->id,
                'action'    => $action,
                'metadata'  => $metadata,
                'acted_at'  => now(),
            ]);
        } catch (\Throwable) {
            // Non-critical — do not break the refund flow
        }
    }
}