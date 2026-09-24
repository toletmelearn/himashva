<?php

namespace App\Policies;

use App\Models\AbandonedCart;
use App\Models\User;
use App\Policies\Concerns\BypassesForLegacyAdmin;

class AbandonedCartPolicy
{
    use BypassesForLegacyAdmin;

    public function viewAny(User $user): bool
    {
        return $user->can('orders.view') || $user->can('orders.manage') || $user->can('orders.update_status');
    }

    public function view(User $user, AbandonedCart $abandonedCart): bool
    {
        return $this->viewAny($user);
    }
}
