<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Every entry below logs its own output to storage/logs/schedule-*.log —
// tail those files on the server to confirm the cron is actually firing,
// independently of waiting for new content to show up in /admin.

// Refresh crypto market data every 10 minutes
Schedule::command('app:fetch-crypto-data')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/schedule-crypto-data.log'));

// Fetch latest crypto news every 30 minutes
Schedule::command('app:fetch-news')
    ->everyThirtyMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/schedule-fetch-news.log'));

// Draft NewsPost articles from real RSS items via AI — always drafts, still
// needs a human to review and publish in /admin/news-posts.
Schedule::command('news:generate --limit=5')
    ->everyThreeHours()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/schedule-news-generate.log'));

// Draft one Blog Article per day from config/blog_pipeline.php — the
// pipeline list is finite, so this naturally stops generating once it's
// exhausted (every entry already has a matching slug).
Schedule::command('blog:generate --limit=1')
    ->daily()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/schedule-blog-generate.log'));
