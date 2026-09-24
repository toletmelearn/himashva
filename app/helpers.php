<?php

use App\Models\SiteSetting;

if (! function_exists('settings')) {
    function settings(string $key, mixed $default = null): mixed
    {
        return SiteSetting::get($key, $default);
    }
}

if (! function_exists('format_price')) {
    function format_price(float|string|null $amount): string
    {
        return '₹'.number_format((float) $amount, 2);
    }
}
