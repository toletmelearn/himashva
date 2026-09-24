<?php

namespace Tests\Feature;

use App\Filament\Resources\OrderResource\Pages\EditOrder;
use App\Filament\Resources\ReturnResource\Pages\EditReturn;
use App\Mail\OrderConfirmation;
use App\Mail\OrderDelivered;
use App\Mail\OrderShipped;
use App\Mail\ReturnStatusUpdate;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ReturnRequest;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class OrderEmailsTest extends TestCase
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

    protected function placeOrder(User $user, Product $product, int $quantity = 1): Order
    {
        $this->actingAs($user)->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => $quantity])->assertOk();

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

        return Order::firstOrFail();
    }

    public function test_order_confirmation_email_sent_on_order_creation(): void
    {
        Mail::fake();

        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'stock' => 10]);
        $user = User::factory()->create();

        $order = $this->placeOrder($user, $product);

        Mail::assertSent(OrderConfirmation::class, fn ($mail) => $mail->hasTo($order->email) && $mail->order->id === $order->id);
    }

    public function test_order_shipped_email_sent_on_status_change(): void
    {
        Mail::fake();

        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'stock' => 10]);
        $order = Order::factory()->create(['order_status' => 'processing']);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'unit_price' => $product->price,
            'quantity' => 1,
            'line_total' => $product->price,
        ]);

        Livewire::actingAs($this->admin())
            ->test(EditOrder::class, ['record' => $order->id])
            ->fillForm(['order_status' => 'shipped'])
            ->call('save')
            ->assertHasNoFormErrors();

        Mail::assertSent(OrderShipped::class, fn ($mail) => $mail->hasTo($order->email));
    }

    public function test_order_delivered_email_sent_on_status_change(): void
    {
        Mail::fake();

        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'stock' => 10]);
        $order = Order::factory()->create(['order_status' => 'shipped']);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'unit_price' => $product->price,
            'quantity' => 1,
            'line_total' => $product->price,
        ]);

        Livewire::actingAs($this->admin())
            ->test(EditOrder::class, ['record' => $order->id])
            ->fillForm(['order_status' => 'delivered'])
            ->call('save')
            ->assertHasNoFormErrors();

        Mail::assertSent(OrderDelivered::class, fn ($mail) => $mail->hasTo($order->email));
    }

    public function test_return_status_email_sent_on_update(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $return = ReturnRequest::factory()->create(['order_id' => $order->id, 'user_id' => $user->id, 'status' => 'requested']);

        Livewire::actingAs($this->admin())
            ->test(EditReturn::class, ['record' => $return->id])
            ->fillForm(['status' => 'approved'])
            ->call('save')
            ->assertHasNoFormErrors();

        Mail::assertSent(ReturnStatusUpdate::class, fn ($mail) => $mail->hasTo($order->email) && $mail->return->status === 'approved');
    }

    public function test_emails_not_sent_when_setting_disabled(): void
    {
        SiteSetting::updateOrCreate(['key' => 'send_order_emails'], ['value' => '0', 'group' => 'email']);

        Mail::fake();

        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'stock' => 10]);
        $user = User::factory()->create();

        $this->placeOrder($user, $product);

        Mail::assertNothingSent();
    }

    public function test_order_confirmation_email_contains_order_details(): void
    {
        Mail::fake();

        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'stock' => 10, 'price' => 499]);
        $user = User::factory()->create();

        $order = $this->placeOrder($user, $product);

        Mail::assertSent(OrderConfirmation::class, function ($mail) use ($order, $product) {
            $html = $mail->render();

            return str_contains($html, $order->order_number)
                && str_contains($html, $product->name)
                && str_contains($html, format_price($order->total));
        });
    }
}
