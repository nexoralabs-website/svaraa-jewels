<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Category;
use App\Models\OrderItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CategoryPerformanceWidget extends ChartWidget
{
    protected ?string $heading = 'Category Performance';
    protected static ?int $sort = 5;

    protected function getData(): array
    {
        $data = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.payment_status', 'captured')
            ->selectRaw('categories.name, SUM(order_items.subtotal) as revenue')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->limit(6)
            ->get();

        $colors = ['#6E0F12', '#C8A35D', '#8B2252', '#D4975A', '#A52B4E', '#E8C07D'];

        return [
            'datasets' => [
                [
                    'data'            => $data->pluck('revenue')->map(fn ($v) => round($v, 2))->toArray(),
                    'backgroundColor' => array_slice($colors, 0, $data->count()),
                ],
            ],
            'labels' => $data->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
