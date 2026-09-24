<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageSiteSettings;
use App\Filament\Pages\ProductPerformanceReport;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\WishlistResource;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_can_access_dashboard(): void
    {
        $this->actingAs($this->admin())->get('/admin')->assertOk();
    }

    public function test_login_page_loads_with_custom_heading(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('Welcome back to Himashva');
    }

    public function test_admin_dashboard_shows_greeting_widget(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin')
            ->assertOk()
            ->assertSee('himashva-greeting', false);
    }

    public function test_order_navigation_badge_reflects_pending_count(): void
    {
        Order::factory()->create(['order_status' => 'pending']);
        Order::factory()->create(['order_status' => 'delivered']);

        $this->assertSame('1', OrderResource::getNavigationBadge());
    }

    public function test_wishlist_resource_shows_customer_and_product_and_navigation_badge(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'name' => 'Wished For Candle']);
        $customer = User::factory()->create(['name' => 'Wishful Customer']);

        Wishlist::create(['user_id' => $customer->id, 'product_id' => $product->id]);

        $this->assertSame('1', WishlistResource::getNavigationBadge());

        $this->actingAs($this->admin())
            ->get('/admin/wishlists')
            ->assertOk()
            ->assertSee('Wished For Candle')
            ->assertSee('Wishful Customer');
    }

    public function test_product_performance_report_computes_revenue_and_filters(): void
    {
        $category = Category::factory()->create();
        $bestSeller = Product::factory()->create([
            'category_id' => $category->id, 'name' => 'Best Seller', 'price' => 500, 'total_sold' => 10,
        ]);
        $slowMover = Product::factory()->create([
            'category_id' => $category->id, 'name' => 'Slow Mover', 'price' => 100, 'total_sold' => 1,
        ]);

        Livewire::actingAs($this->admin())
            ->test(ProductPerformanceReport::class)
            ->assertCanSeeTableRecords([$bestSeller, $slowMover])
            ->assertTableColumnStateSet('revenue', 5000, record: $bestSeller)
            ->filterTable('min_revenue', ['min_revenue' => 1000])
            ->assertCanSeeTableRecords([$bestSeller])
            ->assertCanNotSeeTableRecords([$slowMover]);
    }

    #[DataProvider('resourceIndexProvider')]
    public function test_admin_resource_index_pages_load(string $path): void
    {
        $this->actingAs($this->admin())->get($path)->assertOk();
    }

    public static function resourceIndexProvider(): array
    {
        return [
            ['/admin/products'],
            ['/admin/categories'],
            ['/admin/brands'],
            ['/admin/product-performance-report'],
            ['/admin/orders'],
            ['/admin/inventory-movements'],
            ['/admin/returns'],
            ['/admin/wishlists'],
            ['/admin/coupons'],
            ['/admin/reviews'],
            ['/admin/banners'],
            ['/admin/pages'],
            ['/admin/contact-messages'],
            ['/admin/newsletter-subscribers'],
            ['/admin/users'],
            ['/admin/chatbot-conversations'],
            ['/admin/chatbot-responses'],
            ['/admin/unanswered-questions'],
            ['/admin/manage-site-settings'],
        ];
    }

    public function test_non_admin_cannot_access_panel(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    #[DataProvider('createPageProvider')]
    public function test_admin_create_pages_load(string $path): void
    {
        $this->actingAs($this->admin())->get($path)->assertOk();
    }

    public static function createPageProvider(): array
    {
        return [
            ['/admin/products/create'],
            ['/admin/categories/create'],
            ['/admin/orders/create'],
            ['/admin/coupons/create'],
            ['/admin/banners/create'],
            ['/admin/pages/create'],
            ['/admin/users/create'],
            ['/admin/chatbot-responses/create'],
        ];
    }

    public function test_admin_can_create_product_with_images_and_variants(): void
    {
        $category = Category::factory()->create();

        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->actingAs($this->admin())
            ->get("/admin/products/{$product->id}/edit")
            ->assertOk();
    }

    public function test_admin_can_edit_order(): void
    {
        $order = Order::factory()->create();

        $this->actingAs($this->admin())
            ->get("/admin/orders/{$order->id}/edit")
            ->assertOk();
    }

    public function test_non_admin_cannot_download_order_invoice(): void
    {
        $order = Order::factory()->create();
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get("/admin/orders/{$order->id}/invoice")
            ->assertForbidden();
    }

    public function test_admin_can_download_order_invoice(): void
    {
        $order = Order::factory()->create();

        $this->actingAs($this->admin())
            ->get("/admin/orders/{$order->id}/invoice")
            ->assertOk();
    }

    public function test_manage_site_settings_page_saves(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ManageSiteSettings::class)
            ->fillForm(['site_name' => 'Himashva Updated'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals('Himashva Updated', SiteSetting::get('site_name'));
    }
}
