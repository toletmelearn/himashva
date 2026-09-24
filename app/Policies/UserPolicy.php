<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\BypassesForLegacyAdmin;

/**
 * User management is super_admin-only for now (per Phase 1 RBAC scope:
 * manager/staff do not get settings/users access). No manager/staff
 * permission grants access here — only the legacy-admin bypass does.
 */
class UserPolicy
{
    use BypassesForLegacyAdmin;

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, User $model): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, User $model): bool
    {
        return false;
    }

    public function delete(User $user, User $model): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
