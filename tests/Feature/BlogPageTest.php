<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_index_lists_only_published_articles(): void
    {
        $category = ArticleCategory::create(['name' => 'Guides', 'slug' => 'guides']);

        Article::create([
            'article_category_id' => $category->id,
            'title'       => 'Published Example',
            'slug'        => 'published-example',
            'sections'    => ['<p>Body</p>'],
            'status'      => 'published',
            'published_at'=> now()->subDay(),
        ]);

        Article::create([
            'article_category_id' => $category->id,
            'title'       => 'Draft Example',
            'slug'        => 'draft-example',
            'sections'    => ['<p>Body</p>'],
            'status'      => 'draft',
            'published_at'=> null,
        ]);

        $response = $this->get('/blog');

        $response->assertOk();
        $response->assertSee('Published Example');
        $response->assertDontSee('Draft Example');
    }

    public function test_draft_article_returns_404(): void
    {
        Article::create([
            'title'        => 'Draft Example',
            'slug'         => 'draft-example',
            'sections'     => ['<p>Body</p>'],
            'status'       => 'draft',
            'published_at' => null,
        ]);

        $this->get('/blog/draft-example')->assertNotFound();
    }

    public function test_published_article_page_renders_seo_and_disclaimer(): void
    {
        $article = Article::create([
            'title'        => 'How to Secure Your Crypto Wallet',
            'slug'         => 'how-to-secure-your-crypto-wallet-test',
            'sections'     => ['<h2>Intro</h2><p>Body</p>', '<h2>More</h2><p>Body</p>'],
            'status'       => 'published',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get('/blog/'.$article->slug);

        $response->assertOk();
        $response->assertSee($article->title);
        $response->assertSee('informational purposes only', false);
    }
}
