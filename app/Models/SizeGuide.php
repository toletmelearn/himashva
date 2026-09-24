<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SizeGuide extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'category_id', 'type', 'table_data', 'image_path',
        'measurement_unit', 'is_active', 'description',
    ];

    protected $casts = [
        'table_data' => 'array',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_size_guide');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
