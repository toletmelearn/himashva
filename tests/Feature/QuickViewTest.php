<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuickViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_quick_view_api_returns_product_data(): void
    {
        $product = Product::factory()->create(['status' => 'active']);

        $response = $this->getJson(route('api.product.show', $product->slug));

        $response->assertOk()->assertJson([
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
        ]);
    }

    public function test_quick_view_api_excludes_inactive_products(): void
    {
        $product = Product::factory()->create(['status' => 'draft']);

        $response = $this->getJson(route('api.product.show', $product->slug));

        $response->assertNotFound();
    }
}
