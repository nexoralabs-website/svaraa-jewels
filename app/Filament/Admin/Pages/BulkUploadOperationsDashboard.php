<?php

namespace App\Filament\Admin\Pages;

use App\Models\UploadBatch;
use App\Services\BulkUploadAnalyticsService;
use App\Services\BulkUploadHealthService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;

class BulkUploadOperationsDashboard extends Page
{
    protected static ?string $navigationLabel = 'Operations Dashboard';
    protected static ?int $navigationSort = 3;
    protected static ?string $title = 'Bulk Upload Operations Dashboard';
    protected string $view = 'filament.admin.pages.bulk-upload-operations-dashboard';

    public static function canAccess(): bool
    {
        return Gate::allows('dashboardAccess', UploadBatch::class);
    }

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-cpu-chip';
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return 'Products';
    }
}
