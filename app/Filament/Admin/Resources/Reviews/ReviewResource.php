<?php

namespace App\Filament\Admin\Resources\Reviews;

use App\Filament\Admin\Resources\Reviews\Pages\ListReviews;
use App\Models\Review;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;
    protected static ?string $navigationLabel = 'Product Reviews';
    protected static ?int    $navigationSort  = 10;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-star';
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return 'Catalogue';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('rating')
                    ->label('Rating')
                    ->badge()
                    ->formatStateUsing(fn ($state) => str_repeat('★', (int)$state) . str_repeat('☆', 5 - (int)$state) . " ({$state}/5)")
                    ->color(fn ($state) => match(true) {
                        $state >= 4 => 'success',
                        $state == 3 => 'warning',
                        default     => 'danger',
                    }),

                TextColumn::make('title')
                    ->label('Title')
                    ->limit(40)
                    ->placeholder('—'),

                TextColumn::make('review')
                    ->label('Review')
                    ->limit(80)
                    ->placeholder('No review text')
                    ->wrap(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default    => 'warning',
                    }),

                IconColumn::make('is_verified_purchase')
                    ->label('Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle'),

                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending'  => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                SelectFilter::make('rating')
                    ->options([
                        '5' => '5 Stars',
                        '4' => '4 Stars',
                        '3' => '3 Stars',
                        '2' => '2 Stars',
                        '1' => '1 Star',
                    ]),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Review $record) => $record->status !== 'approved')
                    ->requiresConfirmation()
                    ->action(function (Review $record) {
                        $record->update(['status' => 'approved']);
                        Notification::make()->title('Review approved')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Review $record) => $record->status !== 'rejected')
                    ->requiresConfirmation()
                    ->action(function (Review $record) {
                        $record->update(['status' => 'rejected']);
                        Notification::make()->title('Review rejected')->danger()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approve_selected')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => 'approved'])),

                    BulkAction::make('reject_selected')
                        ->label('Reject Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => 'rejected'])),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReviews::route('/'),
        ];
    }
}
