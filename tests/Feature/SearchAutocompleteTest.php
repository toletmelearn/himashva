<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchAutocompleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_api_returns_matching_products(): void
    {
        $product = Product::factory()->create(['name' => 'Lavender Dream Candle']);
        Product::factory()->create(['name' => 'Unrelated Item']);

        $response = $this->getJson(route('api.search', ['q' => 'Lavender']));

        $response->assertOk();
        $response->assertJsonFragment(['name' => 'Lavender Dream Candle']);
        $response->assertJsonMissing(['name' => 'Unrelated Item']);
    }

    public function test_search_api_requires_minimum_2_chars(): void
    {
        Product::factory()->create(['name' => 'Lavender Dream Candle']);

        $response = $this->getJson(route('api.search', ['q' => 'a']));

        $response->assertOk()->assertExactJson([]);
    }

    public function test_search_api_excludes_inactive_products(): void
    {
        Product::factory()->create(['name' => 'Lavender Draft Candle', 'status' => 'draft']);

        $response = $this->getJson(route('api.search', ['q' => 'Lavender']));

        $response->assertOk()->assertExactJson([]);
    }
}
