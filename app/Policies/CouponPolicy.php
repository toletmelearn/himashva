<?php

namespace App\Policies;

use App\Models\Coupon;
use App\Models\User;
use App\Policies\Concerns\BypassesForLegacyAdmin;

class CouponPolicy
{
    use BypassesForLegacyAdmin;

    public function viewAny(User $user): bool
    {
        return $user->can('coupons.view') || $user->can('coupons.manage');
    }

    public function view(User $user, Coupon $coupon): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('coupons.manage');
    }

    public function update(User $user, Coupon $coupon): bool
    {
        return $user->can('coupons.manage');
    }

    public function delete(User $user, Coupon $coupon): bool
    {
        return $user->can('coupons.manage');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('coupons.manage');
    }
}
