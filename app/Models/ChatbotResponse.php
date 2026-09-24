<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotResponse extends Model
{
    public const CATEGORIES = [
        'shipping' => 'Shipping',
        'payment' => 'Payment',
        'returns' => 'Returns',
        'tracking' => 'Tracking',
        'products' => 'Products',
        'general' => 'General',
        'greeting' => 'Greeting',
    ];

    protected $fillable = [
        'category', 'keywords', 'response', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'keywords' => 'array',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
