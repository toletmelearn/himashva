<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTimelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_timeline_component_renders_on_order_detail(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'order_status' => 'shipped']);

        $response = $this->actingAs($user)->get(route('account.orders.show', $order->order_number));

        $response->assertOk();
        $response->assertSee('Order Placed');
        $response->assertSee('Confirmed');
        $response->assertSee('Processing');
        $response->assertSee('Shipped');
        $response->assertSee('Out for Delivery');
        $response->assertSee('Delivered');
    }

    public function test_cancelled_order_shows_cancellation_message_in_timeline(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'order_status' => 'cancelled',
            'cancellation_reason' => 'Customer requested cancellation',
        ]);

        $response = $this->actingAs($user)->get(route('account.orders.show', $order->order_number));

        $response->assertOk();
        $response->assertSee('Order was cancelled');
        $response->assertSee('Customer requested cancellation');
    }
}
