<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('himashva:low-stock-alert')->dailyAt('09:00');
Schedule::command('himashva:abandoned-cart-reminders')->hourly();
