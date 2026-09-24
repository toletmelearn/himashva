<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbandonedCart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'session_id', 'email', 'cart_data', 'total', 'status',
        'reminder_sent_at', 'reminder_count', 'recovered_order_id', 'expires_at',
    ];

    protected $casts = [
        'cart_data' => 'array',
        'total' => 'decimal:2',
        'expires_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recoveredOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'recovered_order_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeRecoverable($query)
    {
        return $query->where('status', 'active')
            ->whereNotNull('email')
            ->where('expires_at', '>', now())
            ->where('reminder_count', '<', 2);
    }
}
