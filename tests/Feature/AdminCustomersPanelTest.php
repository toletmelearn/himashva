<?php

namespace Tests\Feature;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\RelationManagers\OrdersRelationManager;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminCustomersPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_can_view_customers_list(): void
    {
        $admin = $this->admin();
        User::factory()->create(['is_admin' => false, 'name' => 'Alice Customer']);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertOk();
        $response->assertSee('Alice Customer');
    }

    public function test_customer_list_excludes_admins(): void
    {
        $admin = $this->admin();
        User::factory()->create(['is_admin' => true, 'name' => 'Other Admin']);
        User::factory()->create(['is_admin' => false, 'name' => 'Real Customer']);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertOk();
        $response->assertSee('Real Customer');
        $response->assertDontSee('Other Admin');
    }

    public function test_admin_can_create_customer_account(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(CreateUser::class)
            ->fillForm([
                'name' => 'New Customer',
                'email' => 'newcustomer@example.com',
                'phone' => '9876543210',
                'password' => 'password123',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', ['email' => 'newcustomer@example.com', 'is_admin' => false]);
    }

    public function test_navigation_badge_shows_non_admin_customer_count(): void
    {
        User::factory()->create(['is_admin' => false]);
        User::factory()->create(['is_admin' => false]);
        User::factory()->create(['is_admin' => true]);

        $this->assertSame('2', UserResource::getNavigationBadge());
    }

    public function test_customer_order_history_visible_via_orders_relation_manager(): void
    {
        $admin = $this->admin();
        $customer = User::factory()->create(['is_admin' => false]);
        $order = Order::factory()->create(['user_id' => $customer->id, 'order_number' => 'HMV-20260101-0001']);

        Livewire::actingAs($admin)
            ->test(OrdersRelationManager::class, [
                'ownerRecord' => $customer,
                'pageClass' => EditUser::class,
            ])
            ->assertCanSeeTableRecords([$order]);
    }
}
