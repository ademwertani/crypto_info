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

// Draft NewsPost articles from real RSS items via AI — always drafts, still
// needs a human to review and publish in /admin/news-posts.
Schedule::command('news:generate --limit=5')
    ->everyThreeHours()
    ->withoutOverlapping()
    ->runInBackground();

// Draft one Blog Article per day from config/blog_pipeline.php — the
// pipeline list is finite, so this naturally stops generating once it's
// exhausted (every entry already has a matching slug).
Schedule::command('blog:generate --limit=1')
    ->daily()
    ->withoutOverlapping()
    ->runInBackground();
