<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ChatbotResponse;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_loads(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_shop_and_category_pages_load(): void
    {
        $category = Category::factory()->create(['is_active' => true, 'parent_id' => null]);
        Product::factory()->create(['category_id' => $category->id, 'is_active' => true]);

        $this->get('/shop')->assertOk();
        $this->get('/category/'.$category->slug)->assertOk();
    }

    public function test_product_page_loads(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'is_active' => true]);

        $this->get('/product/'.$product->slug)->assertOk();
    }

    public function test_guest_can_add_to_cart_and_view_it(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'is_active' => true, 'stock' => 10]);

        $this->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 2])
            ->assertOk()
            ->assertJson(['success' => true, 'count' => 2]);

        $this->get('/cart')->assertOk()->assertSee($product->name);
    }

    public function test_placing_order_calculates_gst_at_products_tax_rate(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id, 'is_active' => true, 'stock' => 10,
            'price' => 500, 'tax_rate' => 18,
        ]);

        $this->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 2])->assertOk();

        $this->post('/checkout/place-order', [
            'name' => 'Tax Buyer', 'email' => 'tax@example.com', 'phone' => '9999999999',
            'address_line_1' => '123 Test St', 'city' => 'Delhi', 'state' => 'Delhi',
            'postal_code' => '110001', 'country' => 'India', 'payment_method' => 'cod',
        ]);

        $order = Order::first();

        // subtotal = 500 * 2 = 1000, no discount, tax = 1000 * 18% = 180
        $this->assertEquals(1000, (float) $order->subtotal);
        $this->assertEquals(180, (float) $order->tax_amount);
        $this->assertEquals($order->subtotal - $order->discount_amount + $order->shipping_amount + $order->tax_amount, (float) $order->total);
    }

    public function test_checkout_page_displays_gst_for_cart(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'is_active' => true, 'stock' => 10, 'price' => 500, 'tax_rate' => 18]);

        $this->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 1])->assertOk();

        $this->get('/checkout')->assertOk()->assertSee('GST')->assertSee('90.00');
    }

    public function test_real_product_image_takes_priority_over_ai_placeholder(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'is_active' => true]);

        // Placeholder inserted first (lower id) to reproduce the tie-break bug:
        // both rows share sort_order 0, so without an explicit image_type
        // preference the placeholder's insertion order wins ->first().
        $product->images()->create(['image_path' => 'placeholder.jpg', 'image_type' => 'ai_generated', 'sort_order' => 0]);
        $product->images()->create(['image_path' => 'products/real-photo.jpg', 'image_type' => 'real', 'sort_order' => 0]);

        $this->assertSame('products/real-photo.jpg', $product->fresh()->images->first()->image_path);
    }

    public function test_cannot_add_out_of_stock_product_to_cart(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'out_of_stock', 'stock' => 0]);

        $this->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 1])
            ->assertOk()
            ->assertJson(['success' => false, 'message' => 'Product is out of stock.']);
    }

    public function test_cannot_add_draft_product_to_cart(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'draft', 'stock' => 10]);

        $this->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 1])
            ->assertOk()
            ->assertJson(['success' => false, 'message' => 'Product is out of stock.']);
    }

    public function test_cannot_add_quantity_exceeding_stock(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'active', 'stock' => 2]);

        $this->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 5])
            ->assertOk()
            ->assertJson(['success' => false, 'message' => 'Product is out of stock.']);
    }

    public function test_guest_can_place_cod_order(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'is_active' => true, 'stock' => 10, 'price' => 500]);

        $this->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 1])->assertOk();

        $response = $this->post('/checkout/place-order', [
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
        $this->assertNotNull($order);
        $this->assertEquals('cod', $order->payment_method);
        $this->assertEquals(1, $order->items()->count());
        $response->assertRedirect('/order/success/'.$order->order_number);

        $product->refresh();
        $this->assertEquals(9, $product->stock);
    }

    public function test_contact_form_submission(): void
    {
        $response = $this->post('/contact', [
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'subject' => 'Question',
            'message' => 'Hello there',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('contact_messages', ['email' => 'jane@example.com']);
    }

    public function test_chatbot_responds_with_keyword_match(): void
    {
        ChatbotResponse::create([
            'category' => 'shipping',
            'keywords' => ['shipping'],
            'response' => 'Shipping is free above ₹999.',
            'is_active' => true,
            'sort_order' => 10,
        ]);

        $response = $this->postJson('/chatbot/message', ['message' => 'What are your shipping charges?']);

        $response->assertOk()->assertJsonStructure(['reply']);
        $this->assertStringContainsString('shipping', strtolower($response->json('reply')));
    }

    public function test_account_pages_require_auth(): void
    {
        $this->get('/account')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_account_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user)->get('/account')->assertOk();
    }
}
