<?php

namespace Tests\Feature;

use App\Mail\AbandonedCartReminder;
use App\Models\AbandonedCart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AbandonedCartRecoveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // SiteSetting caches settings in a static property that RefreshDatabase's
        // truncation does not invalidate (no model events fire), so a value set
        // by an earlier test can otherwise leak into this one.
        (new \ReflectionClass(SiteSetting::class))->setStaticPropertyValue('cached', null);
    }

    protected function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    protected function product(): Product
    {
        $category = Category::factory()->create();

        return Product::factory()->create(['category_id' => $category->id, 'stock' => 10]);
    }

    public function test_abandoned_cart_snapshot_created_on_cart_add(): void
    {
        $user = User::factory()->create();
        $product = $this->product();

        $this->actingAs($user)->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 2])->assertOk();

        $cart = AbandonedCart::where('user_id', $user->id)->where('status', 'active')->first();

        $this->assertNotNull($cart);
        $this->assertEquals($user->email, $cart->email);
        $this->assertCount(1, $cart->cart_data);
        $this->assertEquals($product->id, $cart->cart_data[0]['product_id']);
        $this->assertEquals(2, $cart->cart_data[0]['quantity']);
    }

    public function test_abandoned_cart_recovered_on_order_placement(): void
    {
        $user = User::factory()->create();
        $product = $this->product();

        $this->actingAs($user)->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 1])->assertOk();

        $this->assertDatabaseHas('abandoned_carts', ['user_id' => $user->id, 'status' => 'active']);

        $this->actingAs($user)->post('/checkout/place-order', [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '9999999999',
            'address_line_1' => '123 Test St',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'postal_code' => '110001',
            'country' => 'India',
            'payment_method' => 'cod',
        ]);

        $order = Order::firstOrFail();
        $cart = AbandonedCart::where('user_id', $user->id)->firstOrFail();

        $this->assertEquals('recovered', $cart->status);
        $this->assertEquals($order->id, $cart->recovered_order_id);
    }

    public function test_reminder_command_sends_emails_to_recoverable_carts(): void
    {
        SiteSetting::updateOrCreate(['key' => 'abandoned_cart_recovery_enabled'], ['value' => '1', 'group' => 'marketing']);

        Mail::fake();

        $cart = AbandonedCart::factory()->create([
            'status' => 'active',
            'reminder_count' => 0,
            'created_at' => now()->subHours(2),
        ]);

        $this->artisan('himashva:abandoned-cart-reminders')->assertSuccessful();

        Mail::assertSent(AbandonedCartReminder::class, fn ($mail) => $mail->hasTo($cart->email) && $mail->cart->id === $cart->id);

        $cart->refresh();
        $this->assertEquals(1, $cart->reminder_count);
        $this->assertEquals('reminded', $cart->status);
        $this->assertNotNull($cart->reminder_sent_at);
    }

    public function test_reminder_command_respects_max_count(): void
    {
        SiteSetting::updateOrCreate(['key' => 'abandoned_cart_recovery_enabled'], ['value' => '1', 'group' => 'marketing']);

        Mail::fake();

        $cart = AbandonedCart::factory()->create([
            'status' => 'reminded',
            'reminder_count' => 2,
            'created_at' => now()->subHours(2),
            'reminder_sent_at' => now()->subDays(2),
        ]);

        $this->artisan('himashva:abandoned-cart-reminders')->assertSuccessful();

        Mail::assertNotSent(AbandonedCartReminder::class);

        $cart->refresh();
        $this->assertEquals(2, $cart->reminder_count);
    }

    public function test_reminder_command_skips_when_disabled(): void
    {
        SiteSetting::updateOrCreate(['key' => 'abandoned_cart_recovery_enabled'], ['value' => '0', 'group' => 'marketing']);

        Mail::fake();

        AbandonedCart::factory()->create([
            'status' => 'active',
            'reminder_count' => 0,
            'created_at' => now()->subHours(2),
        ]);

        $this->artisan('himashva:abandoned-cart-reminders')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_admin_can_view_abandoned_carts_resource(): void
    {
        AbandonedCart::factory()->create();

        $this->actingAs($this->admin())
            ->get('/admin/abandoned-carts')
            ->assertOk();
    }
}
