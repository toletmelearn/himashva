<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVideo;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVideoAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function manager(): User
    {
        $user = User::factory()->create(['is_admin' => false]);
        $user->assignRole('manager');

        return $user;
    }

    public function test_manager_can_view_product_edit_page_with_videos_relation_manager(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->manager())
            ->get("/admin/products/{$product->id}/edit")
            ->assertOk()
            ->assertSee('Videos');
    }

    public function test_admin_can_add_youtube_video_to_product(): void
    {
        $admin = $this->manager();
        $product = Product::factory()->create();

        $this->actingAs($admin);

        $video = ProductVideo::create([
            'product_id' => $product->id,
            'platform' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'title' => 'Unboxing Video',
        ]);

        $this->assertDatabaseHas('product_videos', [
            'id' => $video->id,
            'product_id' => $product->id,
            'title' => 'Unboxing Video',
        ]);

        $this->assertSame('dQw4w9WgXcQ', $video->video_id);
        $this->assertTrue($product->videos()->whereKey($video->id)->exists());
    }
}
