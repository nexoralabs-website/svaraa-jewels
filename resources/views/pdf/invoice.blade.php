<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice {{ $order->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1a1a1a; background: #fff; }
        .page { padding: 40px; }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 36px; border-bottom: 3px solid #6E0F12; padding-bottom: 24px; }
        .brand-name { font-size: 28px; font-weight: 700; color: #6E0F12; letter-spacing: 1px; }
        .brand-tagline { font-size: 11px; color: #C8A35D; margin-top: 2px; }
        .invoice-title { text-align: right; }
        .invoice-title h2 { font-size: 22px; font-weight: 700; color: #6E0F12; text-transform: uppercase; letter-spacing: 2px; }
        .invoice-title .invoice-number { font-size: 13px; color: #555; margin-top: 4px; }
        .invoice-title .invoice-date { font-size: 11px; color: #888; margin-top: 2px; }

        /* Meta grid */
        .meta-grid { display: flex; gap: 0; margin-bottom: 28px; }
        .meta-box { flex: 1; padding: 16px 20px; border: 1px solid #E8DCCB; background: #FDFBF7; }
        .meta-box:not(:last-child) { border-right: none; }
        .meta-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.08em; color: #888; font-weight: 700; margin-bottom: 6px; }
        .meta-value { font-size: 12px; color: #1a1a1a; line-height: 1.6; }
        .meta-value strong { color: #6E0F12; }

        /* Items table */
        .section-title { font-size: 13px; font-weight: 700; color: #6E0F12; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .items-table thead tr { background: #6E0F12; }
        .items-table thead th { padding: 10px 12px; color: #fff; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; text-align: left; }
        .items-table thead th:last-child { text-align: right; }
        .items-table thead th:nth-child(3),
        .items-table thead th:nth-child(4) { text-align: right; }
        .items-table tbody tr { border-bottom: 1px solid #f0ede6; }
        .items-table tbody tr:nth-child(even) { background: #FDFBF7; }
        .items-table tbody td { padding: 10px 12px; font-size: 12px; }
        .items-table tbody td:nth-child(3),
        .items-table tbody td:nth-child(4),
        .items-table tbody td:nth-child(5) { text-align: right; }

        /* Totals */
        .totals-wrapper { display: flex; justify-content: flex-end; margin-bottom: 28px; }
        .totals-table { width: 280px; }
        .totals-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 12px; border-bottom: 1px solid #f0ede6; }
        .totals-row.grand { border-top: 2px solid #6E0F12; border-bottom: none; margin-top: 4px; padding-top: 10px; font-size: 14px; font-weight: 700; color: #6E0F12; }
        .totals-row .label { color: #555; }
        .totals-row.discount .value { color: #28a745; }

        /* GST table */
        .gst-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .gst-table th { background: #f5f0e8; padding: 8px 12px; font-size: 10px; text-transform: uppercase; color: #666; text-align: left; border: 1px solid #E8DCCB; }
        .gst-table td { padding: 8px 12px; font-size: 11px; border: 1px solid #E8DCCB; }

        /* Footer */
        .footer { border-top: 2px solid #E8DCCB; padding-top: 16px; margin-top: 20px; text-align: center; color: #888; font-size: 10px; line-height: 1.8; }
        .footer strong { color: #6E0F12; }

        /* Status badge */
        .status-badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
        .status-paid { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        <div>
            <div class="brand-name">{{ $appName }}</div>
            <div class="brand-tagline">Crafted with love, adorned with gold</div>
        </div>
        <div class="invoice-title">
            <h2>Tax Invoice</h2>
            <div class="invoice-number">{{ $order->invoice_number }}</div>
            <div class="invoice-date">Date: {{ $invoiceDate }}</div>
        </div>
    </div>

    {{-- Billing + Shipping + Order Info --}}
    <div class="meta-grid">
        <div class="meta-box">
            <div class="meta-label">Bill To</div>
            <div class="meta-value">
                <strong>{{ $order->customer_name }}</strong><br>
                {{ $order->customer_email }}<br>
                {{ $order->customer_phone }}
            </div>
        </div>
        <div class="meta-box">
            <div class="meta-label">Ship To</div>
            <div class="meta-value">{{ $order->shipping_address }}</div>
        </div>
        <div class="meta-box">
            <div class="meta-label">Order Details</div>
            <div class="meta-value">
                <strong>Order #:</strong> {{ $order->order_number }}<br>
                <strong>Date:</strong> {{ $order->created_at->format('d M Y') }}<br>
                <strong>Status:</strong>
                <span class="status-badge {{ $order->payment_status === 'captured' ? 'status-paid' : 'status-pending' }}">
                    {{ $order->payment_status === 'captured' ? 'PAID' : strtoupper($order->payment_status) }}
                </span>
            </div>
        </div>
    </div>

    {{-- Items --}}
    <p class="section-title">Order Items</p>
    <table class="items-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>HSN</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->product_name }}</td>
                <td>7113</td>{{-- HSN code for gold/silver jewellery --}}
                <td>{{ $item->quantity }}</td>
                <td>₹{{ number_format($item->price, 2) }}</td>
                <td>₹{{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="totals-wrapper">
        <div class="totals-table">
            <div class="totals-row">
                <span class="label">Subtotal</span>
                <span class="value">₹{{ number_format($order->subtotal, 2) }}</span>
            </div>
            @if(($order->coupon_discount ?? 0) > 0)
            <div class="totals-row discount">
                <span class="label">Coupon ({{ $order->coupon_code }})</span>
                <span class="value">−₹{{ number_format($order->coupon_discount, 2) }}</span>
            </div>
            @endif
            @if($order->shipping > 0)
            <div class="totals-row">
                <span class="label">Shipping</span>
                <span class="value">₹{{ number_format($order->shipping, 2) }}</span>
            </div>
            @else
            <div class="totals-row">
                <span class="label">Shipping</span>
                <span class="value">FREE</span>
            </div>
            @endif
            <div class="totals-row grand">
                <span class="label">Grand Total</span>
                <span class="value">₹{{ number_format($order->total, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- GST Breakdown --}}
    <p class="section-title">GST Breakdown (18% inclusive)</p>
    <table class="gst-table">
        <thead>
            <tr>
                <th>Description</th>
                <th>HSN Code</th>
                <th>Taxable Amount</th>
                <th>CGST (9%)</th>
                <th>SGST (9%)</th>
                <th>Total GST</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Jewellery</td>
                <td>7113</td>
                <td>₹{{ number_format($gst['taxable'], 2) }}</td>
                <td>₹{{ number_format($gst['cgst'], 2) }}</td>
                <td>₹{{ number_format($gst['sgst'], 2) }}</td>
                <td>₹{{ number_format($gst['total'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        <p>This is a computer-generated invoice and does not require a signature.</p>
        <p><strong>{{ $appName }}</strong> · {{ config('app.url') }} · {{ config('mail.from.address') }}</p>
        <p>Thank you for shopping with us. For queries, contact us at {{ config('mail.from.address') }}</p>
    </div>

</div>
</body>
</html>
