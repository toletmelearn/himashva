<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * "Return" is a reserved word in PHP, so this model is named ReturnRequest
 * but maps to the `returns` table.
 */
class ReturnRequest extends Model
{
    use Auditable, HasFactory;

    protected $table = 'returns';

    protected $fillable = [
        'order_id', 'user_id', 'reason_category', 'reason_text', 'status',
        'refund_amount', 'admin_notes',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }
}
