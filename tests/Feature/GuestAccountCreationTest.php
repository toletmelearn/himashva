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

        $response = $this->withSession(['claimable_order_id' => $order->id])
            ->postJson(route('guest.create-account'), [
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

        $response = $this->withSession(['claimable_order_id' => $order->id])
            ->postJson(route('guest.create-account'), [
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

        $response = $this->withSession(['claimable_order_id' => $order->id])
            ->postJson(route('guest.create-account'), [
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

        $response = $this->withSession(['claimable_order_id' => $order->id])
            ->postJson(route('guest.create-account'), [
                'order_number' => $order->order_number,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertOk();
        $response->assertJson(['success' => false]);
        $this->assertDatabaseMissing('users', ['email' => $order->email]);
    }

    public function test_guest_account_creation_fails_without_matching_session(): void
    {
        // Attacker enumerates a real, unclaimed guest order number that they never placed
        // (no session claim written for it) and tries to take it over.
        $order = $this->guestOrder(['email' => 'victim@example.com']);

        $response = $this->postJson(route('guest.create-account'), [
            'order_number' => $order->order_number,
            'password' => 'attackerpassword123',
            'password_confirmation' => 'attackerpassword123',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => false]);
        $this->assertDatabaseMissing('users', ['email' => 'victim@example.com']);
        $this->assertFalse(Auth::check());
        $this->assertNull($order->fresh()->user_id);
    }

    public function test_guest_account_creation_fails_when_session_claims_a_different_order(): void
    {
        $victim = $this->guestOrder(['email' => 'victim@example.com', 'order_number' => 'HMV-20260101-0001']);
        $ownOrder = $this->guestOrder(['email' => 'attacker@example.com', 'order_number' => 'HMV-20260101-0002']);

        // Attacker's own session only proves they placed $ownOrder, not $victim.
        $response = $this->withSession(['claimable_order_id' => $ownOrder->id])
            ->postJson(route('guest.create-account'), [
                'order_number' => $victim->order_number,
                'password' => 'attackerpassword123',
                'password_confirmation' => 'attackerpassword123',
            ]);

        $response->assertOk();
        $response->assertJson(['success' => false]);
        $this->assertDatabaseMissing('users', ['email' => 'victim@example.com']);
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

        $this->withSession(['claimable_order_id' => $order->id])
            ->postJson(route('guest.create-account'), [
                'order_number' => $order->order_number,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])->assertJson(['success' => true]);

        $user = User::where('email', 'linked@example.com')->firstOrFail();
        $this->assertTrue($order->fresh()->user_id === $user->id);
    }

    public function test_guest_can_create_account_after_razorpay_checkout(): void
    {
        $order = $this->guestOrder(['payment_method' => 'razorpay', 'email' => 'razorpay@example.com']);

        $response = $this->withSession(['claimable_order_id' => $order->id])
            ->postJson(route('guest.create-account'), [
                'order_number' => $order->order_number,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('users', ['email' => 'razorpay@example.com']);
    }

    public function test_guest_sees_create_account_panel_on_success_page(): void
    {
        $order = $this->guestOrder();

        $response = $this->withSession(['claimable_order_id' => $order->id])
            ->get(route('order.success', $order->order_number));

        $response->assertOk();
        $response->assertSee('Create My Account');
        $response->assertSee(route('guest.create-account'), false);
        $response->assertSee($order->email);
    }

    public function test_guest_sees_create_account_panel_on_razorpay_success_page(): void
    {
        $order = $this->guestOrder(['payment_method' => 'razorpay']);

        $response = $this->withSession(['claimable_order_id' => $order->id])
            ->get(route('order.success', $order->order_number));

        $response->assertOk();
        $response->assertSee('Create My Account');
    }

    public function test_guest_without_session_claim_does_not_see_panel_or_email(): void
    {
        // Same order exists and is still unclaimed, but this visitor's session never
        // placed it (e.g. they guessed/were given the URL) — panel and PII stay hidden.
        $order = $this->guestOrder(['email' => 'hidden@example.com']);

        $response = $this->get(route('order.success', $order->order_number));

        $response->assertOk();
        $response->assertDontSee('Create My Account');
        $response->assertDontSee('hidden@example.com');
    }

    public function test_logged_in_user_does_not_see_create_account_panel(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('order.success', $order->order_number));

        $response->assertOk();
        $response->assertDontSee('Create My Account');
    }

    public function test_logged_in_user_does_not_see_panel_on_unclaimed_guest_order(): void
    {
        $user = User::factory()->create();
        $order = $this->guestOrder();

        $response = $this->actingAs($user)->get(route('order.success', $order->order_number));

        $response->assertOk();
        $response->assertDontSee('Create My Account');
    }
}
