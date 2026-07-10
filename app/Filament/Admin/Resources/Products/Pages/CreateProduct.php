<?php

namespace App\Filament\Admin\Resources\Products\Pages;

use App\Filament\Admin\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function afterFill(): void
    {
        Log::error('[Stage 1]', [
            'thumbnail' => $this->data['thumbnail'] ?? null,
            'thumbnail_type' => gettype($this->data['thumbnail'] ?? null),
            'data_keys' => array_keys($this->data),
        ]);
    }

    protected function beforeValidate(): void
    {
        Log::error('[Stage 2]', [
            'thumbnail' => $this->data['thumbnail'] ?? null,
            'thumbnail_type' => gettype($this->data['thumbnail'] ?? null),
            'data_keys' => array_keys($this->data),
        ]);
    }

    protected function afterValidate(): void
    {
        Log::error('[Stage after saveUploadedFiles]', [
            'thumbnail' => $this->data['thumbnail'] ?? null,
            'thumbnail_type' => gettype($this->data['thumbnail'] ?? null),
            'data_keys' => array_keys($this->data),
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        Log::error('[Stage 3]', [
            'thumbnail' => $data['thumbnail'] ?? null,
            'thumbnail_type' => gettype($data['thumbnail'] ?? null),
            'data_keys' => array_keys($data),
        ]);

        return $data;
    }

    protected function beforeCreate(): void
    {
        Log::error('[Stage 4]', [
            'thumbnail' => $this->data['thumbnail'] ?? null,
            'thumbnail_type' => gettype($this->data['thumbnail'] ?? null),
            'data_keys' => array_keys($this->data),
        ]);
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $record = parent::handleRecordCreation($data);

        Log::error('[Stage 4 Result]', [
            'thumbnail' => $record->thumbnail ?? null,
            'thumbnail_type' => gettype($record->thumbnail ?? null),
        ]);

        return $record;
    }

    protected function afterCreate(): void
    {
        Log::error('[Stage after model save]', [
            'thumbnail' => $this->record->thumbnail ?? null,
            'thumbnail_type' => gettype($this->record->thumbnail ?? null),
            'record_keys' => array_keys($this->record->attributesToArray()),
        ]);
    }
}
