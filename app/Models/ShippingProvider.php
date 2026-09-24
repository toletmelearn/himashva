<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'display_name', 'driver', 'credentials', 'is_active', 'is_test_mode',
        'settings', 'supported_features', 'display_order', 'description',
    ];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'settings' => 'array',
        'supported_features' => 'array',
        'is_active' => 'boolean',
        'is_test_mode' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getCredential(string $key): ?string
    {
        return $this->credentials[$key] ?? null;
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return $this->settings[$key] ?? $default;
    }

    public function supportsFeature(string $feature): bool
    {
        return in_array($feature, $this->supported_features ?? [], true);
    }
}
