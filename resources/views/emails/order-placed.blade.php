<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed - {{ $order->order_number }} | {{ config('app.name') }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        .container { max-width: 680px; margin: 0 auto; padding: 40px 20px; }
        .card { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); overflow: hidden; }
        .header { background: linear-gradient(135deg, #6E0F12 0%, #520b0d 100%); padding: 40px 32px; text-align: center; }
        .header h1 { color: #C8A35D; font-family: 'Playfair Display', Georgia, serif; font-size: 32px; margin: 0 0 12px; }
        .header p { color: rgba(255,255,255,0.85); font-size: 16px; margin: 0; }
        .checkmark { display: inline-flex; align-items: center; justify-content: center; width: 80px; height: 80px; background: rgba(200,163,93,0.15); border-radius: 50%; margin-bottom: 20px; }
        .checkmark svg { width: 44px; height: 44px; color: #C8A35D; }
        .body { padding: 32px; }
        .section { margin-bottom: 28px; }
        .section-title { font-family: 'Playfair Display', Georgia, serif; font-size: 18px; color: #2E1A12; margin: 0 0 16px; font-weight: 600; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .info-item { background: #FDFBF7; border: 1px solid #E8DCCB; border-radius: 8px; padding: 14px 16px; }
        .info-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: #888; margin: 0 0 4px; font-weight: 600; }
        .info-value { font-size: 15px; color: #2E1A12; margin: 0; font-weight: 500; }
        .items-table { width: 100%; border-collapse: collapse; }
        .items-table th { text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #999; padding: 10px 8px; border-bottom: 2px solid #E8DCCB; font-weight: 600; }
        .items-table td { padding: 14px 8px; border-bottom: 1px solid #f0ede6; font-size: 14px; color: #333; vertical-align: middle; }
        .items-table tr:last-child td { border-bottom: none; }
        .product-cell { display: flex; align-items: center; gap: 14px; }
        .product-img { width: 56px; height: 56px; border-radius: 8px; object-fit: cover; border: 1px solid #e8e8e8; background: #f5f5f5; }
        .product-name { font-weight: 500; color: #2E1A12; }
        .qty-tag { display: inline-block; background: #f5f5f5; border-radius: 20px; padding: 2px 10px; font-size: 12px; color: #666; }
        .totals { background: #FDFBF7; border: 1px solid #E8DCCB; border-radius: 10px; padding: 20px; }
        .total-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 14px; color: #555; }
        .total-row.grand { border-top: 2px solid #E8DCCD; margin-top: 8px; padding-top: 14px; font-size: 18px; font-weight: 700; color: #2E1A12; }
        .total-row.grand .value { color: #6E0F12; }
        .delivery-box { background: linear-gradient(135deg, #f8f5f0, #fdfbf7); border: 1px solid #E8DCCB; border-radius: 10px; padding: 20px; display: flex; align-items: flex-start; gap: 16px; }
        .delivery-icon { font-size: 28px; flex-shrink: 0; }
        .delivery-text p { margin: 0; font-size: 14px; color: #333; }
        .delivery-text strong { color: #6E0F12; }
        .footer { text-align: center; padding: 24px 32px; background: #fafafa; border-top: 1px solid #eee; font-size: 13px; color: #999; }
        .btn { display: inline-block; background: #6E0F12; color: #fff !important; padding: 14px 32px; border-radius: 50px; text-decoration: none; font-size: 14px; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; }
        .btn-outline { display: inline-block; background: transparent; color: #6E0F12 !important; border: 2px solid #6E0F12; margin-left: 8px; }
        .badge { display: inline-block; padding: 4px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <div class="checkmark">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h1>Order Confirmed!</h1>
                <p>Thank you for shopping with {{ config('app.name') }}. Your order has been placed successfully.</p>
            </div>

            <div class="body">
                <div class="section">
                    <div class="info-grid">
                        <div class="info-item">
                            <p class="info-label">Order Number</p>
                            <p class="info-value">{{ $order->order_number }}</p>
                        </div>
                        <div class="info-item">
                            <p class="info-label">Order Date</p>
                            <p class="info-value">{{ $order->created_at->format('F d, Y') }}</p>
                        </div>
                        <div class="info-item">
                            <p class="info-label">Payment Status</p>
                            <p class="info-value">
                                @php
                                    $statusClass = match($order->payment_status) {
                                        'captured' => 'badge-success',
                                        'pending' => 'badge-warning',
                                        'failed' => 'badge-danger',
                                        'authorized' => 'badge-info',
                                        default => 'badge-warning',
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ ucfirst($order->payment_status) }}</span>
                            </p>
                        </div>
                        <div class="info-item">
                            <p class="info-label">Payment Method</p>
                            <p class="info-value">{{ strtoupper($order->payment_method ?? 'N/A') }}</p>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h3 class="section-title">Items Ordered</h3>
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th style="text-align: right;">Price</th>
                                <th style="text-align: right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        @php
                                            $img = asset('images/placeholder.jpg');
                                            if ($item->product && $item->product->thumbnail) {
                                                $img = asset('storage/' . $item->product->thumbnail);
                                            }
                                        @endphp
                                        <img src="{{ $img }}" alt="{{ $item->product_name }}" class="product-img" onerror="this.src='{{ asset('images/placeholder.jpg') }}'">
                                        <span class="product-name">{{ $item->product_name }}</span>
                                    </div>
                                </td>
                                <td><span class="qty-tag">x{{ $item->quantity }}</span></td>
                                <td style="text-align: right;">₹{{ number_format($item->price, 2) }}</td>
                                <td style="text-align: right; font-weight: 500;">₹{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="section">
                    <div class="totals">
                        <div class="total-row">
                            <span>Subtotal</span>
                            <span>₹{{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        <div class="total-row">
                            <span>Shipping</span>
                            <span>{{ $order->shipping > 0 ? '₹'.number_format($order->shipping, 2) : 'FREE' }}</span>
                        </div>
                        @if($order->discount > 0)
                        <div class="total-row">
                            <span>Discount</span>
                            <span style="color: #28a745;">-₹{{ number_format($order->discount, 2) }}</span>
                        </div>
                        @endif
                        <div class="total-row grand">
                            <span>Total Paid</span>
                            <span class="value">₹{{ number_format($order->total, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <div class="delivery-box">
                        <div class="delivery-icon">📦</div>
                        <div class="delivery-text">
                            @php
                                function addBusinessDays($date, $days) {
                                    $currentDate = clone $date;
                                    $added = 0;
                                    while ($added < $days) {
                                        $currentDate->addDay();
                                        if (!in_array($currentDate->dayOfWeek, [0, 6])) { // 0 = Sunday, 6 = Saturday
                                            $added++;
                                        }
                                    }
                                    return $currentDate;
                                }
                                $deliveryDate = addBusinessDays($order->created_at, 7);
                            @endphp
                            <p><strong>Estimated Delivery:</strong> {{ $deliveryDate->format('F d, Y') }}</p>
                            <p style="margin-top: 6px; color: #666; font-size: 13px;">
                                We will send tracking information once shipped.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h3 class="section-title">Shipping Address</h3>
                    <div style="background: #FDFBF7; border: 1px solid #E8DCCB; border-radius: 10px; padding: 20px; font-size: 14px; color: #333; white-space: pre-line; line-height: 1.7;">
                        {{ $order->shipping_address }}
                    </div>
                </div>

                <div style="text-align: center; margin-top: 32px;">
                    <a href="{{ route('orders.show', $order) }}" class="btn">Track Order</a>
                    <a href="{{ route('products.index') }}" class="btn btn-outline">Continue Shopping</a>
                </div>
            </div>

            <div class="footer">
                <p>Questions? Contact us at {{ config('mail.from.address') }}</p>
                <p style="margin-top: 4px;">{{ config('app.name') }} — Crafted with love, adorned with gold.</p>
            </div>
        </div>
    </div>
</body>
</html>
