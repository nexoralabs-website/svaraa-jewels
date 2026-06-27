<?php

namespace App\Listeners;

use App\Services\CartService;
use Illuminate\Auth\Events\Login;

class MergeCartAfterLogin
{
    public function __construct(protected CartService $cartService)
    {
    }

    public function handle(Login $event): void
    {
        $this->cartService->mergeGuestCartToUser($event->user->id);
    }
}
