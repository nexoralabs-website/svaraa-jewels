<?php

namespace App\Filament\Admin\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (string $state, callable $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                FileUpload::make('image')
                    ->image()
                    ->disk('public'),
                Toggle::make('status')
                    ->default(true),

                Section::make('SEO')
                    ->description('Optional: override auto-generated meta tags')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->nullable()
                            ->maxLength(70)
                            ->placeholder('Auto-generated from name if left empty'),
                        Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->nullable()
                            ->maxLength(160)
                            ->rows(3)
                            ->placeholder('Auto-generated if left empty'),
                    ]),
            ]);
    }
}
