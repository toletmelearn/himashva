<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use App\Policies\Concerns\BypassesForLegacyAdmin;

class OrderPolicy
{
    use BypassesForLegacyAdmin;

    public function viewAny(User $user): bool
    {
        return $user->can('orders.view') || $user->can('orders.manage') || $user->can('orders.update_status');
    }

    public function view(User $user, Order $order): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('orders.manage');
    }

    /**
     * Staff (orders.update_status only, no orders.manage) may reach the edit
     * page to change order status — OrderResource's form restricts which
     * fields they can actually change once there.
     */
    public function update(User $user, Order $order): bool
    {
        return $user->can('orders.manage') || $user->can('orders.update_status');
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->can('orders.manage');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('orders.manage');
    }
}
