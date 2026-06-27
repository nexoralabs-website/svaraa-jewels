<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'backup:run {--tables=orders,payments,payment_events,payment_logs,admin_activity_logs}';

    protected $description = 'Create backup of critical payment tables.';

    public function handle(): int
    {
        $tables = explode(',', $this->option('tables'));
        $timestamp = now()->format('Ymd_His');
        $backupDir = storage_path('app/backups');

        if (!Storage::isDirectory('backups')) {
            Storage::makeDirectory('backups');
        }

        $this->info('Starting database backup...');

        foreach ($tables as $table) {
            $filename = "{$table}_{$timestamp}.json";

            $data = DB::table($table)->get();
            Storage::put("backups/{$filename}", json_encode($data, JSON_PRETTY_PRINT));

            $this->info("Backed up: {$table}");
        }

        $this->info('Backup completed.');

        return self::SUCCESS;
    }
}