<?php

namespace App\Policies;

use App\Models\Brand;
use App\Models\User;
use App\Policies\Concerns\BypassesForLegacyAdmin;

class BrandPolicy
{
    use BypassesForLegacyAdmin;

    public function viewAny(User $user): bool
    {
        return $user->can('brands.view') || $user->can('brands.manage');
    }

    public function view(User $user, Brand $brand): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('brands.manage');
    }

    public function update(User $user, Brand $brand): bool
    {
        return $user->can('brands.manage');
    }

    public function delete(User $user, Brand $brand): bool
    {
        return $user->can('brands.manage');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('brands.manage');
    }
}
