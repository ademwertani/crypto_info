<?php

namespace Tests\Feature;

use App\Models\NewsPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_index_lists_only_published_posts(): void
    {
        NewsPost::create([
            'title'        => 'Published Example',
            'slug'         => 'published-example',
            'content'      => '<p>Body</p>',
            'status'       => 'published',
            'published_at' => now()->subDay(),
        ]);

        NewsPost::create([
            'title'        => 'Draft Example',
            'slug'         => 'draft-example',
            'content'      => '<p>Body</p>',
            'status'       => 'draft',
            'published_at' => null,
        ]);

        $response = $this->get('/news');

        $response->assertOk();
        $response->assertSee('Published Example');
        $response->assertDontSee('Draft Example');
    }

    public function test_news_index_search_filters_by_title(): void
    {
        NewsPost::create([
            'title'        => 'Bitcoin Rallies Past Resistance',
            'slug'         => 'bitcoin-rallies-past-resistance',
            'content'      => '<p>Body</p>',
            'status'       => 'published',
            'published_at' => now()->subDay(),
        ]);

        NewsPost::create([
            'title'        => 'Ethereum Network Upgrade Ships',
            'slug'         => 'ethereum-network-upgrade-ships',
            'content'      => '<p>Body</p>',
            'status'       => 'published',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get('/news?search=Bitcoin');

        $response->assertOk();
        $response->assertSee('Bitcoin Rallies Past Resistance');
        $response->assertDontSee('Ethereum Network Upgrade Ships');
    }

    public function test_draft_news_post_returns_404(): void
    {
        NewsPost::create([
            'title'        => 'Draft Example',
            'slug'         => 'draft-example',
            'content'      => '<p>Body</p>',
            'status'       => 'draft',
            'published_at' => null,
        ]);

        $this->get('/news/draft-example')->assertNotFound();
    }

    public function test_published_news_post_page_renders_seo_and_disclaimer(): void
    {
        $post = NewsPost::create([
            'title'        => 'Bitcoin Holds Above Key Support',
            'slug'         => 'bitcoin-holds-above-key-support-test',
            'content'      => '<h2>Intro</h2><p>Body</p>',
            'status'       => 'published',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get('/news/'.$post->slug);

        $response->assertOk();
        $response->assertSee($post->title);
        $response->assertSee('informational purposes only', false);
    }
}
