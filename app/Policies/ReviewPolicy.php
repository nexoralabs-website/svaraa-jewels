<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * Users can only delete their own pending reviews.
     */
    public function delete(User $user, Review $review): bool
    {
        return $user->id === $review->user_id && $review->status === 'pending';
    }
}
