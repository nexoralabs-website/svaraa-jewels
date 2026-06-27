<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Product;
use App\Services\InventoryService;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockWidget extends BaseWidget
{
    protected static ?string $heading = '⚠️ Low Stock & Out-of-Stock Products';
    protected static ?int    $sort    = 6;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::with('category')
                    ->where('stock', '<=', InventoryService::LOW_STOCK_THRESHOLD)
                    ->orderBy('stock')
            )
            ->columns([
                ImageColumn::make('thumbnail')
                    ->disk('public')
                    ->size(40)
                    ->defaultImageUrl(asset('images/placeholder.jpg')),

                TextColumn::make('name')
                    ->searchable()
                    ->weight('medium'),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge(),

                TextColumn::make('stock')
                    ->label('Stock')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state === 0 => 'danger',
                        $state <= 2  => 'danger',
                        $state <= 5  => 'warning',
                        default      => 'success',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Disabled')
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
            ])
            ->actions([
                \Filament\Actions\Action::make('edit')
                    ->url(fn (Product $record) => route('filament.admin.resources.products.edit', $record))
                    ->icon('heroicon-o-pencil')
                    ->size('sm'),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(10)
            ->emptyStateHeading('All products are well-stocked!')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->emptyStateDescription('No products are low on stock right now.');
    }
}
