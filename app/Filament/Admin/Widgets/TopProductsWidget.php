<?php

namespace App\Filament\Admin\Widgets;

use App\Models\OrderItem;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopProductsWidget extends BaseWidget
{
    protected static ?string $heading = 'Top Selling Products';
    protected static ?int    $sort    = 4;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderItem::query()
                    ->reorder()
                    ->selectRaw('
                        product_id,
                        product_name,
                        SUM(quantity) as total_sold,
                        SUM(subtotal) as total_revenue,
                        COUNT(DISTINCT order_id) as order_count
                    ')
                    ->whereHas('order', function ($q) {
                        $q->where('payment_status', 'captured');
                    })
                    ->groupBy('product_id', 'product_name')
                    ->orderByDesc('total_sold')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('product_name')
                    ->label('Product')
                    ->searchable()
                    ->weight('medium'),

                TextColumn::make('total_sold')
                    ->label('Units Sold')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                TextColumn::make('order_count')
                    ->label('Orders')
                    ->sortable(),

                TextColumn::make('total_revenue')
                    ->label('Revenue')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => '₹' . number_format($state, 2))
                    ->color('warning'),
            ])
            ->paginated(false);
    }
}
