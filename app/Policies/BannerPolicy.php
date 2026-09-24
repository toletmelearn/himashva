<?php

namespace App\Policies;

use App\Models\Banner;
use App\Models\User;
use App\Policies\Concerns\BypassesForLegacyAdmin;

class BannerPolicy
{
    use BypassesForLegacyAdmin;

    public function viewAny(User $user): bool
    {
        return $user->can('cms.manage');
    }

    public function view(User $user, Banner $banner): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('cms.manage');
    }

    public function update(User $user, Banner $banner): bool
    {
        return $user->can('cms.manage');
    }

    public function delete(User $user, Banner $banner): bool
    {
        return $user->can('cms.manage');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('cms.manage');
    }
}
