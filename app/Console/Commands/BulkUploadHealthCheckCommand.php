<?php

namespace App\Console\Commands;

use App\Services\BulkUploadHealthService;
use App\Services\BulkUploadMetricsService;
use Illuminate\Console\Command;

/**
 * bulk-upload:health
 *
 * Prints a health status table and exits with non-zero code when unhealthy.
 *
 * Examples:
 *   php artisan bulk-upload:health
 *   php artisan bulk-upload:health --json
 *
 * Exit codes:
 *   0 = healthy (score >= 80)
 *   1 = warning (score 50–79)
 *   2 = unhealthy (score < 50)
 */
class BulkUploadHealthCheckCommand extends Command
{
    protected $signature = 'bulk-upload:health
                            {--json : Output as JSON instead of table}';

    protected $description = 'Check the health of the bulk upload pipeline and print a status report.';

    public function handle(
        BulkUploadHealthService  $health,
        BulkUploadMetricsService $metrics,
    ): int {
        $result    = $health->check();
        $dashboard = $metrics->dashboard();

        if ($this->option('json')) {
            $this->line(json_encode(array_merge($result, $dashboard), JSON_PRETTY_PRINT));
            return $this->exitCode($result['score']);
        }

        // ── Health score header ───────────────────────────────────────────
        $scoreLabel = $result['score'] >= 80 ? '✅ HEALTHY'
                    : ($result['score'] >= 50 ? '⚠️  WARNING'
                    : '🔴 UNHEALTHY');

        $this->newLine();
        $this->line("  Bulk Upload Health Check");
        $this->line("  Score: {$result['score']}/100  —  {$scoreLabel}");
        $this->newLine();

        // ── Failures ─────────────────────────────────────────────────────
        if (! empty($result['failures'])) {
            $this->error('  FAILURES:');
            foreach ($result['failures'] as $f) {
                $this->line("    ✗ {$f}");
            }
            $this->newLine();
        }

        // ── Warnings ─────────────────────────────────────────────────────
        if (! empty($result['warnings'])) {
            $this->warn('  WARNINGS:');
            foreach ($result['warnings'] as $w) {
                $this->line("    ! {$w}");
            }
            $this->newLine();
        }

        if (empty($result['failures']) && empty($result['warnings'])) {
            $this->info('  All checks passed.');
            $this->newLine();
        }

        // ── Dashboard metrics table ───────────────────────────────────────
        $this->table(
            ['Metric', 'Value'],
            [
                ['Today uploads',          $dashboard['today_uploads']],
                ['Today success %',        $dashboard['today_success_pct'] . '%'],
                ['Published today',        $dashboard['total_published_today']],
                ['Active batches',         $dashboard['active_batches']],
                ['Avg pages/batch',        $dashboard['avg_pages']],
                ['Queue load',             $dashboard['queue_load'] . ' pending jobs'],
                ['Retry rate',             round($dashboard['retry_rate'] * 100, 1) . '%'],
                ['Health score',           $dashboard['health_score'] . '/100'],
            ]
        );

        $this->newLine();

        return $this->exitCode($result['score']);
    }

    private function exitCode(int $score): int
    {
        if ($score >= 80) {
            return self::SUCCESS;
        }
        if ($score >= 50) {
            return 1; // warning
        }
        return 2; // unhealthy
    }
}
