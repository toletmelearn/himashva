<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;
use App\Policies\Concerns\BypassesForLegacyAdmin;

class PagePolicy
{
    use BypassesForLegacyAdmin;

    public function viewAny(User $user): bool
    {
        return $user->can('cms.manage');
    }

    public function view(User $user, Page $page): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('cms.manage');
    }

    public function update(User $user, Page $page): bool
    {
        return $user->can('cms.manage');
    }

    public function delete(User $user, Page $page): bool
    {
        return $user->can('cms.manage');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('cms.manage');
    }
}
