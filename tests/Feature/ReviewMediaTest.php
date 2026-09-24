<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\ReviewMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReviewMediaTest extends TestCase
{
    use RefreshDatabase;

    protected function product(): Product
    {
        return Product::factory()->create(['category_id' => Category::factory()->create()->id]);
    }

    public function test_customer_can_submit_review_with_photos(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $product = $this->product();

        $response = $this->actingAs($user)->post(route('account.reviews.store'), [
            'product_id' => $product->id,
            'rating' => 5,
            'title' => 'Great candle',
            'comment' => 'Smells amazing',
            'media' => [
                UploadedFile::fake()->image('photo.jpg'),
            ],
        ]);

        $response->assertRedirect();
        $review = Review::first();
        $this->assertNotNull($review);
        $this->assertCount(1, $review->media);
        Storage::disk('public')->assertExists($review->media->first()->file_path);
    }

    public function test_review_media_shown_on_product_page(): void
    {
        Storage::fake('public');

        $product = $this->product();
        $review = Review::factory()->create(['product_id' => $product->id, 'is_approved' => true]);
        ReviewMedia::factory()->create(['review_id' => $review->id, 'is_approved' => true]);

        $response = $this->get(route('product.show', $product->slug));

        $response->assertOk();
        $response->assertSee($review->media->first()->url, false);
    }

    public function test_unapproved_media_not_shown(): void
    {
        Storage::fake('public');

        $product = $this->product();
        $review = Review::factory()->create(['product_id' => $product->id, 'is_approved' => true]);
        $media = ReviewMedia::factory()->create(['review_id' => $review->id, 'is_approved' => false]);

        $response = $this->get(route('product.show', $product->slug));

        $response->assertOk();
        $response->assertDontSee($media->url, false);
    }

    public function test_max_5_files_per_review_enforced(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $product = $this->product();

        $files = [];
        for ($i = 0; $i < 6; $i++) {
            $files[] = UploadedFile::fake()->image("photo{$i}.jpg");
        }

        $response = $this->actingAs($user)->post(route('account.reviews.store'), [
            'product_id' => $product->id,
            'rating' => 4,
            'media' => $files,
        ]);

        $response->assertSessionHasErrors('media');
    }

    public function test_invalid_file_type_rejected(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $product = $this->product();

        $response = $this->actingAs($user)->post(route('account.reviews.store'), [
            'product_id' => $product->id,
            'rating' => 4,
            'media' => [
                UploadedFile::fake()->create('malware.exe', 100),
            ],
        ]);

        $response->assertSessionHasErrors('media.0');
    }

    public function test_verified_purchase_badge_set_correctly(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $product = $this->product();

        $order = Order::factory()->create(['user_id' => $user->id, 'order_status' => 'delivered']);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku ?? 'SKU-1',
            'unit_price' => 100,
            'quantity' => 1,
            'line_total' => 100,
        ]);

        $response = $this->actingAs($user)->post(route('account.reviews.store'), [
            'product_id' => $product->id,
            'rating' => 5,
        ]);

        $response->assertRedirect();
        $review = Review::first();
        $this->assertTrue($review->is_verified_purchase);

        // A second user with no delivered order gets no verified badge.
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser)->post(route('account.reviews.store'), [
            'product_id' => $product->id,
            'rating' => 3,
        ]);

        $unverifiedReview = Review::where('user_id', $otherUser->id)->first();
        $this->assertFalse($unverifiedReview->is_verified_purchase);
    }

    public function test_review_helpfulness_vote_increments_count(): void
    {
        $review = Review::factory()->create(['is_approved' => true]);

        $response = $this->postJson(route('reviews.vote', $review), ['vote' => 'up']);

        $response->assertOk();
        $response->assertJson(['success' => true, 'helpful_count' => 1, 'unhelpful_count' => 0]);
        $this->assertEquals(1, $review->fresh()->helpful_count);
    }

    public function test_duplicate_vote_prevented(): void
    {
        $review = Review::factory()->create(['is_approved' => true]);
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('reviews.vote', $review), ['vote' => 'up']);
        $this->actingAs($user)->postJson(route('reviews.vote', $review), ['vote' => 'up']);

        $this->assertEquals(1, $review->fresh()->helpful_count);
        $this->assertEquals(1, $review->votes()->count());
    }
}
