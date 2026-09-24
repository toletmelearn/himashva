<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'platform', 'video_url', 'video_id',
        'title', 'thumbnail_url', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (self $video) {
            if ($video->platform === 'youtube') {
                if (preg_match('/(?:youtube\.com\/(?:watch\?v=|shorts\/|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $video->video_url, $matches)) {
                    $video->video_id = $matches[1];
                }
                if ($video->video_id && ! $video->thumbnail_url) {
                    $video->thumbnail_url = "https://img.youtube.com/vi/{$video->video_id}/hqdefault.jpg";
                }
            } elseif ($video->platform === 'instagram') {
                if (preg_match('/instagram\.com\/(?:reel|p)\/([a-zA-Z0-9_-]+)/', $video->video_url, $matches)) {
                    $video->video_id = $matches[1];
                }
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getThumbnailAttribute(): string
    {
        if ($this->thumbnail_url) {
            return $this->thumbnail_url;
        }

        if ($this->platform === 'youtube' && $this->video_id) {
            return "https://img.youtube.com/vi/{$this->video_id}/hqdefault.jpg";
        }

        return asset('images/instagram-video-placeholder.png');
    }
}
