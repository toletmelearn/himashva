<?php

namespace App\Models;

use Database\Factories\ReviewMediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ReviewMedia extends Model
{
    /** @use HasFactory<ReviewMediaFactory> */
    use HasFactory;

    protected $fillable = [
        'review_id', 'type', 'file_path', 'file_name', 'file_size',
        'mime_type', 'sort_order', 'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'file_size' => 'integer',
        'sort_order' => 'integer',
    ];

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }
}
