<?php

namespace App\Filament\Admin\Resources\Products\Pages;

use App\Filament\Admin\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        Log::error('[Stage 3]', [
            'thumbnail' => $data['thumbnail'] ?? null,
            'thumbnail_type' => gettype($data['thumbnail'] ?? null),
            'data_keys' => array_keys($data),
        ]);
        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        Log::error('[Stage 4]', [
            'thumbnail' => $data['thumbnail'] ?? null,
            'thumbnail_type' => gettype($data['thumbnail'] ?? null),
            'data_keys' => array_keys($data),
        ]);
        $record = parent::handleRecordCreation($data);
        Log::error('[Stage 4 Result]', [
            'thumbnail' => $record->thumbnail ?? null,
            'thumbnail_type' => gettype($record->thumbnail ?? null),
        ]);
        return $record;
    }
}
