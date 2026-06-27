<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\User;
use App\Services\CouponService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_flat_discount_coupon_is_valid(): void
    {
        $coupon = Coupon::create([
            'code' => 'WELCOME100',
            'type' => 'flat',
            'value' => 100,
            'active' => true,
        ]);

        $service = new CouponService();
        $result = $service->validate('WELCOME100', 500);

        $this->assertNotNull($result);
        $this->assertSame('WELCOME100', $result->code);
    }

    public function test_percentage_discount_coupon_calculates_correctly(): void
    {
        $coupon = Coupon::create([
            'code' => 'SAVE20',
            'type' => 'percentage',
            'value' => 20,
            'active' => true,
        ]);

        $service = new CouponService();
        $discount = $service->calculateDiscount($coupon, 500);

        $this->assertSame(100.0, $discount);
    }

    public function test_coupon_respects_minimum_order_value(): void
    {
        $coupon = Coupon::create([
            'code' => 'MIN500',
            'type' => 'flat',
            'value' => 50,
            'min_order_value' => 500,
            'active' => true,
        ]);

        $service = new CouponService();

        $validResult = $service->validate('MIN500', 600);
        $invalidResult = $service->validate('MIN500', 400);

        $this->assertNotNull($validResult);
        $this->assertNull($invalidResult);
    }

    public function test_expired_coupon_is_invalid(): void
    {
        $coupon = Coupon::create([
            'code' => 'EXPIRED',
            'type' => 'flat',
            'value' => 50,
            'expires_at' => now()->subDay(),
            'active' => true,
        ]);

        $service = new CouponService();
        $result = $service->validate('EXPIRED', 500);

        $this->assertNull($result);
    }

    public function test_usage_limit_is_enforced(): void
    {
        $coupon = Coupon::create([
            'code' => 'ONCE',
            'type' => 'flat',
            'value' => 50,
            'usage_limit' => 1,
            'used_count' => 1,
            'active' => true,
        ]);

        $service = new CouponService();
        $result = $service->validate('ONCE', 500);

        $this->assertNull($result);
    }

    public function test_first_order_only_restriction(): void
    {
        $user = User::factory()->create();
        $user->orders()->create([
            'order_number' => 'SVR-X',
            'total' => 100,
            'subtotal' => 100,
            'discount' => 0,
            'shipping' => 0,
            'tax' => 0,
            'payment_status' => 'captured',
            'order_status' => 'paid',
            'customer_name' => 'Test',
            'customer_email' => 'test@test.com',
            'customer_phone' => '9999999999',
            'shipping_address' => 'Test',
        ]);

        $coupon = Coupon::create([
            'code' => 'FIRST',
            'type' => 'flat',
            'value' => 50,
            'first_order_only' => true,
            'active' => true,
        ]);

        $service = new CouponService();
        $result = $service->validate('FIRST', 500, $user);

        $this->assertNull($result);
    }

    public function test_coupon_code_generation(): void
    {
        $service = new CouponService();
        $code = $service->generateCode('TEST');

        $this->assertMatchesRegularExpression('/^TEST-\d{6}$/', $code);
    }
}