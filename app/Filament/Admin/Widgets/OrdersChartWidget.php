<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrdersChartWidget extends ChartWidget
{
    protected ?string $heading = 'Orders — Last 30 Days';
    protected static ?int $sort = 3;
    protected string $color = 'primary';

    protected function getData(): array
    {
        $labels = [];
        $counts = [];

        for ($i = 29; $i >= 0; $i--) {
            $day      = now()->subDays($i);
            $labels[] = $day->format('d M');
            $counts[] = Order::whereDate('created_at', $day->toDateString())->count();
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Orders',
                    'data'            => $counts,
                    'backgroundColor' => 'rgba(110,15,18,0.7)',
                    'borderColor'     => '#6E0F12',
                    'borderWidth'     => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
