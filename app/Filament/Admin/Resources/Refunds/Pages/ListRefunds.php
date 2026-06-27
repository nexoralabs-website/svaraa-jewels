<?php

namespace App\Filament\Admin\Resources\Refunds\Pages;

use App\Filament\Admin\Resources\Refunds\RefundResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListRefunds extends ListRecords
{
    protected static string $resource = RefundResource::class;

    public function getTabs(): array
    {
        return [
            'all'       => Tab::make('All'),
            'pending'   => Tab::make('Pending')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'pending'))
                ->badge(\App\Models\Refund::where('status', 'pending')->count()),
            'processed' => Tab::make('Processed')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'processed')),
            'rejected'  => Tab::make('Rejected')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'rejected')),
        ];
    }
}
