<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use App\Policies\Concerns\BypassesForLegacyAdmin;

class ProductPolicy
{
    use BypassesForLegacyAdmin;

    public function viewAny(User $user): bool
    {
        return $user->can('products.view') || $user->can('products.manage');
    }

    public function view(User $user, Product $product): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('products.manage');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->can('products.manage');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->can('products.manage');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('products.manage');
    }
}
