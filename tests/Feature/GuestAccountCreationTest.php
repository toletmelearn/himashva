<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class GuestAccountCreationTest extends TestCase
{
    use RefreshDatabase;

    protected function guestOrder(array $overrides = []): Order
    {
        return Order::factory()->create(array_merge([
            'user_id' => null,
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'payment_status' => 'paid',
            'order_status' => 'processing',
        ], $overrides));
    }

    public function test_guest_can_create_account_after_checkout(): void
    {
        $order = $this->guestOrder();

        $response = $this->postJson(route('guest.create-account'), [
            'order_number' => $order->order_number,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', ['email' => 'jane@example.com', 'name' => 'Jane Doe']);

        $user = User::where('email', 'jane@example.com')->first();
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'user_id' => $user->id]);
    }

    public function test_guest_account_creation_fails_if_email_already_registered(): void
    {
        User::factory()->create(['email' => 'jane@example.com']);
        $order = $this->guestOrder();

        $response = $this->postJson(route('guest.create-account'), [
            'order_number' => $order->order_number,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => false]);
        $this->assertFalse(Auth::check());

        $order->refresh();
        $this->assertNull($order->user_id);
    }

    public function test_guest_account_creation_requires_password(): void
    {
        $order = $this->guestOrder();

        $response = $this->postJson(route('guest.create-account'), [
            'order_number' => $order->order_number,
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('users', ['email' => 'jane@example.com']);
    }

    public function test_guest_account_creation_fails_for_order_already_claimed(): void
    {
        $owner = User::factory()->create();
        $order = $this->guestOrder(['user_id' => $owner->id]);

        $response = $this->postJson(route('guest.create-account'), [
            'order_number' => $order->order_number,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => false]);
        $this->assertDatabaseMissing('users', ['email' => $order->email]);
    }

    public function test_guest_account_creation_fails_for_unknown_order_number(): void
    {
        $response = $this->postJson(route('guest.create-account'), [
            'order_number' => 'HMV-20260101-9999',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => false]);
    }

    public function test_order_linked_to_new_account_after_registration(): void
    {
        $order = $this->guestOrder(['email' => 'linked@example.com', 'name' => 'Link Test']);

        $this->postJson(route('guest.create-account'), [
            'order_number' => $order->order_number,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertJson(['success' => true]);

        $user = User::where('email', 'linked@example.com')->firstOrFail();
        $this->assertTrue($order->fresh()->user_id === $user->id);
    }
}
