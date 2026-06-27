<?php

namespace Tests\Feature;

use Tests\TestCase;

class IntelligenceLayerTest extends TestCase
{
    public function test_recommendation_service_exists(): void
    {
        $service = app(\App\Services\RecommendationService::class);
        $this->assertInstanceOf(\App\Services\RecommendationService::class, $service);
    }

    public function test_cart_recovery_service_exists(): void
    {
        $service = app(\App\Services\CartRecoveryService::class);
        $this->assertInstanceOf(\App\Services\CartRecoveryService::class, $service);
    }
}