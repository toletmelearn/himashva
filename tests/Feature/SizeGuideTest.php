<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\SizeGuide;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SizeGuideTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_can_create_size_guide_with_table_data(): void
    {
        $guide = SizeGuide::factory()->create([
            'name' => 'Candle Size Guide',
            'table_data' => ['headers' => ['Diameter', 'Height'], 'rows' => [['7cm', '8cm']]],
        ]);

        $this->assertDatabaseHas('size_guides', ['name' => 'Candle Size Guide']);
        $this->assertEquals(['Diameter', 'Height'], $guide->fresh()->table_data['headers']);
    }

    public function test_size_guide_shows_on_product_page_when_directly_assigned(): void
    {
        $product = Product::factory()->create(['status' => 'active', 'is_active' => true]);
        $guide = SizeGuide::factory()->create(['name' => 'Direct Guide', 'is_active' => true]);
        $product->sizeGuides()->attach($guide);

        $response = $this->get(route('product.show', $product->slug));

        $response->assertOk();
        $response->assertSee('Direct Guide');
    }

    public function test_category_level_size_guide_applies_to_products_in_that_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['status' => 'active', 'is_active' => true, 'category_id' => $category->id]);
        $guide = SizeGuide::factory()->create(['name' => 'Category Guide', 'category_id' => $category->id, 'is_active' => true]);

        $response = $this->get(route('product.show', $product->slug));

        $response->assertOk();
        $response->assertSee('Category Guide');
    }

    public function test_product_level_guide_overrides_category_level(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['status' => 'active', 'is_active' => true, 'category_id' => $category->id]);

        $categoryGuide = SizeGuide::factory()->create(['name' => 'Category Guide', 'category_id' => $category->id, 'is_active' => true]);
        $productGuide = SizeGuide::factory()->create(['name' => 'Product Guide', 'is_active' => true]);
        $product->sizeGuides()->attach($productGuide);

        $response = $this->get(route('product.show', $product->slug));

        $response->assertOk();
        $response->assertSee('Product Guide');
        $response->assertDontSee('Category Guide');
    }

    public function test_inactive_size_guides_not_shown(): void
    {
        $product = Product::factory()->create(['status' => 'active', 'is_active' => true]);
        $guide = SizeGuide::factory()->create(['name' => 'Inactive Guide', 'is_active' => false]);
        $product->sizeGuides()->attach($guide);

        $response = $this->get(route('product.show', $product->slug));

        $response->assertOk();
        $response->assertDontSee('Inactive Guide');
    }
}
