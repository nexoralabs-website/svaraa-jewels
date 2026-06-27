<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Reminder - {{ $order->order_number }} | {{ config('app.name') }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 40px 20px; }
        .card { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); overflow: hidden; }
        .header { background: linear-gradient(135deg, #C8A35D 0%, #a88a3e 100%); padding: 36px 32px; text-align: center; }
        .header h1 { color: #fff; font-family: 'Playfair Display', Georgia, serif; font-size: 26px; margin: 0 0 8px; }
        .header p { color: rgba(255,255,255,0.9); font-size: 15px; margin: 0; }
        .body { padding: 32px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 24px; }
        .info-item { background: #FDFBF7; border: 1px solid #E8DCCB; border-radius: 8px; padding: 14px 16px; }
        .info-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.08em; color: #999; margin: 0 0 4px; font-weight: 700; }
        .info-value { font-size: 14px; color: #2E1A12; margin: 0; font-weight: 500; }
        .btn { display: inline-block; background: #C8A35D; color: #fff !important; padding: 14px 32px; border-radius: 50px; text-decoration: none; font-size: 14px; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; }
        .footer { text-align: center; padding: 24px 32px; background: #fafafa; border-top: 1px solid #eee; font-size: 13px; color: #999; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>⏰ Complete Your Payment</h1>
                <p>Your order #{{ $order->order_number }} is waiting for payment confirmation.</p>
            </div>
            <div class="body">
                <div class="info-grid">
                    <div class="info-item">
                        <p class="info-label">Order Number</p>
                        <p class="info-value">{{ $order->order_number }}</p>
                    </div>
                    <div class="info-item">
                        <p class="info-label">Order Total</p>
                        <p class="info-value">₹{{ number_format($order->total, 2) }}</p>
                    </div>
                    <div class="info-item">
                        <p class="info-label">Payment Method</p>
                        <p class="info-value">{{ strtoupper($order->payment_method ?? 'N/A') }}</p>
                    </div>
                    <div class="info-item">
                        <p class="info-label">Order Date</p>
                        <p class="info-value">{{ $order->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
                <div style="background: #fff8e1; border: 1px solid #ffc107; border-radius: 8px; padding: 14px 18px; margin-bottom: 24px;">
                    <p style="margin: 0; font-size: 13px; color: #856404;">Please complete your payment to avoid order cancellation. Your items are reserved for a limited time.</p>
                </div>
                <div style="text-align: center;">
                    <a href="{{ $retryUrl ?? route('orders.show', $order) }}" class="btn">Complete Payment</a>
                </div>
            </div>
            <div class="footer">
                {{ config('app.name') }} · Need help? Contact {{ config('mail.from.address') }}
            </div>
        </div>
    </div>
</body>
</html>
