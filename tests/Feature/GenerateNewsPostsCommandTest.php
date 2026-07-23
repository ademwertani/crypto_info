<?php

namespace Tests\Feature;

use App\Models\NewsPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GenerateNewsPostsCommandTest extends TestCase
{
    use RefreshDatabase;

    private function rssFixture(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"><channel>
<item>
<title>Example Exchange Reports Record Trading Volume</title>
<link>https://example.com/news/record-volume</link>
<pubDate>Wed, 22 Jul 2026 08:00:00 +0000</pubDate>
<description>Example Exchange said trading volume reached a new high this week, according to a company statement.</description>
</item>
</channel></rss>
XML;
    }

    private function validGroqPayload(): array
    {
        $body = json_encode([
            'meta_title' => 'Example Exchange Hits Record Volume',
            'meta_description' => 'Example Exchange reported a new record in trading volume this week, according to a company statement released today.',
            'excerpt' => 'Example Exchange reported record trading volume this week.',
            'content_html' => '<p>Example Exchange said trading volume reached a new high this week, according to a company statement. Based on reporting from Example Source, no further details were disclosed.</p>',
        ]);

        return [
            'id' => 'chatcmpl_test',
            'choices' => [
                ['index' => 0, 'message' => ['role' => 'assistant', 'content' => $body], 'finish_reason' => 'stop'],
            ],
        ];
    }

    private function fakeRssEndpoints(): void
    {
        Http::fake([
            'coindesk.com/*' => Http::response($this->rssFixture(), 200),
            'cointelegraph.com/*' => Http::response('<rss version="2.0"><channel></channel></rss>', 200),
            'api.groq.com/*' => Http::response($this->validGroqPayload(), 200),
        ]);
    }

    public function test_dry_run_creates_nothing_and_calls_no_groq_api(): void
    {
        $this->fakeRssEndpoints();

        $this->artisan('news:generate', ['--dry-run' => true])->assertSuccessful();

        $this->assertDatabaseCount('news_posts', 0);
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'api.groq.com'));
    }

    public function test_generates_and_publishes_immediately_from_a_real_rss_item(): void
    {
        config(['services.groq.api_key' => 'test-key']);
        $this->fakeRssEndpoints();

        $this->artisan('news:generate', ['--limit' => 1])->assertSuccessful();

        $this->assertDatabaseCount('news_posts', 1);

        $post = NewsPost::first();
        $this->assertSame('Example Exchange Reports Record Trading Volume', $post->title);
        $this->assertSame('published', $post->status);
        $this->assertSame('https://example.com/news/record-volume', $post->source_url);
        $this->assertNotEmpty($post->content);
        $this->assertSame('2026-07-22', $post->published_at->format('Y-m-d'));

        // No manual "publish" step in the dashboard — it's live right away.
        $this->get('/news/'.$post->slug)->assertOk()->assertSee($post->title);
        $this->get('/news')->assertSee($post->title);
    }

    public function test_rerun_is_idempotent_and_skips_already_published_articles(): void
    {
        config(['services.groq.api_key' => 'test-key']);
        $this->fakeRssEndpoints();

        $this->artisan('news:generate', ['--limit' => 1])->assertSuccessful();
        $this->assertDatabaseCount('news_posts', 1);
        Http::assertSentCount(3); // 2 RSS feeds + 1 Groq call

        $this->artisan('news:generate', ['--limit' => 1])->assertSuccessful();
        $this->assertDatabaseCount('news_posts', 1);
        Http::assertSentCount(5); // + 2 more RSS re-fetches, no new Groq call
    }

    public function test_missing_api_key_fails_fast_without_fetching_rss(): void
    {
        config(['services.groq.api_key' => null]);
        Http::fake();

        $this->artisan('news:generate', ['--limit' => 1])->assertFailed();

        $this->assertDatabaseCount('news_posts', 0);
        Http::assertNothingSent();
    }

    /**
     * Regression test for a real production incident: Groq returned a
     * 235-char meta_description for a Tesla/CoinDesk story, and MySQL
     * (varchar(255), strict mode) rejected the whole insert with
     * "Data too long for column 'meta_description'", crashing the batch.
     */
    public function test_oversized_meta_description_is_truncated_instead_of_crashing(): void
    {
        config(['services.groq.api_key' => 'test-key']);

        $oversizedPayload = [
            'id' => 'chatcmpl_test',
            'choices' => [[
                'index' => 0,
                'message' => ['role' => 'assistant', 'content' => json_encode([
                    'meta_title' => 'Oversized Description Test',
                    'meta_description' => str_repeat('This description is way too long. ', 10),
                    'excerpt' => 'Short excerpt.',
                    'content_html' => '<p>Body.</p>',
                ])],
                'finish_reason' => 'stop',
            ]],
        ];

        Http::fake([
            'coindesk.com/*' => Http::response($this->rssFixture(), 200),
            'cointelegraph.com/*' => Http::response('<rss version="2.0"><channel></channel></rss>', 200),
            'api.groq.com/*' => Http::response($oversizedPayload, 200),
        ]);

        $this->artisan('news:generate', ['--limit' => 1])->assertSuccessful();

        $this->assertDatabaseCount('news_posts', 1);
        // 191, not 255 — AppServiceProvider sets Builder::defaultStringLength(191).
        $this->assertLessThanOrEqual(191, mb_strlen(NewsPost::first()->meta_description));
    }

    public function test_no_rss_items_returned_is_not_an_error(): void
    {
        config(['services.groq.api_key' => 'test-key']);
        Http::fake([
            'coindesk.com/*' => Http::response('<rss version="2.0"><channel></channel></rss>', 200),
            'cointelegraph.com/*' => Http::response('<rss version="2.0"><channel></channel></rss>', 200),
        ]);

        $this->artisan('news:generate', ['--limit' => 1])->assertSuccessful();

        $this->assertDatabaseCount('news_posts', 0);
    }
}
