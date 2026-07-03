<?php

namespace App\Filament\Admin\Widgets;

use App\Models\OrderItem;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TopProductsWidget extends BaseWidget
{
    protected static ?string $heading = 'Top Selling Products';
    protected static ?int    $sort    = 4;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Wrap in a Closure so Filament receives a ?Closure, not a Builder instance.
                // ->reorder() clears any implicit orderings added by Eloquent scopes or
                // relations, giving us a clean slate before our own orderByDesc().
                fn (): Builder => OrderItem::query()
                    ->reorder()
                    ->selectRaw('
                        product_id,
                        product_name,
                        SUM(quantity)            AS total_sold,
                        SUM(subtotal)            AS total_revenue,
                        COUNT(DISTINCT order_id) AS order_count
                    ')
                    ->whereHas('order', fn (Builder $q) =>
                        $q->where('payment_status', 'captured')
                    )
                    ->groupBy('product_id', 'product_name')
                    ->orderByDesc('total_sold')
            )
            // Disable Filament's "default key sort" feature.
            //
            // By default Filament appends ->orderBy('order_items.id') to every query
            // so that pagination is stable. On a GROUP BY query this breaks PostgreSQL
            // because `order_items.id` is neither in the GROUP BY clause nor wrapped
            // in an aggregate — PostgreSQL requires every ORDER BY column to appear in
            // GROUP BY or be an aggregate function when the query uses GROUP BY.
            //
            // ->defaultKeySort(false) sets $hasDefaultKeySort = false in
            // CanSortRecords, so the key-sort code-path is skipped entirely and
            // our orderByDesc('total_sold') is the only ordering applied.
            ->defaultKeySort(false)
            ->columns([
                TextColumn::make('product_name')
                    ->label('Product')
                    ->searchable()
                    ->weight('medium'),

                TextColumn::make('total_sold')
                    ->label('Units Sold')
                    ->badge()
                    ->color('success'),

                TextColumn::make('order_count')
                    ->label('Orders'),

                TextColumn::make('total_revenue')
                    ->label('Revenue')
                    ->formatStateUsing(fn ($state) => '₹' . number_format($state, 2))
                    ->color('warning'),
            ])
            ->paginated(false);
    }
}
