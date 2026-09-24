<?php

namespace App\Policies;

use App\Models\InventoryMovement;
use App\Models\User;
use App\Policies\Concerns\BypassesForLegacyAdmin;

class InventoryMovementPolicy
{
    use BypassesForLegacyAdmin;

    public function viewAny(User $user): bool
    {
        return $user->can('inventory.view');
    }

    public function view(User $user, InventoryMovement $inventoryMovement): bool
    {
        return $this->viewAny($user);
    }
}
