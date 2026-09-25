<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CouponService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_coupon_is_rejected(): void
    {
        $coupon = Coupon::factory()->create(['expires_at' => now()->subDay()]);

        [$valid, $message] = app(CouponService::class)->validate($coupon->code, 500);

        $this->assertFalse($valid);
        $this->assertSame('This coupon is not valid or has expired.', $message);
    }

    public function test_coupon_not_yet_started_is_rejected(): void
    {
        $coupon = Coupon::factory()->create(['starts_at' => now()->addDay()]);

        [$valid] = app(CouponService::class)->validate($coupon->code, 500);

        $this->assertFalse($valid);
    }

    public function test_coupon_below_minimum_order_value_is_rejected(): void
    {
        $coupon = Coupon::factory()->create(['min_order_amount' => 1000]);

        [$valid, $message] = app(CouponService::class)->validate($coupon->code, 500);

        $this->assertFalse($valid);
        $this->assertSame('This coupon is not valid or has expired.', $message);
    }

    public function test_coupon_at_or_above_minimum_order_value_is_accepted(): void
    {
        $coupon = Coupon::factory()->create(['min_order_amount' => 500]);

        [$valid] = app(CouponService::class)->validate($coupon->code, 500);

        $this->assertTrue($valid);
    }

    public function test_coupon_exceeding_max_uses_is_rejected(): void
    {
        $coupon = Coupon::factory()->create(['max_uses' => 2, 'used_count' => 2]);

        [$valid] = app(CouponService::class)->validate($coupon->code, 500);

        $this->assertFalse($valid);
    }

    public function test_user_exceeding_per_user_limit_is_rejected(): void
    {
        $coupon = Coupon::factory()->create(['per_user_limit' => 1]);
        $user = User::factory()->create();

        Order::factory()->create([
            'user_id' => $user->id,
            'coupon_id' => $coupon->id,
            'payment_status' => 'paid',
        ]);

        $this->actingAs($user);
        [$valid, $message] = app(CouponService::class)->validate($coupon->code, 500);

        $this->assertFalse($valid);
        $this->assertSame('You have already used this coupon.', $message);
    }

    public function test_user_under_per_user_limit_is_accepted(): void
    {
        $coupon = Coupon::factory()->create(['per_user_limit' => 2]);
        $user = User::factory()->create();

        Order::factory()->create([
            'user_id' => $user->id,
            'coupon_id' => $coupon->id,
            'payment_status' => 'paid',
        ]);

        $this->actingAs($user);
        [$valid] = app(CouponService::class)->validate($coupon->code, 500);

        $this->assertTrue($valid);
    }

    public function test_percentage_discount_is_capped_by_max_discount_amount(): void
    {
        $coupon = Coupon::factory()->create([
            'type' => 'percentage',
            'value' => 50,
            'max_discount_amount' => 100,
        ]);

        $discount = app(CouponService::class)->calculateDiscount($coupon, 1000);

        $this->assertEquals(100, $discount);
    }

    public function test_fixed_discount_does_not_exceed_subtotal(): void
    {
        $coupon = Coupon::factory()->create(['type' => 'fixed', 'value' => 500]);

        $discount = app(CouponService::class)->calculateDiscount($coupon, 200);

        $this->assertEquals(200, $discount);
    }

    public function test_invalid_coupon_code_is_rejected(): void
    {
        [$valid, $message] = app(CouponService::class)->validate('DOES-NOT-EXIST', 500);

        $this->assertFalse($valid);
        $this->assertSame('Invalid coupon code.', $message);
    }

    public function test_cart_page_drops_coupon_and_discount_once_subtotal_falls_below_minimum(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'price' => 100, 'stock' => 10]);
        $coupon = Coupon::factory()->create(['min_order_amount' => 500, 'type' => 'fixed', 'value' => 50]);

        $this->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 1])->assertOk();
        Session::put('applied_coupon', $coupon->code);

        $response = $this->get('/cart');

        $response->assertOk();
        $response->assertViewHas('discount', 0);
        $this->assertNull(Session::get('applied_coupon'));
    }

    public function test_placing_order_with_valid_coupon_applies_discount_and_increments_usage(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'price' => 1000, 'stock' => 10]);
        $coupon = Coupon::factory()->create(['type' => 'fixed', 'value' => 100, 'min_order_amount' => 500]);

        $this->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 1])->assertOk();
        $this->postJson('/cart/apply-coupon', ['code' => $coupon->code])->assertJson(['success' => true]);

        $this->post('/checkout/place-order', [
            'name' => 'Coupon Buyer',
            'email' => 'couponbuyer@example.com',
            'phone' => '9999999999',
            'address_line_1' => '123 Test St',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'postal_code' => '110001',
            'country' => 'India',
            'payment_method' => 'cod',
        ]);

        $order = Order::first();
        $coupon->refresh();

        $this->assertNotNull($order);
        $this->assertEquals($coupon->id, $order->coupon_id);
        $this->assertEquals(100, (float) $order->discount_amount);
        $this->assertEquals(1, $coupon->used_count);
        $this->assertNull(Session::get('applied_coupon'));
    }
}
