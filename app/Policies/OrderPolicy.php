<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Users can view their own orders.
     * Guests are handled separately in the controller via session check.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->user_id;
    }

    /**
     * Only the order owner can request a refund.
     */
    public function requestRefund(User $user, Order $order): bool
    {
        return $user->id === $order->user_id
            && $order->payment_status === 'captured';
    }

    /**
     * Only the order owner can download the invoice.
     */
    public function downloadInvoice(User $user, Order $order): bool
    {
        return $user->id === $order->user_id
            && in_array($order->payment_status, ['captured', 'refunded']);
    }
}
