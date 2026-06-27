<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Alert | {{ config('app.name') }}</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        .container { max-width: 640px; margin: 40px auto; padding: 0 20px; }
        .card { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); overflow: hidden; }
        .header { background: linear-gradient(135deg, #6E0F12, #520b0d); padding: 28px 32px; }
        .header h1 { color: #C8A35D; font-size: 22px; margin: 0; }
        .header p { color: rgba(255,255,255,0.8); font-size: 14px; margin: 6px 0 0; }
        .body { padding: 28px 32px; }
        .section-title { font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #6E0F12; margin: 0 0 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 28px; font-size: 13px; }
        th { text-align: left; padding: 8px 10px; background: #f5f0e8; color: #555; font-weight: 600; }
        td { padding: 10px; border-bottom: 1px solid #f0ede6; }
        .badge-danger { background: #f8d7da; color: #721c24; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .badge-warning { background: #fff3cd; color: #856404; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .footer { text-align: center; padding: 20px; background: #fafafa; border-top: 1px solid #eee; font-size: 12px; color: #aaa; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="header">
            <h1>⚠️ Inventory Alert</h1>
            <p>{{ config('app.name') }} — {{ now()->format('d M Y, h:i A') }}</p>
        </div>
        <div class="body">

            @if($outOfStock->isNotEmpty())
            <p class="section-title">Out of Stock ({{ $outOfStock->count() }})</p>
            <table>
                <thead><tr><th>Product</th><th>Category</th><th>Stock</th></tr></thead>
                <tbody>
                @foreach($outOfStock as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category?->name ?? '—' }}</td>
                    <td><span class="badge-danger">0</span></td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @endif

            @if($lowStock->isNotEmpty())
            <p class="section-title">Low Stock ({{ $lowStock->count() }})</p>
            <table>
                <thead><tr><th>Product</th><th>Category</th><th>Stock</th></tr></thead>
                <tbody>
                @foreach($lowStock as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category?->name ?? '—' }}</td>
                    <td><span class="badge-warning">{{ $product->stock }}</span></td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @endif

        </div>
        <div class="footer">
            {{ config('app.name') }} Admin · Please restock the above products to avoid order failures.
        </div>
    </div>
</div>
</body>
</html>
