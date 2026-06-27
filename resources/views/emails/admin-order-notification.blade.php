<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order #{{ $order->order_number }} | {{ config('app.name') }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        .container { max-width: 700px; margin: 0 auto; padding: 40px 20px; }
        .card { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); overflow: hidden; }
        .header { background: linear-gradient(135deg, #2E1A12, #1a100c); padding: 32px; text-align: center; border-bottom: 3px solid #C8A35D; }
        .header h1 { color: #fff; font-family: 'Playfair Display', Georgia, serif; font-size: 24px; margin: 0; }
        .body { padding: 28px 32px; }
        .alert { display: flex; align-items: center; gap: 12px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 14px 18px; margin-bottom: 24px; }
        .alert-icon { font-size: 22px; }
        .alert-text { font-size: 14px; color: #856404; }
        .section { margin-bottom: 24px; }
        .section-title { font-family: 'Playfair Display', Georgia, serif; font-size: 16px; color: #2E1A12; margin: 0 0 12px; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #999; padding: 8px; border-bottom: 2px solid #eee; }
        td { padding: 10px 8px; font-size: 13px; color: #333; border-bottom: 1px solid #f5f5f5; }
        .highlight-box { background: #FDFBF7; border: 1px solid #E8DCCB; border-radius: 8px; padding: 16px; }
        .footer { text-align: center; padding: 20px 32px; background: #fafafa; border-top: 1px solid #eee; font-size: 12px; color: #aaa; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>🔔 New Order Received</h1>
            </div>
            <div class="body">
                <div class="alert">
                    <div class="alert-icon">⚠️</div>
                    <div class="alert-text">
                        A new order <strong>#{{ $order->order_number }}</strong> has been placed and requires your attention.
                        Payment status: <strong>{{ ucfirst($order->payment_status) }}</strong>
                    </div>
                </div>

                <div class="section">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div class="highlight-box">
                            <p style="margin: 0 0 4px; font-size: 11px; text-transform: uppercase; color: #999; font-weight: 600;">Customer</p>
                            <p style="margin: 0; font-size: 14px; color: #2E1A12; font-weight: 500;">{{ $order->customer_name }}</p>
                            <p style="margin: 2px 0 0; font-size: 13px; color: #666;">{{ $order->customer_email }}</p>
                            <p style="margin: 2px 0 0; font-size: 13px; color: #666;">{{ $order->customer_phone }}</p>
                        </div>
                        <div class="highlight-box">
                            <p style="margin: 0 0 4px; font-size: 11px; text-transform: uppercase; color: #999; font-weight: 600;">Order Details</p>
                            <p style="margin: 0; font-size: 14px; color: #2E1A12; font-weight: 500;">₹{{ number_format($order->total, 2) }}</p>
                            <p style="margin: 2px 0 0; font-size: 13px; color: #666;">{{ $order->payment_method ?? 'N/A' }} · {{ ucfirst($order->order_status->value) }}</p>
                            <p style="margin: 2px 0 0; font-size: 13px; color: #666;">{{ $order->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h3 class="section-title">Items</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th style="text-align: center;">Qty</th>
                                <th style="text-align: right;">Price</th>
                                <th style="text-align: right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td style="text-align: center;">{{ $item->quantity }}</td>
                                <td style="text-align: right;">₹{{ number_format($item->price, 2) }}</td>
                                <td style="text-align: right;">₹{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="section">
                    <h3 class="section-title">Shipping Address</h3>
                    <div class="highlight-box" style="white-space: pre-line; font-size: 13px; color: #333;">
                        {{ $order->shipping_address }}
                    </div>
                </div>

                @if($order->notes)
                <div class="section">
                    <h3 class="section-title">Order Notes</h3>
                    <div class="highlight-box" style="font-size: 13px; color: #555;">{{ $order->notes }}</div>
                </div>
                @endif
            </div>
            <div class="footer">
                {{ config('app.name') }} Admin Panel · {{ now()->format('M d, Y h:i A') }}
            </div>
        </div>
    </div>
</body>
</html>
