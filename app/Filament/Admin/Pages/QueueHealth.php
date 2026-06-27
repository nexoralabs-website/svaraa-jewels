<?php

namespace App\Filament\Admin\Pages;

use App\Services\QueueHealthService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class QueueHealth extends Page
{
    protected string $view = 'filament.admin.pages.queue-health';

    protected static ?string $navigationLabel = 'Queue Health';

    protected static ?string $title = 'Queue Health';

    protected static ?int $navigationSort = 3;

    protected function getViewData(): array
    {
        $queueHealth = app(QueueHealthService::class);

        return [
            'snapshot' => $queueHealth->snapshot(),
            'failedJobs' => DB::table('failed_jobs')->latest('failed_at')->limit(50)->get(),
            'deadLetters' => $queueHealth->deadLetterCandidates(),
        ];
    }
}
