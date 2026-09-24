<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class SiteSetting extends Model
{
    public $timestamps = false;

    protected $fillable = ['key', 'value', 'group'];

    protected static ?Collection $cached = null;

    protected static function boot()
    {
        parent::boot();

        static::saved(fn () => static::$cached = null);
        static::deleted(fn () => static::$cached = null);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (static::$cached === null) {
            static::$cached = static::query()->pluck('value', 'key');
        }

        return static::$cached[$key] ?? $default;
    }
}
