<?php

namespace Tests\Load;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class LoadTestScenarios extends TestCase
{
    use RefreshDatabase;

    public function test_concurrent_product_page_access(): void
    {
        $category = Category::create(['name' => 'Load Test', 'slug' => 'load-test']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Load Product',
            'slug' => 'load-prod',
            'price' => 100,
            'stock' => 100,
            'status' => true,
        ]);

        $response = $this->get("/product/{$product->slug}");
        $response->assertOk();
    }

    public function test_cache_hit_scenario(): void
    {
        $category = Category::create(['name' => 'Cache Test', 'slug' => 'cache-test', 'status' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Cache Product',
            'slug' => 'cache-prod',
            'price' => 100,
            'stock' => 100,
            'status' => true,
        ]);

        $service = app(\App\Services\ProductService::class);
        
        $result1 = $service->getProductBySlug('cache-prod');
        $result2 = $service->getProductBySlug('cache-prod');

        $this->assertNotNull($result1);
        $this->assertNotNull($result2);
    }

    public function test_queue_health_check(): void
    {
        DB::table('jobs')->insert([
            ['queue' => 'default', 'payload' => '{}', 'created_at' => now(), 'available_at' => now(), 'attempts' => 0],
            ['queue' => 'emails', 'payload' => '{}', 'created_at' => now(), 'available_at' => now(), 'attempts' => 0],
        ]);

        $service = app(\App\Services\QueueHealthService::class);
        $snapshot = $service->snapshot();

        $this->assertSame(2, $snapshot['pending_jobs']);
    }
}