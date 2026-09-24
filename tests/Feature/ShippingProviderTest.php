<?php

namespace Tests\Feature;

use App\Models\ShippingProvider;
use App\Services\Shipping\Drivers\ManualDriver;
use App\Services\Shipping\Drivers\ShiprocketDriver;
use App\Services\Shipping\ShippingManager;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ShippingProviderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_can_create_shipping_provider(): void
    {
        $provider = ShippingProvider::create([
            'name' => 'delhivery', 'display_name' => 'Delhivery', 'driver' => 'delhivery',
            'credentials' => ['api_key' => 'xyz'], 'is_active' => false, 'is_test_mode' => true,
            'settings' => [], 'supported_features' => ['tracking'], 'display_order' => 2,
        ]);

        $this->assertDatabaseHas('shipping_providers', ['name' => 'delhivery']);
        $this->assertEquals('xyz', $provider->fresh()->getCredential('api_key'));
    }

    public function test_admin_can_toggle_active_status(): void
    {
        $provider = ShippingProvider::factory()->create(['is_active' => false]);

        $provider->update(['is_active' => true]);

        $this->assertTrue($provider->fresh()->is_active);
    }

    public function test_credentials_encrypted_in_db(): void
    {
        ShippingProvider::create([
            'name' => 'shiprocket', 'display_name' => 'Shiprocket', 'driver' => 'shiprocket',
            'credentials' => ['password' => 'super-secret-password'], 'is_active' => false,
            'is_test_mode' => true, 'settings' => [], 'supported_features' => [], 'display_order' => 1,
        ]);

        $raw = DB::table('shipping_providers')->where('name', 'shiprocket')->value('credentials');

        $this->assertStringNotContainsString('super-secret-password', $raw);
    }

    public function test_manual_driver_always_returns_available(): void
    {
        $provider = ShippingProvider::factory()->create(['driver' => 'manual']);
        $driver = new ManualDriver;

        $result = $driver->checkPincode('110001', $provider);

        $this->assertTrue($result->available);
    }

    public function test_manager_resolves_correct_driver_class(): void
    {
        $manager = app(ShippingManager::class);

        $this->assertInstanceOf(ShiprocketDriver::class, $manager->driver('shiprocket'));
        $this->assertInstanceOf(ManualDriver::class, $manager->driver('manual'));
    }

    public function test_pincode_check_uses_active_provider(): void
    {
        ShippingProvider::factory()->create(['name' => 'inactive-one', 'is_active' => false, 'display_order' => 1]);
        $active = ShippingProvider::factory()->create(['name' => 'active-one', 'driver' => 'manual', 'is_active' => true, 'display_order' => 5]);

        $manager = app(ShippingManager::class);

        $this->assertEquals($active->id, $manager->activeProvider()->id);

        $response = $this->postJson(route('check.pincode'), ['pincode' => '400001']);

        $response->assertOk();
        $response->assertJsonPath('serviceable', true);
    }
}
