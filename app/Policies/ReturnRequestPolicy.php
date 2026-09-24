<?php

namespace App\Policies;

use App\Models\ReturnRequest;
use App\Models\User;
use App\Policies\Concerns\BypassesForLegacyAdmin;

/**
 * Returns are part of order management, so they follow the same
 * permission set as OrderPolicy.
 */
class ReturnRequestPolicy
{
    use BypassesForLegacyAdmin;

    public function viewAny(User $user): bool
    {
        return $user->can('orders.view') || $user->can('orders.manage') || $user->can('orders.update_status');
    }

    public function view(User $user, ReturnRequest $returnRequest): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, ReturnRequest $returnRequest): bool
    {
        return $user->can('orders.manage') || $user->can('orders.update_status');
    }

    public function delete(User $user, ReturnRequest $returnRequest): bool
    {
        return $user->can('orders.manage');
    }
}
