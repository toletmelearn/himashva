<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'order_number', 'name', 'email', 'phone', 'address_line_1',
        'address_line_2', 'city', 'state', 'postal_code', 'country', 'subtotal',
        'discount_amount', 'coupon_id', 'shipping_amount', 'tax_amount', 'total',
        'payment_method', 'payment_status', 'payment_id', 'order_status',
        'tracking_number', 'tracking_url', 'shipping_partner', 'estimated_delivery',
        'delivered_at', 'cancelled_at', 'cancellation_reason', 'admin_notes',
        'ip_address', 'user_agent',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'estimated_delivery' => 'date',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Order $order) {
            if (! $order->order_number) {
                $order->order_number = static::generateOrderNumber();
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = "HMV-{$date}-";
        $last = static::withTrashed()->where('order_number', 'like', "{$prefix}%")->orderByDesc('id')->first();
        $sequence = $last ? ((int) Str::afterLast($last->order_number, '-') + 1) : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(ReturnRequest::class);
    }

    protected function formattedTotal(): Attribute
    {
        return Attribute::get(fn () => '₹'.number_format((float) $this->total, 2));
    }
}
