<?php

namespace Tests\Feature;

use App\Filament\Resources\OrderResource\Pages\EditOrder;
use App\Filament\Resources\ReturnResource\Pages\EditReturn;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Services\CartService;
use App\Services\InventoryService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InventoryAndReturnsTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_placing_an_order_logs_a_sale_movement_and_can_flip_product_out_of_stock(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'stock' => 1, 'price' => 500]);

        $this->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 1])->assertOk();

        $this->post('/checkout/place-order', [
            'name' => 'Test Buyer',
            'email' => 'buyer@example.com',
            'phone' => '9999999999',
            'address_line_1' => '123 Test St',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'postal_code' => '110001',
            'country' => 'India',
            'payment_method' => 'cod',
        ]);

        $order = Order::first();
        $product->refresh();

        $this->assertEquals(0, $product->stock);
        $this->assertEquals('out_of_stock', $product->status);

        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'type' => 'sale',
            'quantity_delta' => -1,
            'reference_type' => Order::class,
            'reference_id' => $order->id,
        ]);
    }

    public function test_cancelling_order_restocks_via_order_service(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'stock' => 10]);
        $customer = User::factory()->create();

        $this->actingAs($customer)->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 3])->assertOk();
        $this->actingAs($customer)->post('/checkout/place-order', [
            'name' => 'Buyer', 'email' => 'buyer3@example.com', 'phone' => '9999999999',
            'address_line_1' => 'Street', 'city' => 'Delhi', 'state' => 'Delhi',
            'postal_code' => '110001', 'country' => 'India', 'payment_method' => 'cod',
        ]);

        $order = Order::first();
        $product->refresh();
        $this->assertEquals(7, $product->stock);

        app(OrderService::class)->restockCancelledOrder($order);

        $product->refresh();
        $this->assertEquals(10, $product->stock);

        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'type' => 'return',
            'quantity_delta' => 3,
        ]);
    }

    public function test_admin_cancelling_order_in_filament_restocks_stock(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'stock' => 10]);
        $customer = User::factory()->create();

        $this->actingAs($customer)->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 3])->assertOk();
        $this->actingAs($customer)->post('/checkout/place-order', [
            'name' => 'Buyer', 'email' => 'buyer4@example.com', 'phone' => '9999999999',
            'address_line_1' => 'Street', 'city' => 'Delhi', 'state' => 'Delhi',
            'postal_code' => '110001', 'country' => 'India', 'payment_method' => 'cod',
        ]);

        $order = Order::first();
        $product->refresh();
        $this->assertEquals(7, $product->stock);

        Livewire::actingAs($this->admin())
            ->test(EditOrder::class, ['record' => $order->id])
            ->fillForm(['order_status' => 'cancelled'])
            ->call('save')
            ->assertHasNoFormErrors();

        $product->refresh();
        $this->assertEquals(10, $product->stock);
        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'type' => 'return',
            'quantity_delta' => 3,
            'reference_type' => Order::class,
            'reference_id' => $order->id,
        ]);
    }

    public function test_adjust_stock_reverts_status_to_active_when_restocked(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'stock' => 0, 'status' => 'out_of_stock']);

        app(InventoryService::class)->adjustStock($product->id, 5, 'Restocked from supplier');

        $product->refresh();
        $this->assertEquals(5, $product->stock);
        $this->assertEquals('active', $product->status);
    }

    public function test_adjust_stock_does_not_reactivate_a_draft_product(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'stock' => 0, 'status' => 'draft']);

        app(InventoryService::class)->adjustStock($product->id, 5, 'Restock');

        $product->refresh();
        $this->assertEquals('draft', $product->status);
    }

    public function test_cannot_add_variant_to_cart_when_variant_stock_is_insufficient(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'active', 'stock' => 50]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => 'Small',
            'sku' => 'SKU-SMALL-1',
            'price' => 100,
            'stock' => 1,
        ]);

        [$success, $error] = app(CartService::class)->add($product->id, 3, $variant->id);

        $this->assertFalse($success);
        $this->assertEquals('Product is out of stock.', $error);
    }

    public function test_out_of_stock_products_remain_browsable_on_shop_and_detail_page(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'stock' => 0, 'status' => 'out_of_stock']);

        $this->get('/shop')->assertOk()->assertSee($product->name);
        $this->get('/product/'.$product->slug)->assertOk()->assertSee($product->name);
    }

    public function test_draft_products_are_not_visible_anywhere_on_storefront(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'draft']);

        $this->get('/shop')->assertOk()->assertDontSee($product->name);
        $this->get('/product/'.$product->slug)->assertNotFound();
    }

    public function test_customer_can_request_return_for_delivered_order_within_window(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $user = User::factory()->create();

        $order = Order::factory()->create(['user_id' => $user->id, 'order_status' => 'delivered', 'delivered_at' => now()->subDays(2)]);
        $orderItem = $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'unit_price' => $product->price,
            'quantity' => 2,
            'line_total' => $product->price * 2,
        ]);

        $response = $this->actingAs($user)->post("/account/orders/{$order->order_number}/return", [
            'reason_category' => 'defective',
            'reason_text' => 'Item arrived broken',
            'items' => [
                ['order_item_id' => $orderItem->id, 'quantity' => 1, 'condition' => 'damaged'],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('returns', [
            'order_id' => $order->id,
            'user_id' => $user->id,
            'reason_category' => 'defective',
            'status' => 'requested',
        ]);
        $this->assertDatabaseHas('return_items', [
            'order_item_id' => $orderItem->id,
            'quantity' => 1,
            'condition' => 'damaged',
        ]);
    }

    public function test_customer_cannot_request_return_outside_seven_day_window(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'order_status' => 'delivered', 'delivered_at' => now()->subDays(10)]);

        $this->actingAs($user)->post("/account/orders/{$order->order_number}/return", [
            'reason_category' => 'defective',
            'items' => [],
        ])->assertForbidden();
    }

    public function test_customer_cannot_request_return_for_non_delivered_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'order_status' => 'processing', 'delivered_at' => null]);

        $this->actingAs($user)->post("/account/orders/{$order->order_number}/return", [
            'reason_category' => 'defective',
            'items' => [],
        ])->assertForbidden();
    }

    public function test_return_received_by_admin_restocks_inventory(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'stock' => 5]);
        $user = User::factory()->create();

        $order = Order::factory()->create(['user_id' => $user->id, 'order_status' => 'delivered', 'delivered_at' => now()->subDay()]);
        $orderItem = $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'unit_price' => $product->price,
            'quantity' => 2,
            'line_total' => $product->price * 2,
        ]);

        $return = ReturnRequest::factory()->create(['order_id' => $order->id, 'user_id' => $user->id, 'status' => 'approved']);
        $return->items()->create(['order_item_id' => $orderItem->id, 'quantity' => 2, 'condition' => 'unopened']);

        Livewire::actingAs($this->admin())
            ->test(EditReturn::class, ['record' => $return->id])
            ->fillForm(['status' => 'received'])
            ->call('save')
            ->assertHasNoFormErrors();

        $product->refresh();
        $this->assertEquals(7, $product->stock);

        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'type' => 'return',
            'quantity_delta' => 2,
            'reference_type' => ReturnRequest::class,
            'reference_id' => $return->id,
        ]);
    }

    public function test_partial_return_received_by_admin_restocks_only_the_returned_quantity(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'stock' => 5]);
        $user = User::factory()->create();

        $order = Order::factory()->create(['user_id' => $user->id, 'order_status' => 'delivered', 'delivered_at' => now()->subDay()]);
        $orderItem = $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'unit_price' => $product->price,
            'quantity' => 3,
            'line_total' => $product->price * 3,
        ]);

        $return = ReturnRequest::factory()->create(['order_id' => $order->id, 'user_id' => $user->id, 'status' => 'approved']);
        $return->items()->create(['order_item_id' => $orderItem->id, 'quantity' => 1, 'condition' => 'unopened']);

        Livewire::actingAs($this->admin())
            ->test(EditReturn::class, ['record' => $return->id])
            ->fillForm(['status' => 'received'])
            ->call('save')
            ->assertHasNoFormErrors();

        $product->refresh();
        $this->assertEquals(6, $product->stock);

        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'type' => 'return',
            'quantity_delta' => 1,
            'reference_type' => ReturnRequest::class,
            'reference_id' => $return->id,
        ]);
    }
}
