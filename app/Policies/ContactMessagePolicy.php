<?php

namespace App\Policies;

use App\Models\ContactMessage;
use App\Models\User;
use App\Policies\Concerns\BypassesForLegacyAdmin;

class ContactMessagePolicy
{
    use BypassesForLegacyAdmin;

    public function viewAny(User $user): bool
    {
        return $user->can('cms.manage');
    }

    public function view(User $user, ContactMessage $contactMessage): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, ContactMessage $contactMessage): bool
    {
        return $user->can('cms.manage');
    }

    public function delete(User $user, ContactMessage $contactMessage): bool
    {
        return $user->can('cms.manage');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('cms.manage');
    }
}
