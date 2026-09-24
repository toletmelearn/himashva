<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JsonLdSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_page_contains_json_ld_product_schema(): void
    {
        $product = Product::factory()->create(['status' => 'active']);

        $response = $this->get(route('product.show', $product->slug));

        $response->assertOk();
        $response->assertSee('application/ld+json', false);
        $response->assertSee('"@type": "Product"', false);
        $response->assertSee('"@type": "BreadcrumbList"', false);
    }

    public function test_homepage_contains_json_ld_organization_schema(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('"@type": "Organization"', false);
    }

    public function test_shop_page_contains_json_ld_breadcrumb_schema(): void
    {
        $response = $this->get(route('shop'));

        $response->assertOk();
        $response->assertSee('"@type": "BreadcrumbList"', false);
    }
}
