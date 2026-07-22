<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GenerateArticlesCommandTest extends TestCase
{
    use RefreshDatabase;

    private function validGroqPayload(): array
    {
        $body = json_encode([
            'meta_title' => 'What Is DeFi in Under 60 Characters',
            'meta_description' => 'Learn the basics of decentralized finance, how it differs from traditional finance, and what to know before exploring it.',
            'excerpt' => 'A beginner-friendly introduction to decentralized finance (DeFi).',
            'sections' => [
                '<h2>What Is DeFi?</h2><p>'.str_repeat('Lorem ipsum dolor sit amet. ', 60).'</p>',
                '<h2>How It Differs From Traditional Finance</h2><p>'.str_repeat('More detail here. ', 40).'</p><ul><li>No intermediaries</li><li>Open access</li></ul>',
            ],
        ]);

        return [
            'id' => 'chatcmpl_test',
            'choices' => [
                ['index' => 0, 'message' => ['role' => 'assistant', 'content' => $body], 'finish_reason' => 'stop'],
            ],
        ];
    }

    private function malformedGroqPayload(): array
    {
        return [
            'id' => 'chatcmpl_bad',
            'choices' => [
                ['index' => 0, 'message' => ['role' => 'assistant', 'content' => 'not valid json {'], 'finish_reason' => 'stop'],
            ],
        ];
    }

    public function test_dry_run_creates_nothing_and_calls_no_api(): void
    {
        Http::fake();

        $this->artisan('blog:generate', ['--category' => 'security', '--limit' => 3, '--dry-run' => true])
            ->assertSuccessful();

        $this->assertDatabaseCount('articles', 0);
        Http::assertNothingSent();
    }

    public function test_generates_requested_number_of_drafts_for_category(): void
    {
        config(['services.groq.api_key' => 'test-key']);
        ArticleCategory::create(['name' => 'Beginner Guides', 'slug' => 'beginner-guides']);
        Http::fake(['api.groq.com/*' => Http::response($this->validGroqPayload(), 200)]);

        $this->artisan('blog:generate', ['--category' => 'beginner-guides', '--limit' => 3])
            ->assertSuccessful();

        $this->assertDatabaseCount('articles', 3);
        $this->assertSame(3, Article::where('status', 'draft')->count());

        $article = Article::first();
        $this->assertNotSame('', $article->slug);
        $this->assertCount(2, $article->sections);
        $this->assertSame(ArticleCategory::first()->id, $article->article_category_id);
    }

    public function test_rerun_is_idempotent_and_makes_no_new_api_calls(): void
    {
        config(['services.groq.api_key' => 'test-key']);
        ArticleCategory::create(['name' => 'Security', 'slug' => 'security']);
        Http::fake(['api.groq.com/*' => Http::response($this->validGroqPayload(), 200)]);

        // "security" has exactly 3 entries in config/blog_pipeline.php.
        $this->artisan('blog:generate', ['--category' => 'security', '--limit' => 3])->assertSuccessful();
        $this->assertDatabaseCount('articles', 3);
        Http::assertSentCount(3);

        $this->artisan('blog:generate', ['--category' => 'security', '--limit' => 3])->assertSuccessful();
        $this->assertDatabaseCount('articles', 3);
        Http::assertSentCount(3);
    }

    public function test_malformed_json_response_is_skipped_without_crashing(): void
    {
        config(['services.groq.api_key' => 'test-key']);
        Http::fake(['api.groq.com/*' => Http::sequence()
            ->push($this->malformedGroqPayload(), 200)
            ->push($this->validGroqPayload(), 200),
        ]);

        $this->artisan('blog:generate', ['--category' => 'security', '--limit' => 1])
            ->assertSuccessful();

        $this->assertDatabaseCount('articles', 1);
        Http::assertSentCount(2);
    }

    public function test_missing_category_leaves_article_category_id_null(): void
    {
        config(['services.groq.api_key' => 'test-key']);
        config(['blog_pipeline.articles' => [
            ['title' => 'Orphan Topic', 'category' => 'nonexistent-category', 'related_coin_slugs' => []],
        ]]);
        Http::fake(['api.groq.com/*' => Http::response($this->validGroqPayload(), 200)]);

        $this->artisan('blog:generate', ['--limit' => 1])->assertSuccessful();

        $article = Article::first();
        $this->assertNotNull($article);
        $this->assertNull($article->article_category_id);
    }

    public function test_missing_api_key_fails_fast(): void
    {
        config(['services.groq.api_key' => null]);
        Http::fake();

        $this->artisan('blog:generate', ['--category' => 'security', '--limit' => 1])
            ->assertFailed();

        $this->assertDatabaseCount('articles', 0);
        Http::assertNothingSent();
    }
}
