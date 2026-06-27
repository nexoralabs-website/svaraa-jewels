<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\CartRecoveryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class RecoverAbandonedCarts extends Command
{
    protected $signature = 'carts:recover-abandoned {--minutes=30}';

    protected $description = 'Send recovery emails for abandoned carts.';

    public function handle(CartRecoveryService $recoveryService): int
    {
        $abandoned = $recoveryService->detectAbandonedCarts((int) $this->option('minutes'));
        $count = 0;

        foreach ($abandoned as $cart) {
            $user = User::find($cart['user_id']);
            if (!$user) {
                continue;
            }

            $coupon = $recoveryService->generateRecoveryCoupon($user);

            if ($coupon && $user->email) {
                Mail::raw(
                    "We noticed you left items in your cart. Use coupon {$coupon} for ₹100 off!",
                    fn ($message) => $message->to($user->email)->subject('Complete your purchase')
                );
                $count++;
            }
        }

        $this->info("Recovery emails sent: {$count}");

        return self::SUCCESS;
    }
}