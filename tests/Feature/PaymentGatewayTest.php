<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\User;
use App\Services\Payment\Drivers\CodDriver;
use App\Services\Payment\Drivers\RazorpayDriver;
use App\Services\Payment\PaymentGatewayManager;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function superAdmin(): User
    {
        $user = User::factory()->create(['is_admin' => false]);
        $user->assignRole('super_admin');

        return $user;
    }

    public function test_admin_can_create_payment_gateway(): void
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin);

        $gateway = PaymentGateway::create([
            'name' => 'payu',
            'display_name' => 'PayU',
            'driver' => 'payu',
            'credentials' => ['merchant_key' => 'abc'],
            'is_active' => false,
            'is_test_mode' => true,
            'supported_methods' => ['card'],
            'display_order' => 5,
        ]);

        $this->assertDatabaseHas('payment_gateways', ['name' => 'payu']);
        $this->assertEquals('abc', $gateway->fresh()->getCredential('merchant_key'));
    }

    public function test_admin_can_toggle_active_status(): void
    {
        $gateway = PaymentGateway::create([
            'name' => 'cashfree', 'display_name' => 'Cashfree', 'driver' => 'cashfree',
            'credentials' => [], 'is_active' => false, 'is_test_mode' => true,
            'supported_methods' => [], 'display_order' => 3,
        ]);

        $gateway->update(['is_active' => true]);

        $this->assertTrue($gateway->fresh()->is_active);
    }

    public function test_cod_gateway_works_without_credentials(): void
    {
        $gateway = PaymentGateway::create([
            'name' => 'cod', 'display_name' => 'Cash on Delivery', 'driver' => 'cod',
            'credentials' => [], 'is_active' => true, 'is_test_mode' => false,
            'supported_methods' => ['cod'], 'display_order' => 10,
        ]);

        $order = Order::factory()->create(['total' => 500]);

        $driver = new CodDriver;
        $payload = $driver->createOrder($order, $gateway);

        $this->assertTrue(filled($payload['order_id']));
        $this->assertEquals('checkout.partials.cod', $driver->getCheckoutView());
    }

    public function test_inactive_gateways_not_shown_at_checkout(): void
    {
        PaymentGateway::create([
            'name' => 'instamojo', 'display_name' => 'Instamojo', 'driver' => 'instamojo',
            'credentials' => [], 'is_active' => false, 'is_test_mode' => true,
            'supported_methods' => [], 'display_order' => 4,
        ]);

        PaymentGateway::create([
            'name' => 'cod', 'display_name' => 'COD', 'driver' => 'cod',
            'credentials' => [], 'is_active' => true, 'is_test_mode' => false,
            'supported_methods' => ['cod'], 'display_order' => 10,
        ]);

        $manager = app(PaymentGatewayManager::class);
        $active = $manager->activeGateways();

        $this->assertCount(1, $active);
        $this->assertEquals('cod', $active->first()->name);
    }

    public function test_credentials_are_encrypted_in_db(): void
    {
        PaymentGateway::create([
            'name' => 'razorpay', 'display_name' => 'Razorpay', 'driver' => 'razorpay',
            'credentials' => ['key_id' => 'rzp_test_secretvalue'], 'is_active' => false,
            'is_test_mode' => true, 'supported_methods' => [], 'display_order' => 1,
        ]);

        $raw = DB::table('payment_gateways')->where('name', 'razorpay')->value('credentials');

        $this->assertStringNotContainsString('rzp_test_secretvalue', $raw);
    }

    public function test_manager_resolves_correct_driver_class(): void
    {
        $manager = app(PaymentGatewayManager::class);

        $this->assertInstanceOf(RazorpayDriver::class, $manager->driver('razorpay'));
        $this->assertInstanceOf(CodDriver::class, $manager->driver('cod'));
    }
}
