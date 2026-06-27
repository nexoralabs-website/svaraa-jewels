<?php

namespace App\Filament\Admin\Resources\Orders\Tables;

use App\Models\Order;
use App\Services\AdminActivityLogger;
use App\Services\PaymentRecoveryService;
use App\Services\PaymentService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order')
                    ->searchable(),
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable(),
                TextColumn::make('total')
                    ->money('INR')
                    ->sortable(),
                TextColumn::make('order_status')
                    ->badge()
                    ->color(fn ($state): string => match ((string) ($state?->value ?? $state)) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'processing' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'authorized' => 'info',
                        'captured' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('paid_at')
                    ->dateTime()
                    ->placeholder('Not paid')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('retryPayment')
                    ->label('Retry Payment')
                    ->visible(fn (Order $record): bool => $record->payment_method !== 'cod' && $record->payment_status === 'failed')
                    ->requiresConfirmation()
                    ->action(function (Order $record, AdminActivityLogger $activityLogger): void {
                        $record->update([
                            'payment_status' => 'pending',
                            'order_status' => \App\Enums\OrderStatus::PENDING,
                        ]);
                        $activityLogger->log('retry_payment', auth()->user(), $record);

                        Notification::make()
                            ->title('Payment marked for retry')
                            ->success()
                            ->send();
                    }),
                Action::make('retryReconciliation')
                    ->label('Retry Reconciliation')
                    ->requiresConfirmation()
                    ->action(function (Order $record, PaymentRecoveryService $recoveryService): void {
                        $recoveryService->retryReconciliation($record, auth()->user());

                        Notification::make()
                            ->title('Reconciliation retried')
                            ->success()
                            ->send();
                    }),
                Action::make('retryVerification')
                    ->label('Retry Verification')
                    ->requiresConfirmation()
                    ->action(function (Order $record, PaymentRecoveryService $recoveryService): void {
                        $recoveryService->retryPaymentVerification($record, auth()->user());

                        Notification::make()
                            ->title('Verification retried')
                            ->success()
                            ->send();
                    }),
                Action::make('retryEmail')
                    ->label('Retry Email')
                    ->requiresConfirmation()
                    ->action(function (Order $record, PaymentRecoveryService $recoveryService): void {
                        $recoveryService->retryEmailDispatch($record, auth()->user());

                        Notification::make()
                            ->title('Email dispatch retried')
                            ->success()
                            ->send();
                    }),
                Action::make('retryFinalization')
                    ->label('Retry Finalization')
                    ->requiresConfirmation()
                    ->action(function (Order $record, PaymentRecoveryService $recoveryService): void {
                        $recoveryService->retryOrderFinalization($record, auth()->user());

                        Notification::make()
                            ->title('Finalization retried')
                            ->success()
                            ->send();
                    }),
                Action::make('refund')
                    ->label('Refund')
                    ->visible(fn (Order $record): bool => $record->payment_status === 'captured')
                    ->requiresConfirmation()
                    ->action(function (Order $record, PaymentService $paymentService): void {
                        $paymentService->refundPayment($record);

                        Notification::make()
                            ->title('Refund requested')
                            ->success()
                            ->send();
                    }),
                Action::make('viewLogs')
                    ->label('View Logs')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(function (Order $record): HtmlString {
                        $logs = $record->paymentLogs()->latest()->limit(20)->get();

                        if ($logs->isEmpty()) {
                            return new HtmlString('<p class="text-sm text-gray-500">No payment logs yet.</p>');
                        }

                        $html = $logs->map(function ($log): string {
                            return sprintf(
                                '<div class="border-b border-gray-200 py-3"><div class="text-sm font-medium">%s · %s · %s</div><pre class="mt-2 overflow-auto rounded bg-gray-50 p-2 text-xs">%s</pre></div>',
                                e($log->created_at->format('M d, Y H:i:s')),
                                e($log->action),
                                e($log->status),
                                e(json_encode(['request' => $log->request, 'response' => $log->response], JSON_PRETTY_PRINT))
                            );
                        })->implode('');

                        return new HtmlString($html);
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
