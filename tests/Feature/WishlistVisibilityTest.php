<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_wishlist_button_is_visible_to_guests_on_product_page(): void
    {
        $product = Product::factory()->create(['status' => 'active']);

        $response = $this->get(route('product.show', $product->slug));

        $response->assertOk();
        $response->assertSee(route('wishlist.toggle'), false);
    }

    public function test_wishlist_button_is_visible_to_guests_on_shop_listing(): void
    {
        Product::factory()->create(['status' => 'active']);

        $response = $this->get(route('shop'));

        $response->assertOk();
        $response->assertSee('wishlist-btn-mock', false);
    }

    public function test_guest_toggling_wishlist_receives_unauthorized_response(): void
    {
        $product = Product::factory()->create(['status' => 'active']);

        $response = $this->postJson(route('wishlist.toggle'), ['product_id' => $product->id]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_toggle_wishlist(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['status' => 'active']);

        $response = $this->actingAs($user)->postJson(route('wishlist.toggle'), ['product_id' => $product->id]);

        $response->assertOk();
        $response->assertJson(['success' => true, 'in_wishlist' => true]);
    }

    public function test_added_wishlist_item_persists_and_shows_on_account_page(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['status' => 'active']);

        $this->actingAs($user)->postJson(route('wishlist.toggle'), ['product_id' => $product->id])->assertOk();

        $response = $this->actingAs($user)->get(route('account.wishlist'));

        $response->assertOk();
        $response->assertSee($product->name);
    }

    public function test_removing_wishlist_item_clears_it_from_account_page(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['status' => 'active']);

        $this->actingAs($user)->postJson(route('wishlist.toggle'), ['product_id' => $product->id])->assertOk();
        $this->actingAs($user)->postJson(route('wishlist.toggle'), ['product_id' => $product->id])
            ->assertJson(['success' => true, 'in_wishlist' => false]);

        $response = $this->actingAs($user)->get(route('account.wishlist'));

        $response->assertOk();
        $response->assertDontSee($product->name);
    }
}
