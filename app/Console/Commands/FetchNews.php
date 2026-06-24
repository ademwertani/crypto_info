<?php

namespace App\Console\Commands;

use App\Models\News;
use App\Services\AiSummaryService;
use App\Services\NewsApiService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

#[Signature('app:fetch-news')]
#[Description('Fetch latest crypto news from CryptoPanic RSS and store in DB')]
class FetchNews extends Command
{
    public function handle(NewsApiService $api, AiSummaryService $ai): int
    {
        $this->info('Fetching news from CryptoPanic RSS…');

        $articles = $api->fetchLatest(limit: 30);

        if (empty($articles)) {
            $this->warn('No articles returned.');
            return self::SUCCESS;
        }

        $inserted = 0;

        foreach ($articles as $article) {
            $exists = News::where('slug', $article['slug'])->exists();
            if ($exists) continue;

            $summary = $ai->summarizeNews($article['title'], $article['summary']);
            if ($summary) {
                $article['ai_summary'] = $summary;
            }

            News::create($article);
            $inserted++;
        }

        // Bust news caches
        for ($p = 1; $p <= 3; $p++) {
            Cache::forget("news_page_{$p}");
        }

        $this->info("Inserted {$inserted} new articles.");

        return self::SUCCESS;
    }
}
