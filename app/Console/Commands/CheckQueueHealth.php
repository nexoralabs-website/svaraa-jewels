<?php

namespace App\Console\Commands;

use App\Services\PaymentAlertService;
use App\Services\QueueHealthService;
use Illuminate\Console\Command;

class CheckQueueHealth extends Command
{
    protected $signature = 'queue:health-check';

    protected $description = 'Report queue backlog, failed jobs, and dead-letter candidates.';

    public function handle(QueueHealthService $queueHealthService, PaymentAlertService $paymentAlertService): int
    {
        $snapshot = $queueHealthService->snapshot();
        $deadLetters = $queueHealthService->deadLetterCandidates();

        $paymentAlertService->check();

        $this->info(sprintf(
            'Queue health: pending=%d, emails=%d, failed=%d, dead_letter_candidates=%d',
            $snapshot['pending_jobs'],
            $snapshot['email_jobs'],
            $snapshot['failed_jobs'],
            count($deadLetters)
        ));

        return self::SUCCESS;
    }
}
