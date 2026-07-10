<?php

namespace App\Filament\Admin\Resources\Products\Schemas;

use App\Models\Category;
use App\Services\ProductDescriptionService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Log;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('₹')
                    ->step(0.01),

                TextInput::make('stock')
                    ->required()
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->default(0),

                Textarea::make('description')
                    ->nullable()
                    ->rows(4)
                    ->columnSpanFull()
                    ->hintAction(
                        Action::make('generateDescription')
                            ->label('✨ Generate Description')
                            ->icon('heroicon-m-sparkles')
                            ->action(function (array $arguments, $component, $get, $set) {
                                $name       = $get('name');
                                $categoryId = $get('category_id');

                                if (blank($name)) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Product name is required')
                                        ->body('Please enter a product name before generating a description.')
                                        ->warning()
                                        ->send();
                                    return;
                                }

                                $categoryName = 'default';
                                if ($categoryId) {
                                    $category = Category::find($categoryId);
                                    if ($category) {
                                        $categoryName = $category->name;
                                    }
                                }

                                $description = app(ProductDescriptionService::class)
                                    ->generate($name, $categoryName);

                                $set('description', $description);

                                \Filament\Notifications\Notification::make()
                                    ->title('Description generated')
                                    ->success()
                                    ->send();
                            })
                    ),

                FileUpload::make('thumbnail')
                    ->label('Product Image')
                    ->image()
                    ->disk(config('filesystems.disks.public.driver') === 's3' ? 'public' : 'public')
                    ->directory('products')
                    ->visibility('public')
                    ->fetchFileInformation(false)
                    ->imagePreviewHeight(120)
                    ->imageResizeMode('contain')
                    ->imageResizeTargetWidth(1200)
                    ->imageResizeTargetHeight(1200)
                    ->imageResizeUpscale(false)
                    ->openable()
                    ->downloadable()
                    ->saveUploadedFileUsing(function ($component, $file) {
                        Log::error('[File Upload Callback Start]', [
                            'file_class' => get_class($file),
                        ]);
                        $result = $component->saveUploadedFile($file);
                        Log::error('[File Upload Callback End]', [
                            'result' => $result,
                            'result_type' => gettype($result),
                        ]);
                        return $result;
                    }),

                Toggle::make('status')
                    ->label('Active')
                    ->default(true),

                Section::make('SEO')
                    ->description('Optional: override auto-generated meta tags for search engines')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->nullable()
                            ->maxLength(70)
                            ->placeholder('Auto-generated from product name if empty'),

                        Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->nullable()
                            ->maxLength(160)
                            ->rows(3)
                            ->placeholder('Auto-generated from description if empty'),

                        FileUpload::make('og_image')
                            ->label('OG Image (Social Share)')
                            ->image()
                            ->disk('public')
                            ->directory('og-images')
                            ->helperText('Recommended: 1200×630px. Falls back to product thumbnail if empty.'),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
