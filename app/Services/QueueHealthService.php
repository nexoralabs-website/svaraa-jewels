<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class QueueHealthService
{
    public function snapshot(): array
    {
        return [
            'pending_jobs' => DB::table('jobs')->count(),
            'email_jobs' => DB::table('jobs')->where('queue', 'emails')->count(),
            'default_jobs' => DB::table('jobs')->where('queue', 'default')->count(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
            'oldest_pending_at' => DB::table('jobs')->min('created_at'),
            'oldest_failed_at' => DB::table('failed_jobs')->min('failed_at'),
        ];
    }

    public function deadLetterCandidates(int $hours = 24): array
    {
        return DB::table('failed_jobs')
            ->where('failed_at', '<=', now()->subHours($hours))
            ->latest('failed_at')
            ->limit(100)
            ->get()
            ->all();
    }
}
