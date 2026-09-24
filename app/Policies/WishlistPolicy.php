<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wishlist;
use App\Policies\Concerns\BypassesForLegacyAdmin;

class WishlistPolicy
{
    use BypassesForLegacyAdmin;

    public function viewAny(User $user): bool
    {
        return $user->can('orders.view') || $user->can('orders.manage');
    }

    public function view(User $user, Wishlist $wishlist): bool
    {
        return $this->viewAny($user);
    }
}
