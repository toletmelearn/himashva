<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;
use App\Policies\Concerns\BypassesForLegacyAdmin;

/**
 * Audit log visibility is super_admin-only (via the legacy-admin bypass) —
 * not part of the manager/staff permission set.
 */
class AuditLogPolicy
{
    use BypassesForLegacyAdmin;

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, AuditLog $auditLog): bool
    {
        return false;
    }
}
