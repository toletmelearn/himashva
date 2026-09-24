<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVideo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVideoStorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_page_shows_video_section_when_videos_exist(): void
    {
        $product = Product::factory()->create(['status' => 'active']);
        ProductVideo::factory()->for($product)->create([
            'title' => 'Unboxing Video',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        $response = $this->get(route('product.show', $product->slug));

        $response->assertOk();
        $response->assertSee('Watch Our Videos');
        $response->assertSee('Unboxing Video');
        $response->assertSee('https://www.youtube.com/watch?v=dQw4w9WgXcQ', false);
    }

    public function test_product_page_hides_video_section_when_no_videos(): void
    {
        $product = Product::factory()->create(['status' => 'active']);

        $response = $this->get(route('product.show', $product->slug));

        $response->assertOk();
        $response->assertDontSee('Watch Our Videos');
    }
}
