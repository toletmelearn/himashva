<?php

namespace App\Policies;

use App\Models\SizeGuide;
use App\Models\User;
use App\Policies\Concerns\BypassesForLegacyAdmin;

class SizeGuidePolicy
{
    use BypassesForLegacyAdmin;

    public function viewAny(User $user): bool
    {
        return $user->can('cms.manage');
    }

    public function view(User $user, SizeGuide $sizeGuide): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('cms.manage');
    }

    public function update(User $user, SizeGuide $sizeGuide): bool
    {
        return $user->can('cms.manage');
    }

    public function delete(User $user, SizeGuide $sizeGuide): bool
    {
        return $user->can('cms.manage');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('cms.manage');
    }
}
