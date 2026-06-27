<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $todayRevenue   = Order::where('payment_status', 'captured')
            ->whereDate('paid_at', today())
            ->sum('total');

        $monthRevenue   = Order::where('payment_status', 'captured')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('total');

        $todayOrders    = Order::whereDate('created_at', today())->count();

        $totalCustomers = User::count();

        $avgOrderValue  = Order::where('payment_status', 'captured')->avg('total') ?? 0;

        $failedPayments = Order::where('payment_status', 'failed')
            ->whereMonth('created_at', now()->month)
            ->count();

        $refundCount    = Order::where('payment_status', 'refunded')
            ->whereMonth('updated_at', now()->month)
            ->count();

        $pendingOrders  = Order::where('payment_status', 'pending')->count();

        return [
            Stat::make("Today's Revenue", '₹' . number_format($todayRevenue, 2))
                ->description('Captured payments today')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('Monthly Revenue', '₹' . number_format($monthRevenue, 2))
                ->description(now()->format('F Y'))
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('success'),

            Stat::make("Today's Orders", $todayOrders)
                ->description('Orders placed today')
                ->descriptionIcon('heroicon-o-shopping-bag')
                ->color('primary'),

            Stat::make('Total Customers', number_format($totalCustomers))
                ->description('Registered users')
                ->descriptionIcon('heroicon-o-users')
                ->color('info'),

            Stat::make('Avg Order Value', '₹' . number_format($avgOrderValue, 2))
                ->description('Per captured order')
                ->descriptionIcon('heroicon-o-receipt-percent')
                ->color('warning'),

            Stat::make('Failed Payments', $failedPayments)
                ->description('This month')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color($failedPayments > 5 ? 'danger' : 'warning'),

            Stat::make('Refunds', $refundCount)
                ->description('This month')
                ->descriptionIcon('heroicon-o-arrow-uturn-left')
                ->color($refundCount > 3 ? 'danger' : 'info'),

            Stat::make('Pending Payments', $pendingOrders)
                ->description('Awaiting confirmation')
                ->descriptionIcon('heroicon-o-clock')
                ->color($pendingOrders > 10 ? 'danger' : 'warning'),
        ];
    }
}
