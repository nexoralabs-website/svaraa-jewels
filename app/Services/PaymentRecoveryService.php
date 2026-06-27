<?php

namespace App\Services;

use App\Mail\AdminOrderNotificationMail;
use App\Mail\OrderPlacedMail;
use App\Models\Order;
use App\Models\PaymentLog;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

class PaymentRecoveryService
{
    public function __construct(
        protected PaymentService $paymentService,
        protected AdminActivityLogger $activityLogger,
        protected PaymentStructuredLogger $logger,
    ) {
    }

    public function retryReconciliation(Order $order, ?User $actor = null): bool
    {
        $updated = $this->paymentService->reconcileOrder($order);
        $this->activityLogger->log('retry_reconciliation', $actor, $order, ['updated' => $updated]);

        return $updated;
    }

    public function retryPaymentVerification(Order $order, ?User $actor = null): bool
    {
        if (!$order->razorpay_order_id || !$order->razorpay_payment_id || !$order->razorpay_signature) {
            $this->activityLogger->log('retry_payment_verification_skipped', $actor, $order, ['reason' => 'missing_signature_fields']);
            return false;
        }

        $verified = $this->paymentService->verifySignature([
            'razorpay_order_id' => $order->razorpay_order_id,
            'razorpay_payment_id' => $order->razorpay_payment_id,
            'razorpay_signature' => $order->razorpay_signature,
        ]);

        $this->activityLogger->log('retry_payment_verification', $actor, $order, ['verified' => $verified]);

        return $verified;
    }

    public function retryEmailDispatch(Order $order, ?User $actor = null): bool
    {
        if ($order->payment_method !== 'cod' && $order->payment_status !== 'captured') {
            $this->activityLogger->log('retry_email_dispatch_skipped', $actor, $order, ['reason' => 'order_not_finalized']);
            return false;
        }

        Mail::to($order->customer_email)->queue(new OrderPlacedMail($order));
        Mail::to(config('mail.from.address'))->queue(new AdminOrderNotificationMail($order));
        $this->activityLogger->log('retry_email_dispatch', $actor, $order);
        $this->logger->log('info', 'retry_email_dispatch', $order, action: 'email_retry', status: 'queued');

        return true;
    }

    public function retryWebhookProcessing(PaymentLog $webhookLog, ?User $actor = null): bool
    {
        if ($webhookLog->action !== 'webhook' || empty($webhookLog->request)) {
            $this->activityLogger->log('retry_webhook_processing_skipped', $actor, targetType: PaymentLog::class, targetId: (string) $webhookLog->id, metadata: ['reason' => 'missing_webhook_payload']);
            return false;
        }

        $processed = $this->paymentService->processWebhook($webhookLog->request);
        $this->activityLogger->log('retry_webhook_processing', $actor, targetType: PaymentLog::class, targetId: (string) $webhookLog->id, metadata: ['processed' => $processed]);

        return $processed;
    }

    public function retryOrderFinalization(Order $order, ?User $actor = null): bool
    {
        if ($order->payment_status === 'captured') {
            $this->activityLogger->log('retry_order_finalization_skipped', $actor, $order, ['reason' => 'already_finalized']);
            return false;
        }

        $updated = $this->retryReconciliation($order, $actor);
        $this->activityLogger->log('retry_order_finalization', $actor, $order, ['updated' => $updated]);

        return $updated;
    }

    public function retryFailedJob(string $uuid, ?User $actor = null): int
    {
        $exitCode = Artisan::call('queue:retry', ['id' => [$uuid]]);
        $this->activityLogger->log('retry_failed_job', $actor, metadata: ['uuid' => $uuid, 'exit_code' => $exitCode], targetType: 'failed_job', targetId: $uuid);

        return $exitCode;
    }
}
