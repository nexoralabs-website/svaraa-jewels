<?php

namespace App\Filament\Admin\Resources\Refunds;

use App\Filament\Admin\Resources\Refunds\Pages\ListRefunds;
use App\Models\Refund;
use App\Services\RefundService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;

class RefundResource extends Resource
{
    protected static ?string $model = Refund::class;
    protected static ?string $navigationLabel = 'Refund Requests';
    protected static ?int    $navigationSort  = 5;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-arrow-uturn-left';
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return 'Orders';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.order_number')
                    ->label('Order')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->url(fn (Refund $record) => route('filament.admin.resources.orders.view', $record->order_id)),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable(),

                TextColumn::make('amount')
                    ->label('Refund Amount')
                    ->formatStateUsing(fn ($state) => '₹' . number_format($state, 2))
                    ->sortable(),

                TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(60)
                    ->wrap(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'processed' => 'success',
                        'rejected'  => 'danger',
                        default     => 'warning',
                    }),

                TextColumn::make('razorpay_refund_id')
                    ->label('Razorpay Ref')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('requested_at')
                    ->label('Requested')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('processed_at')
                    ->label('Processed')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->defaultSort('requested_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending'   => 'Pending',
                        'processed' => 'Processed',
                        'rejected'  => 'Rejected',
                    ]),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve & Refund')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Refund $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalDescription('This will trigger a Razorpay refund, restore stock, and mark the order as refunded.')
                    ->action(function (Refund $record) {
                        try {
                            app(RefundService::class)->approveAndProcess($record);
                            Notification::make()
                                ->title('Refund processed successfully')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Log::error('Filament refund approval failed', [
                                'refund_id' => $record->id,
                                'error'     => $e->getMessage(),
                            ]);
                            Notification::make()
                                ->title('Refund failed: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Refund $record) => $record->status === 'pending')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('admin_notes')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Refund $record, array $data) {
                        app(RefundService::class)->rejectRefund($record, $data['admin_notes']);
                        Notification::make()->title('Refund rejected')->warning()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRefunds::route('/'),
        ];
    }
}
