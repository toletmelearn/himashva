<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductRecommendationTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_page_shows_frequently_bought_together(): void
    {
        $anchor = Product::factory()->create();
        $coProduct = Product::factory()->create();
        $unrelated = Product::factory()->create();

        foreach ([1, 2] as $i) {
            $order = Order::factory()->create();
            OrderItem::factory()->for($order)->create(['product_id' => $anchor->id, 'sku' => $anchor->sku, 'product_name' => $anchor->name]);
            OrderItem::factory()->for($order)->create(['product_id' => $coProduct->id, 'sku' => $coProduct->sku, 'product_name' => $coProduct->name]);
        }

        $response = $this->get(route('product.show', $anchor->slug));

        $response->assertOk();
        $response->assertSee('Frequently Bought Together');
        $response->assertSee($coProduct->name);
        $response->assertDontSee($unrelated->name);
    }

    public function test_product_page_falls_back_to_category_when_no_order_history(): void
    {
        $category = Category::factory()->create();
        $anchor = Product::factory()->create(['category_id' => $category->id]);
        $sibling = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->get(route('product.show', $anchor->slug));

        $response->assertOk();
        $response->assertSee('Frequently Bought Together');
        $response->assertSee($sibling->name);
    }
}
