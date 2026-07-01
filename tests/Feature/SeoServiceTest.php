<?php

namespace Tests\Feature;

use App\Models\News;
use App\Services\SeoService;
use Tests\TestCase;

class SeoServiceTest extends TestCase
{
    public function test_for_news_uses_safe_fallback_when_news_routes_are_unavailable(): void
    {
        $article = new News([
            'title' => 'Sample crypto update',
            'slug' => 'sample-crypto-update',
            'summary' => 'This is a summary.',
            'published_at' => now(),
            'updated_at' => now(),
        ]);

        $seo = SeoService::forNews($article);

        $this->assertSame(url('/'), $seo->canonical);
        $this->assertSame('Sample crypto update | CryptoInfo', $seo->title);
    }
}
