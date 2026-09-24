<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Phase 1 RBAC covered products/categories/brands/orders/coupons/reviews
     * (WishlistResource and ReturnResource also reuse `orders.*` since they're
     * order-adjacent). Phase 2 adds `cms.manage` (banners, pages, newsletter
     * subscribers, contact messages, chatbot conversations) and
     * `inventory.view` (stock history). AuditLog, Users, and Site Settings
     * remain super_admin-only — not covered by this permission set.
     */
    protected array $permissions = [
        'products.view', 'products.manage',
        'categories.view', 'categories.manage',
        'brands.view', 'brands.manage',
        'orders.view', 'orders.manage', 'orders.update_status',
        'coupons.view', 'coupons.manage',
        'reviews.view', 'reviews.manage',
        'cms.manage', 'inventory.view',
    ];

    public function run(): void
    {
        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions($this->permissions);

        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'products.view', 'products.manage',
            'categories.view', 'categories.manage',
            'brands.view', 'brands.manage',
            'orders.view', 'orders.manage', 'orders.update_status',
            'coupons.view', 'coupons.manage',
            'reviews.view', 'reviews.manage',
            'cms.manage', 'inventory.view',
        ]);

        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $staff->syncPermissions([
            'orders.view', 'orders.update_status',
        ]);

        // Backfill: existing legacy admins (is_admin = true) get the super_admin
        // role so they show up correctly in the new RBAC system going forward.
        // is_admin remains the source of truth for full-access bypass in Policies
        // (see App\Policies\*::before()), so this backfill does not change any
        // existing user's access — it only keeps the role table in sync.
        User::where('is_admin', true)->each(function (User $user) use ($superAdmin) {
            $user->assignRole($superAdmin);
        });
    }
}
