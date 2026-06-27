<?php

namespace App\Filament\Admin\Pages;

use App\Services\PaymentHealthService;
use Filament\Pages\Page;

class OperationsDashboard extends Page
{
    protected string $view = 'filament.admin.pages.operations-dashboard';

    protected static ?string $navigationLabel = 'Operations';

    protected static ?string $title = 'Operations Dashboard';

    protected static ?int $navigationSort = 1;

    public ?string $from = null;

    public ?string $to = null;

    public ?string $paymentStatus = null;

    public ?string $orderStatus = null;

    public function mount(): void
    {
        $this->from ??= today()->toDateString();
        $this->to ??= today()->toDateString();
    }

    protected function getViewData(): array
    {
        $filters = [
            'from' => $this->from,
            'to' => $this->to,
            'payment_status' => $this->paymentStatus,
            'order_status' => $this->orderStatus,
        ];

        return [
            'metrics' => app(PaymentHealthService::class)->dashboardMetrics($filters),
            'filters' => $filters,
        ];
    }
}
