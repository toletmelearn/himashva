<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartStockLimitsTest extends TestCase
{
    use RefreshDatabase;

    public function test_adding_zero_quantity_is_rejected(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'active', 'stock' => 10]);

        $this->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 0])
            ->assertUnprocessable();
    }

    public function test_adding_more_than_available_stock_is_rejected(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'active', 'stock' => 3]);

        $response = $this->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 5]);

        $response->assertOk();
        $response->assertJson(['success' => false]);
    }

    public function test_increasing_cart_quantity_beyond_available_stock_is_rejected(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'active', 'stock' => 3]);
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 2])->assertOk();

        $item = CartItem::where('user_id', $user->id)->where('product_id', $product->id)->first();

        $response = $this->actingAs($user)->patchJson("/cart/update/{$item->id}", ['quantity' => 10]);

        $response->assertJson(['success' => false]);
        $this->assertEquals(2, $item->fresh()->quantity);
    }

    public function test_increasing_cart_quantity_within_available_stock_succeeds(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'active', 'stock' => 10]);
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 2])->assertOk();

        $item = CartItem::where('user_id', $user->id)->where('product_id', $product->id)->first();

        $this->actingAs($user)->patchJson("/cart/update/{$item->id}", ['quantity' => 8])
            ->assertJson(['success' => true]);

        $this->assertEquals(8, $item->fresh()->quantity);
    }

    public function test_checkout_rejects_order_when_stock_drops_below_cart_quantity_before_placing_order(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'active', 'stock' => 5]);
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 5])->assertOk();

        // Stock drops (e.g. sold via another channel) after the item was added to the cart.
        $product->update(['stock' => 1]);

        $response = $this->actingAs($user)->post('/checkout/place-order', [
            'name' => 'Buyer', 'email' => 'buyer@example.com', 'phone' => '9999999999',
            'address_line_1' => 'Street', 'city' => 'Delhi', 'state' => 'Delhi',
            'postal_code' => '110001', 'country' => 'India', 'payment_method' => 'cod',
        ]);

        $response->assertRedirect(route('cart.index'));
        $this->assertDatabaseCount('orders', 0);
        $product->refresh();
        $this->assertEquals(1, $product->stock);
    }

    public function test_concurrent_checkouts_for_the_last_unit_only_let_one_order_succeed(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'active', 'stock' => 1]);
        $buyerA = User::factory()->create();
        $buyerB = User::factory()->create();

        $this->actingAs($buyerA)->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 1])->assertOk();
        $this->actingAs($buyerB)->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 1])->assertOk();

        $payload = [
            'name' => 'Buyer', 'email' => 'buyer@example.com', 'phone' => '9999999999',
            'address_line_1' => 'Street', 'city' => 'Delhi', 'state' => 'Delhi',
            'postal_code' => '110001', 'country' => 'India', 'payment_method' => 'cod',
        ];

        $this->actingAs($buyerA)->post('/checkout/place-order', $payload);
        $this->actingAs($buyerB)->post('/checkout/place-order', $payload);

        $this->assertDatabaseCount('orders', 1);
        $product->refresh();
        $this->assertEquals(0, $product->stock);
    }
}
