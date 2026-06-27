<?php

namespace App\Filament\Admin\Pages;

use App\Models\AdminActivityLog;
use App\Models\PaymentLog;
use Filament\Pages\Page;

class PaymentAudit extends Page
{
    protected string $view = 'filament.admin.pages.payment-audit';

    protected static ?string $navigationLabel = 'Payment Audit';

    protected static ?string $title = 'Payment Audit';

    protected static ?int $navigationSort = 2;

    protected function getViewData(): array
    {
        return [
            'paymentLogs' => PaymentLog::with('order', 'actor')->latest()->limit(100)->get(),
            'activityLogs' => AdminActivityLog::with('order', 'actor')->latest('acted_at')->limit(50)->get(),
        ];
    }
}
