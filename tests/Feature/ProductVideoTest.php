<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVideo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVideoTest extends TestCase
{
    use RefreshDatabase;

    public function test_youtube_video_id_is_auto_extracted_from_url(): void
    {
        $product = Product::factory()->create();

        $video = ProductVideo::create([
            'product_id' => $product->id,
            'platform' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        $this->assertSame('dQw4w9WgXcQ', $video->video_id);
    }

    public function test_youtube_thumbnail_is_auto_generated_when_not_set(): void
    {
        $product = Product::factory()->create();

        $video = ProductVideo::create([
            'product_id' => $product->id,
            'platform' => 'youtube',
            'video_url' => 'https://youtu.be/dQw4w9WgXcQ',
        ]);

        $this->assertSame('https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg', $video->thumbnail);
    }

    public function test_instagram_video_id_is_auto_extracted_from_url(): void
    {
        $product = Product::factory()->create();

        $video = ProductVideo::create([
            'product_id' => $product->id,
            'platform' => 'instagram',
            'video_url' => 'https://www.instagram.com/reel/Cabc123XYZ/',
        ]);

        $this->assertSame('Cabc123XYZ', $video->video_id);
    }

    public function test_product_videos_relation_excludes_inactive_and_orders_by_sort_order(): void
    {
        $product = Product::factory()->create();
        ProductVideo::factory()->for($product)->create(['sort_order' => 2, 'title' => 'Second']);
        ProductVideo::factory()->for($product)->create(['sort_order' => 1, 'title' => 'First']);
        ProductVideo::factory()->for($product)->create(['is_active' => false, 'title' => 'Hidden']);

        $titles = $product->fresh()->videos->pluck('title')->all();

        $this->assertSame(['First', 'Second'], $titles);
    }
}
