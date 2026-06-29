<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TempBulkUploadPageInspectTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_upload_page_can_be_captured_for_bootstrap_inspection(): void
    {
        $user = User::factory()->create([
            'is_admin' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/bulk-upload-products');

        $response->assertOk();

        File::put(
            base_path('bulk-upload-page.html'),
            $response->getContent(),
        );

        $response->assertSee('livewire.js', false);
    }
}
