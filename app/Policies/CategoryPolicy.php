<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use App\Policies\Concerns\BypassesForLegacyAdmin;

class CategoryPolicy
{
    use BypassesForLegacyAdmin;

    public function viewAny(User $user): bool
    {
        return $user->can('categories.view') || $user->can('categories.manage');
    }

    public function view(User $user, Category $category): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('categories.manage');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->can('categories.manage');
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->can('categories.manage');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('categories.manage');
    }
}
