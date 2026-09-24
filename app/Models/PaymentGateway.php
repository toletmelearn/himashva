<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'display_name', 'driver', 'credentials', 'is_active', 'is_test_mode',
        'supported_methods', 'display_order', 'description', 'icon',
        'min_order_amount', 'max_order_amount',
    ];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'supported_methods' => 'array',
        'is_active' => 'boolean',
        'is_test_mode' => 'boolean',
        'min_order_amount' => 'decimal:2',
        'max_order_amount' => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getCredential(string $key): ?string
    {
        return $this->credentials[$key] ?? null;
    }

    public function supportsMethod(string $method): bool
    {
        return in_array($method, $this->supported_methods ?? [], true);
    }
}
