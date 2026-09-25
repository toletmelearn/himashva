<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
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

    protected function staff(): User
    {
        $user = User::factory()->create(['is_admin' => false]);
        $user->assignRole('staff');

        return $user;
    }

    public function test_manager_can_access_panel_and_products(): void
    {
        $manager = $this->manager();

        $this->actingAs($manager)->get('/admin')->assertOk();
        $this->actingAs($manager)->get('/admin/products')->assertOk();
        $this->actingAs($manager)->get('/admin/orders')->assertOk();
    }

    public function test_manager_cannot_access_users_resource(): void
    {
        $this->actingAs($this->manager())->get('/admin/users')->assertForbidden();
    }

    public function test_staff_can_access_panel_and_view_orders(): void
    {
        $staff = $this->staff();

        $this->actingAs($staff)->get('/admin')->assertOk();
        $this->actingAs($staff)->get('/admin/orders')->assertOk();
    }

    public function test_staff_cannot_access_products_resource(): void
    {
        $this->actingAs($this->staff())->get('/admin/products')->assertForbidden();
    }

    public function test_staff_cannot_access_users_resource(): void
    {
        $this->actingAs($this->staff())->get('/admin/users')->assertForbidden();
    }

    public function test_staff_can_open_order_edit_page_to_update_status(): void
    {
        $order = Order::factory()->create();

        $this->actingAs($this->staff())
            ->get("/admin/orders/{$order->id}/edit")
            ->assertOk();
    }

    public function test_super_admin_role_has_full_access(): void
    {
        $superAdmin = User::factory()->create(['is_admin' => false]);
        $superAdmin->assignRole('super_admin');

        $this->actingAs($superAdmin)->get('/admin/users')->assertOk();
        $this->actingAs($superAdmin)->get('/admin/products')->assertOk();
        $this->actingAs($superAdmin)->get('/admin/audit-logs')->assertOk();
        $this->actingAs($superAdmin)->get('/admin/payment-gateways')->assertOk();
        $this->actingAs($superAdmin)->get('/admin/shipping-providers')->assertOk();
    }

    public function test_legacy_is_admin_user_retains_full_access_without_roles(): void
    {
        $legacyAdmin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($legacyAdmin)->get('/admin/users')->assertOk();
        $this->actingAs($legacyAdmin)->get('/admin/products')->assertOk();
        $this->actingAs($legacyAdmin)->get('/admin/audit-logs')->assertOk();
    }

    public function test_manager_and_staff_cannot_access_audit_log(): void
    {
        $this->actingAs($this->manager())->get('/admin/audit-logs')->assertForbidden();
        $this->actingAs($this->staff())->get('/admin/audit-logs')->assertForbidden();
    }

    public function test_manager_can_access_cms_resources_but_staff_cannot(): void
    {
        $manager = $this->manager();
        $staff = $this->staff();

        foreach (['/admin/banners', '/admin/pages', '/admin/newsletter-subscribers', '/admin/contact-messages', '/admin/chatbot-conversations'] as $path) {
            $this->actingAs($manager)->get($path)->assertOk();
            $this->actingAs($staff)->get($path)->assertForbidden();
        }
    }

    public function test_manager_can_access_inventory_movements_but_staff_cannot(): void
    {
        $this->actingAs($this->manager())->get('/admin/inventory-movements')->assertOk();
        $this->actingAs($this->staff())->get('/admin/inventory-movements')->assertForbidden();
    }
}
