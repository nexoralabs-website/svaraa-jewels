<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChartWidget extends ChartWidget
{
    protected ?string $heading = 'Revenue — Last 12 Months';
    protected static ?int $sort = 2;
    protected string $color = 'warning';

    protected function getData(): array
    {
        $labels   = [];
        $revenues = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[]   = $month->format('M Y');
            $revenues[] = (float) Order::where('payment_status', 'captured')
                ->whereYear('paid_at', $month->year)
                ->whereMonth('paid_at', $month->month)
                ->sum('total');
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Revenue (₹)',
                    'data'            => $revenues,
                    'borderColor'     => '#C8A35D',
                    'backgroundColor' => 'rgba(200,163,93,0.15)',
                    'tension'         => 0.4,
                    'fill'            => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
