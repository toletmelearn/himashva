<?php

namespace App\Policies\Concerns;

use App\Models\User;

/**
 * Preserves pre-RBAC behaviour: any user with the legacy `is_admin` flag
 * (or the new super_admin role) keeps full, unrestricted access everywhere.
 * All other authorization checks in the policy only apply to users who
 * have been assigned a scoped role (manager/staff) without this bypass.
 */
trait BypassesForLegacyAdmin
{
    public function before(User $user): ?bool
    {
        if ($user->isAdmin() || $user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }
}
