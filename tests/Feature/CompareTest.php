<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompareTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_add_product_to_comparison(): void
    {
        $product = Product::factory()->create();

        $response = $this->postJson(route('compare.add', $product->id));

        $response->assertOk()->assertJson(['success' => true, 'count' => 1]);
    }

    public function test_cannot_exceed_max_comparison_limit(): void
    {
        $products = Product::factory()->count(5)->create();

        foreach ($products->take(4) as $product) {
            $this->postJson(route('compare.add', $product->id))->assertOk();
        }

        $response = $this->postJson(route('compare.add', $products->last()->id));

        $response->assertStatus(422)->assertJson(['success' => false]);
    }

    public function test_cannot_add_duplicate_product(): void
    {
        $product = Product::factory()->create();

        $this->postJson(route('compare.add', $product->id))->assertOk();
        $response = $this->postJson(route('compare.add', $product->id));

        $response->assertOk()->assertJson(['success' => false, 'count' => 1]);
    }

    public function test_can_remove_product_from_comparison(): void
    {
        $product = Product::factory()->create();
        $this->postJson(route('compare.add', $product->id))->assertOk();

        $response = $this->deleteJson(route('compare.remove', $product->id));

        $response->assertOk()->assertJson(['success' => true, 'count' => 0]);
    }

    public function test_comparison_page_shows_products_side_by_side(): void
    {
        $products = Product::factory()->count(2)->create();

        foreach ($products as $product) {
            $this->postJson(route('compare.add', $product->id))->assertOk();
        }

        $response = $this->get(route('compare.show'));

        $response->assertOk();
        foreach ($products as $product) {
            $response->assertSee($product->name);
        }
    }

    public function test_comparison_page_handles_empty_list_gracefully(): void
    {
        $response = $this->get(route('compare.show'));

        $response->assertOk();
        $response->assertSee('Add at least 2 products');
    }
}
