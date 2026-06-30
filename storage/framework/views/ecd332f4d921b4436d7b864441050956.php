<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed - <?php echo e($order->order_number); ?> | <?php echo e(config('app.name')); ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 40px 20px; }
        .card { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); overflow: hidden; }
        .header { background: linear-gradient(135deg, #dc3545 0%, #a71d2a 100%); padding: 36px 32px; text-align: center; }
        .header h1 { color: #fff; font-family: 'Playfair Display', Georgia, serif; font-size: 26px; margin: 0 0 8px; }
        .header p { color: rgba(255,255,255,0.85); font-size: 15px; margin: 0; }
        .icon { display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; background: rgba(255,255,255,0.2); border-radius: 50%; margin-bottom: 16px; }
        .icon svg { width: 32px; height: 32px; color: #fff; }
        .body { padding: 32px; }
        .alert-box { background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 16px 20px; margin-bottom: 24px; }
        .alert-box p { margin: 0; font-size: 14px; color: #721c24; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 24px; }
        .info-item { background: #FDFBF7; border: 1px solid #E8DCCB; border-radius: 8px; padding: 14px 16px; }
        .info-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.08em; color: #999; margin: 0 0 4px; font-weight: 700; }
        .info-value { font-size: 14px; color: #2E1A12; margin: 0; font-weight: 500; }
        .btn { display: inline-block; background: #6E0F12; color: #fff !important; padding: 14px 32px; border-radius: 50px; text-decoration: none; font-size: 14px; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; }
        .btn-outline { display: inline-block; background: transparent; color: #6E0F12 !important; border: 2px solid #6E0F12; padding: 14px 32px; border-radius: 50px; text-decoration: none; font-size: 14px; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; margin-left: 8px; }
        .footer { text-align: center; padding: 24px 32px; background: #fafafa; border-top: 1px solid #eee; font-size: 13px; color: #999; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <div class="icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                </div>
                <h1>Payment Failed</h1>
                <p>We couldn't process your payment for order #<?php echo e($order->order_number); ?></p>
            </div>
            <div class="body">
                <div class="alert-box">
                    <p>Your payment could not be completed. Don't worry — your order is still reserved. Please retry within 15 minutes to avoid cancellation.</p>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <p class="info-label">Order Number</p>
                        <p class="info-value"><?php echo e($order->order_number); ?></p>
                    </div>
                    <div class="info-item">
                        <p class="info-label">Order Total</p>
                        <p class="info-value">₹<?php echo e(number_format($order->total, 2)); ?></p>
                    </div>
                    <div class="info-item">
                        <p class="info-label">Payment Method</p>
                        <p class="info-value"><?php echo e(strtoupper($order->payment_method ?? 'N/A')); ?></p>
                    </div>
                    <div class="info-item">
                        <p class="info-label">Order Date</p>
                        <p class="info-value"><?php echo e($order->created_at->format('M d, Y')); ?></p>
                    </div>
                </div>
                <div style="text-align: center; margin-top: 28px;">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($retryUrl): ?>
                    <a href="<?php echo e($retryUrl); ?>" class="btn">Retry Payment</a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <a href="<?php echo e(route('products.index')); ?>" class="btn-outline">Continue Shopping</a>
                </div>
            </div>
            <div class="footer">
                <?php echo e(config('app.name')); ?> · Need help? Contact <?php echo e(config('mail.from.address')); ?>

            </div>
        </div>
    </div>
</body>
</html>
<?php /**PATH /var/www/html/resources/views/emails/payment-failed.blade.php ENDPATH**/ ?>