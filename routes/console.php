<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Refresh crypto market data every 10 minutes
Schedule::command('app:fetch-crypto-data')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// Fetch latest crypto news every 30 minutes
Schedule::command('app:fetch-news')
    ->everyThirtyMinutes()
    ->withoutOverlapping()
    ->runInBackground();
