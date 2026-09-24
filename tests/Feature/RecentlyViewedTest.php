<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecentlyViewedTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewing_a_product_adds_it_to_recently_viewed_session(): void
    {
        $product = Product::factory()->create();

        $this->get(route('product.show', $product->slug))->assertOk();

        $this->assertSame([$product->id], session('recently_viewed'));
    }

    public function test_recently_viewed_excludes_current_product_on_product_page(): void
    {
        $first = Product::factory()->create();
        $second = Product::factory()->create();

        $this->get(route('product.show', $first->slug))->assertOk();
        $response = $this->get(route('product.show', $second->slug));

        $response->assertOk();
        $response->assertSee('Recently Viewed');
        $response->assertSee($first->name);
        $response->assertDontSee($second->name.'</h3>', false);
    }
}
