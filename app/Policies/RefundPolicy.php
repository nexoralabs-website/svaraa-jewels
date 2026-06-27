<?php

namespace App\Policies;

use App\Models\Refund;
use App\Models\User;

class RefundPolicy
{
    /**
     * Only the refund owner can view the refund.
     */
    public function view(User $user, Refund $refund): bool
    {
        return $user->id === $refund->user_id;
    }
}
