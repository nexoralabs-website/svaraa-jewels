<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UploadBatch;
use App\Models\BulkUploadEvent;
use App\Services\BulkUploadAuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class UploadBatchPolicyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_access_dashboard()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);
        $this->assertTrue(Gate::allows('dashboardAccess', UploadBatch::class));
    }

    /** @test */
    public function non_admin_cannot_access_dashboard()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);
        $this->assertFalse(Gate::allows('dashboardAccess', UploadBatch::class));
    }

    /** @test */
    public function denied_access_creates_audit_event()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);
        Gate::denies('dashboardAccess', UploadBatch::class);
        
        $this->assertDatabaseHas('bulk_upload_events', [
            'event_type' => 'access_denied',
            'actor_id' => $user->id,
        ]);
    }

    /** @test */
    public function admin_can_publish()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);
        $this->assertTrue(Gate::allows('publish', UploadBatch::class));
    }

    /** @test */
    public function non_admin_cannot_publish()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);
        $this->assertFalse(Gate::allows('publish', UploadBatch::class));
    }

    /** @test */
    public function admin_can_retry()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);
        $this->assertTrue(Gate::allows('retry', UploadBatch::class));
    }

    /** @test */
    public function non_admin_cannot_retry()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);
        $this->assertFalse(Gate::allows('retry', UploadBatch::class));
    }

    /** @test */
    public function admin_can_view_any_batches()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);
        $this->assertTrue(Gate::allows('viewAny', UploadBatch::class));
    }

    /** @test */
    public function non_admin_cannot_view_any_batches()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);
        $this->assertFalse(Gate::allows('viewAny', UploadBatch::class));
    }
}
