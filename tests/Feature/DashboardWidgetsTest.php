<?php

namespace Tests\Feature;

use App\Filament\Widgets\TopProducts;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

class DashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_dashboard_loads_with_all_widgets(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin')
            ->assertOk()
            ->assertSee('Quick Access');
    }

    public function test_quick_access_widget_shows_correct_counts(): void
    {
        Product::factory()->count(3)->create();
        User::factory()->count(2)->create(['is_admin' => false]);
        Order::factory()->create(['created_at' => now()]);
        Coupon::create([
            'code' => 'SAVE10',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
        ]);

        $expectedProducts = (string) Product::count();
        $expectedCustomers = (string) User::where('is_admin', false)->count();

        $this->actingAs($this->admin())
            ->get('/admin')
            ->assertOk()
            ->assertSee($expectedProducts)
            ->assertSee($expectedCustomers);
    }

    public function test_business_stats_widget_shows_real_data(): void
    {
        Order::factory()->create([
            'payment_status' => 'paid',
            'total' => 500,
            'created_at' => now(),
        ]);
        Order::factory()->create([
            'payment_status' => 'paid',
            'total' => 1500,
            'created_at' => now(),
        ]);

        $expectedRevenue = '₹'.number_format((float) Order::where('payment_status', 'paid')->sum('total'), 2);
        $expectedTodayOrders = (string) Order::whereDate('created_at', today())->count();

        $this->actingAs($this->admin())
            ->get('/admin')
            ->assertOk()
            ->assertSee($expectedRevenue)
            ->assertSee($expectedTodayOrders);
    }

    public function test_top_selling_products_widget_prefers_real_photo_over_ai_placeholder(): void
    {
        $product = Product::factory()->create(['total_sold' => 5]);
        $product->images()->create(['image_path' => 'placeholder.jpg', 'image_type' => 'ai_generated', 'sort_order' => 0, 'is_primary' => true]);
        $product->images()->create(['image_path' => 'products/real-photo.jpg', 'image_type' => 'real', 'sort_order' => 0, 'is_primary' => false]);

        $widget = new TopProducts;
        $method = new ReflectionMethod($widget, 'getViewData');
        $method->setAccessible(true);
        $data = $method->invoke($widget);

        $this->assertSame('products/real-photo.jpg', $data['products'][0]['image']);
    }
}
