<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'product_id', 'order_id', 'rating', 'title', 'comment',
        'is_approved', 'admin_reply', 'is_verified_purchase',
        'helpful_count', 'unhelpful_count',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'is_verified_purchase' => 'boolean',
        'rating' => 'integer',
        'helpful_count' => 'integer',
        'unhelpful_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ReviewMedia::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ReviewVote::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }
}
