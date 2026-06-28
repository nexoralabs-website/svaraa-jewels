<?php

namespace App\Services;

use App\Models\BulkUploadJobLog;
use App\Models\BulkUploadPreview;
use App\Models\UploadBatch;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BulkUploadAnalyticsService
{
    const CACHE_TTL = 60; // seconds

    public function collectBatchMetrics(): array
    {
        return Cache::remember('bulk_upload:batch_metrics', self::CACHE_TTL, function () {
            $today = now()->startOfDay();

            $activeBatches = UploadBatch::whereNotIn('status', ['completed', 'failed', 'abandoned'])->count();
            $failedBatches = UploadBatch::where('status', 'failed')->where('created_at', '>=', $today)->count();

            $avgExtractionDuration = BulkUploadJobLog::where('job_name', 'like', '%ProcessPdf%')
                ->where('created_at', '>=', $today)
                ->avg('duration_ms') ?? 0;

            $avgPublishDuration = BulkUploadJobLog::where('job_name', 'like', '%PublishBulk%')
                ->where('created_at', '>=', $today)
                ->avg('duration_ms') ?? 0;

            $totalPublishedToday = BulkUploadPreview::where('status', 'published')
                ->where('published_at', '>=', $today)
                ->count();

            $totalProcessedToday = BulkUploadPreview::where('created_at', '>=', $today)->count();
            $publishSuccessRate = $totalProcessedToday > 0 ? ($totalPublishedToday / $totalProcessedToday) * 100 : 0;

            return [
                'active_batches' => $activeBatches,
                'failed_batches' => $failedBatches,
                'avg_extraction_ms' => $avgExtractionDuration,
                'avg_publish_ms' => $avgPublishDuration,
                'published_today' => $totalPublishedToday,
                'publish_success_percent' => round($publishSuccessRate, 2),
            ];
        });
    }

    public function collectQueueMetrics(): array
    {
        return Cache::remember('bulk_upload:queue_metrics', self::CACHE_TTL, function () {
            $queueDepth = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')->where('failed_at', '>=', now()->subHours(24))->count();
            $pendingJobs = DB::table('jobs')->where('created_at', '<=', now()->subMinutes(5))->count();
            $queueLag = $pendingJobs > 0 ? 'High' : 'Normal';

            return [
                'queue_depth' => $queueDepth,
                'failed_jobs' => $failedJobs,
                'queue_lag' => $queueLag,
                'pending_jobs' => $pendingJobs,
            ];
        });
    }

    public function collectPublishMetrics(): array
    {
        return Cache::remember('bulk_upload:publish_metrics', self::CACHE_TTL, function () {
            $last7Days = now()->subDays(7);
            $publishedLast7Days = BulkUploadPreview::where('status', 'published')
                ->where('published_at', '>=', $last7Days)
                ->count();

            $throughput = $publishedLast7Days / 7; // avg per day

            return [
                'published_last_7_days' => $publishedLast7Days,
                'daily_throughput_avg' => round($throughput, 2),
            ];
        });
    }

    public function collectStorageMetrics(): array
    {
        return Cache::remember('bulk_upload:storage_metrics', self::CACHE_TTL, function () {
            $totalStorage = 0;
            $files = Storage::disk('public')->allFiles('pdf-uploads');
            foreach ($files as $file) {
                $totalStorage += Storage::disk('public')->size($file);
            }

            $totalMb = round($totalStorage / (1024 * 1024), 2);

            return [
                'storage_used_mb' => $totalMb,
                'total_files' => count($files),
            ];
        });
    }

    public function collectHealthSnapshot(): array
    {
        $healthService = app(BulkUploadHealthService::class);
        $healthResult = $healthService->check();
        $batchMetrics = $this->collectBatchMetrics();
        $queueMetrics = $this->collectQueueMetrics();

        $lastCleanup = DB::table('bulk_upload_job_logs')
            ->where('job_name', 'like', '%Cleanup%')
            ->latest('ended_at')
            ->value('ended_at');

        return [
            'health_score' => $healthResult['score'],
            'avg_processing_ms' => $batchMetrics['avg_extraction_ms'],
            'avg_memory_mb' => BulkUploadJobLog::avg('memory_mb') ?? 0,
            'queue_depth' => $queueMetrics['queue_depth'],
            'failed_jobs' => $queueMetrics['failed_jobs'],
            'published_today' => $batchMetrics['published_today'],
            'storage_mb' => $this->collectStorageMetrics()['storage_used_mb'],
            'last_cleanup_execution' => $lastCleanup,
        ];
    }
}
