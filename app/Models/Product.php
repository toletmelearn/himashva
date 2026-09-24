<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'brand_id', 'name', 'slug', 'short_description', 'description',
        'sku', 'price', 'sale_price', 'cost_price', 'tax_rate', 'stock', 'low_stock_threshold',
        'weight_grams', 'status', 'is_active', 'is_featured', 'is_bestseller', 'is_new',
        'meta_title', 'meta_description', 'total_sold', 'avg_rating', 'review_count',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_bestseller' => 'boolean',
        'is_new' => 'boolean',
        'avg_rating' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::updating(function (Product $product) {
            if ($product->isDirty('stock')) {
                $product->syncStockStatus();
            }
        });
    }

    /**
     * Flips status between active and out_of_stock based on the current
     * stock level. Never touches draft/archived — those are deliberate
     * admin choices, not stock-driven.
     */
    public function syncStockStatus(): void
    {
        if ($this->stock <= 0 && $this->status === 'active') {
            $this->status = 'out_of_stock';
        } elseif ($this->stock > 0 && $this->status === 'out_of_stock') {
            $this->status = 'active';
        }
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)
            ->orderByRaw("image_type = 'real' desc")
            ->orderBy('sort_order');
    }

    public function primaryImage(): HasMany
    {
        return $this->hasMany(ProductImage::class)->where('is_primary', true);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function attributes_(): HasMany
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function sizeGuides(): BelongsToMany
    {
        return $this->belongsToMany(SizeGuide::class, 'product_size_guide');
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }

    protected function discountPercentage(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->sale_price || $this->price <= 0) {
                return 0;
            }

            return (int) round((($this->price - $this->sale_price) / $this->price) * 100);
        });
    }

    protected function currentPrice(): Attribute
    {
        return Attribute::get(fn () => $this->sale_price ?: $this->price);
    }

    protected function formattedPrice(): Attribute
    {
        return Attribute::get(fn () => '₹'.number_format((float) $this->current_price, 2));
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeVisible($query)
    {
        return $query->whereIn('status', ['active', 'out_of_stock']);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeBestseller($query)
    {
        return $query->where('is_bestseller', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeDiscountBetween($query, int $min, int $max)
    {
        return $query->whereNotNull('sale_price')
            ->where('price', '>', 0)
            ->whereRaw('((price - sale_price) / price) * 100 BETWEEN ? AND ?', [$min, $max]);
    }
}
