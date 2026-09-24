<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LowStockAlertCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // SiteSetting caches values in a static property that RefreshDatabase
        // doesn't reset between tests — clear it so each test starts blank.
        $cached = new \ReflectionProperty(SiteSetting::class, 'cached');
        $cached->setAccessible(true);
        $cached->setValue(null, null);
    }

    public function test_reports_no_low_stock_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id, 'stock' => 50, 'low_stock_threshold' => 5, 'status' => 'active']);

        $this->artisan('himashva:low-stock-alert')
            ->expectsOutputToContain('No low-stock products found.')
            ->assertExitCode(0);
    }

    public function test_sends_summary_for_low_stock_active_products(): void
    {
        SiteSetting::updateOrCreate(['key' => 'contact_email'], ['value' => 'admin@himashva.com', 'group' => 'general']);

        $category = Category::factory()->create();
        Product::factory()->create([
            'category_id' => $category->id, 'stock' => 2, 'low_stock_threshold' => 5,
            'status' => 'active', 'name' => 'Low Stock Candle',
        ]);
        Product::factory()->create(['category_id' => $category->id, 'stock' => 50, 'low_stock_threshold' => 5, 'status' => 'active']);
        // Non-active product below threshold should be excluded.
        Product::factory()->create(['category_id' => $category->id, 'stock' => 1, 'low_stock_threshold' => 5, 'status' => 'draft']);

        $this->artisan('himashva:low-stock-alert')
            ->expectsOutputToContain('Low stock alert sent for 1 product(s).')
            ->assertExitCode(0);
    }

    public function test_skips_when_no_contact_email_configured(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id, 'stock' => 1, 'low_stock_threshold' => 5, 'status' => 'active']);

        $this->artisan('himashva:low-stock-alert')
            ->expectsOutputToContain('No contact_email configured')
            ->assertExitCode(1);
    }
}
