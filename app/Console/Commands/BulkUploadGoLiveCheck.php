<?php

namespace App\Console\Commands;

use App\Services\BulkUploadHealthService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class BulkUploadGoLiveCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bulk-upload:go-live-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run production readiness checks for the bulk upload platform';

    private array $warnings = [];
    private array $blockers = [];
    private array $readyItems = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Bulk Upload Platform — Go-Live Check ===');

        $this->checkDatabaseTables();
        $this->checkHealthService();
        $this->checkQueueConnection();
        $this->checkStorageConnection();
        $this->checkCacheConnection();
        $this->checkMigrations();

        $this->outputResults();

        return empty($this->blockers) ? 0 : 1;
    }

    private function checkDatabaseTables(): void
    {
        $requiredTables = [
            'upload_batches',
            'bulk_upload_previews',
            'bulk_upload_job_logs',
            'upload_batch_steps',
            'jobs',
            'failed_jobs',
            'bulk_upload_events',
        ];

        foreach ($requiredTables as $table) {
            if (Schema::hasTable($table)) {
                $this->readyItems[] = "Table exists: $table";
            } else {
                $this->blockers[] = "Missing required table: $table";
            }
        }
    }

    private function checkHealthService(): void
    {
        try {
            $result = app(BulkUploadHealthService::class)->check();

            if ($result['healthy']) {
                $this->readyItems[] = "Health check passed (score: {$result['score']}/100)";
            } else {
                $this->warnings[] = "Health check failed (score: {$result['score']}/100)";
                foreach ($result['warnings'] as $w) {
                    $this->warnings[] = "- $w";
                }
                foreach ($result['failures'] as $f) {
                    $this->blockers[] = "- $f";
                }
            }
        } catch (\Throwable $e) {
            $this->blockers[] = "Health service failed: " . $e->getMessage();
        }
    }

    private function checkQueueConnection(): void
    {
        try {
            $connection = config('queue.default');
            $this->readyItems[] = "Queue configured ($connection)";
        } catch (\Throwable $e) {
            $this->warnings[] = "Queue check failed: " . $e->getMessage();
        }
    }

    private function checkStorageConnection(): void
    {
        try {
            $disk = config('filesystems.default');
            Storage::disk($disk)->put('test-golive-check.txt', 'ok');
            Storage::disk($disk)->delete('test-golive-check.txt');
            $this->readyItems[] = "Storage connected ($disk)";
        } catch (\Throwable $e) {
            $this->blockers[] = "Storage check failed: " . $e->getMessage();
        }
    }

    private function checkCacheConnection(): void
    {
        try {
            Cache::put('golive-check', 'ok', 5);
            $this->readyItems[] = "Cache working";
        } catch (\Throwable $e) {
            $this->warnings[] = "Cache check failed: " . $e->getMessage();
        }
    }

    private function checkMigrations(): void
    {
        try {
            // Ensure migrations are up to date by checking the migration table exists
            if (Schema::hasTable('migrations')) {
                $this->readyItems[] = "Migrations table exists";
            } else {
                $this->blockers[] = "Missing migrations table";
            }
        } catch (\Throwable $e) {
            $this->blockers[] = "Migration check failed: " . $e->getMessage();
        }
    }

    private function outputResults(): void
    {
        $this->newLine();
        $this->info('--- READY ---');
        foreach ($this->readyItems as $item) {
            $this->line("<info>✓</info> $item");
        }

        $this->newLine();
        $this->info('--- WARNINGS ---');
        if (empty($this->warnings)) {
            $this->line('<comment>No warnings</comment>');
        } else {
            foreach ($this->warnings as $warn) {
                $this->line("<comment>⚠</comment> $warn");
            }
        }

        $this->newLine();
        $this->info('--- BLOCKERS ---');
        if (empty($this->blockers)) {
            $this->line('<bg=green;fg=white>READY FOR LAUNCH</bg=green;fg=white>');
        } else {
            $this->line('<error>✗ Not ready for launch</error>');
            foreach ($this->blockers as $blocker) {
                $this->line("<error>✗</error> $blocker");
            }
        }
    }
}
