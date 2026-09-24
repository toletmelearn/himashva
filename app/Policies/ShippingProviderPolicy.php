<?php

namespace App\Policies;

use App\Models\ShippingProvider;
use App\Models\User;
use App\Policies\Concerns\BypassesForLegacyAdmin;

/**
 * Scoped the same as PaymentGatewayPolicy: shipping provider credentials
 * (API keys, pickup addresses) are treated as sensitive as payment
 * credentials, so this is super_admin-only.
 */
class ShippingProviderPolicy
{
    use BypassesForLegacyAdmin;

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, ShippingProvider $shippingProvider): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, ShippingProvider $shippingProvider): bool
    {
        return false;
    }

    public function delete(User $user, ShippingProvider $shippingProvider): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
