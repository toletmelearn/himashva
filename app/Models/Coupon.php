<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'code', 'type', 'value', 'min_order_amount', 'max_discount_amount',
        'max_uses', 'used_count', 'per_user_limit', 'starts_at', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function isValid(?float $subtotal = null): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses && $this->used_count >= $this->max_uses) {
            return false;
        }

        if ($subtotal !== null && $this->min_order_amount && $subtotal < $this->min_order_amount) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $amount): float
    {
        $discount = $this->type === 'percentage'
            ? $amount * ((float) $this->value / 100)
            : (float) $this->value;

        if ($this->max_discount_amount) {
            $discount = min($discount, (float) $this->max_discount_amount);
        }

        return round(min($discount, $amount), 2);
    }
}
