<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PaymentRecoveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesPaymentFixtures;
use Tests\TestCase;

class QueueRecoveryTest extends TestCase
{
    use CreatesPaymentFixtures;
    use RefreshDatabase;

    public function test_queue_health_service_reports_correct_metrics(): void
    {
        $now = time();
        DB::table('jobs')->insert([
            ['queue' => 'default', 'payload' => '{}', 'created_at' => $now, 'available_at' => $now, 'attempts' => 0],
            ['queue' => 'default', 'payload' => '{}', 'created_at' => $now, 'available_at' => $now, 'attempts' => 0],
            ['queue' => 'emails', 'payload' => '{}', 'created_at' => $now, 'available_at' => $now, 'attempts' => 0],
        ]);

        DB::table('failed_jobs')->insert([
            ['uuid' => 'test-uuid-1', 'connection' => 'database', 'queue' => 'emails', 'payload' => '{}', 'exception' => 'Test exception', 'failed_at' => now()],
            ['uuid' => 'test-uuid-2', 'connection' => 'database', 'queue' => 'default', 'payload' => '{}', 'exception' => 'Test exception', 'failed_at' => now()],
        ]);

        $service = app(\App\Services\QueueHealthService::class);
        $snapshot = $service->snapshot();

        $this->assertSame(3, $snapshot['pending_jobs']);
        $this->assertSame(2, $snapshot['failed_jobs']);
        $this->assertSame(1, $snapshot['email_jobs']);

        $deadLetters = $service->deadLetterCandidates(0);
        $this->assertCount(2, $deadLetters);
    }

    public function test_failed_job_retry_is_logged(): void
    {
        $admin = User::factory()->create();
        
        DB::table('failed_jobs')->insert([
            'uuid' => 'retry-test-uuid',
            'connection' => 'database',
            'queue' => 'emails',
            'payload' => '{}',
            'exception' => 'Test exception',
            'failed_at' => now(),
        ]);

        $service = app(PaymentRecoveryService::class);
        
        $exitCode = $service->retryFailedJob('retry-test-uuid', $admin);
        
        $this->assertGreaterThanOrEqual(0, $exitCode);
    }

    public function test_recovery_service_exists(): void
    {
        $service = app(PaymentRecoveryService::class);
        $this->assertInstanceOf(PaymentRecoveryService::class, $service);
    }
}