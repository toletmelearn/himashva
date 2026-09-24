<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UrlCouponTest extends TestCase
{
    use RefreshDatabase;

    public function test_coupon_captured_from_url_parameter(): void
    {
        $this->get('/?coupon=welcome10')->assertOk();

        $this->assertSame('WELCOME10', session('auto_coupon'));
    }

    public function test_auto_coupon_banner_shown_on_cart_page(): void
    {
        $this->withSession(['auto_coupon' => 'WELCOME10']);

        $response = $this->get(route('cart.index'));

        $response->assertOk();
        $response->assertSee('WELCOME10');
        $response->assertSee('is ready!');
    }

    public function test_dismiss_coupon_clears_session(): void
    {
        $this->withSession(['auto_coupon' => 'WELCOME10']);

        $response = $this->post(route('cart.dismissCoupon'));

        $response->assertOk()->assertJson(['ok' => true]);
        $this->assertNull(session('auto_coupon'));
    }
}
