<?php

namespace App\Policies;

use App\Models\PaymentGateway;
use App\Models\User;
use App\Policies\Concerns\BypassesForLegacyAdmin;

/**
 * Payment gateway credentials are as sensitive as user management —
 * super_admin-only, no manager/staff permission grants access here.
 */
class PaymentGatewayPolicy
{
    use BypassesForLegacyAdmin;

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, PaymentGateway $paymentGateway): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, PaymentGateway $paymentGateway): bool
    {
        return false;
    }

    public function delete(User $user, PaymentGateway $paymentGateway): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
