<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class CouponService
{
    public function validate(string $code, float $subtotal): array
    {
        $coupon = Coupon::whereRaw('LOWER(code) = ?', [strtolower($code)])->first();

        if (! $coupon) {
            return [false, 'Invalid coupon code.', null];
        }

        if (! $coupon->isValid($subtotal)) {
            return [false, 'This coupon is not valid or has expired.', null];
        }

        if (Auth::check()) {
            $usedCount = Order::where('user_id', Auth::id())
                ->where('coupon_id', $coupon->id)
                ->whereIn('payment_status', ['paid', 'pending'])
                ->count();

            if ($usedCount >= $coupon->per_user_limit) {
                return [false, 'You have already used this coupon.', null];
            }
        }

        return [true, null, $coupon];
    }

    public function calculateDiscount(Coupon $coupon, float $subtotal): float
    {
        return $coupon->calculateDiscount($subtotal);
    }

    public function incrementUsage(int $couponId): void
    {
        Coupon::where('id', $couponId)->increment('used_count');
    }
}
