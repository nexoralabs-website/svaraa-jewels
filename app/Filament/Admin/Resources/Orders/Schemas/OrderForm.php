<?php

namespace App\Filament\Admin\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name'),
                TextInput::make('order_number')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->prefix('INR')
                    ->step(0.01),
                TextInput::make('order_status')
                    ->required()
                    ->default('pending'),
                TextInput::make('payment_status')
                    ->required()
                    ->default('pending'),
            ]);
    }
}
