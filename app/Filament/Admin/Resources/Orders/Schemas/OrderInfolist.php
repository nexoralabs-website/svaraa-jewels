<?php

namespace App\Filament\Admin\Resources\Orders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('User'),
                TextEntry::make('order_number'),
                TextEntry::make('customer_name'),
                TextEntry::make('total')
                    ->money('INR'),
                TextEntry::make('order_status'),
                TextEntry::make('payment_status'),
                TextEntry::make('paid_at')
                    ->dateTime()
                    ->placeholder('Not paid'),
                TextEntry::make('razorpay_payment_id')
                    ->placeholder('N/A'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
