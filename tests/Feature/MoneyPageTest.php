<?php

namespace Tests\Feature;

use App\Models\MoneyPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MoneyPageTest extends TestCase
{
    use RefreshDatabase;

    private function makePage(array $overrides = []): MoneyPage
    {
        return MoneyPage::create(array_merge([
            'type' => 'how_to',
            'cluster' => 'exchanges',
            'h1' => 'How to Buy Bitcoin',
            'slug' => 'how-to-buy-bitcoin-test',
            'meta_description' => 'A guide on how to buy Bitcoin safely.',
            'body_html' => '<h2>Step 1</h2><p>Create an account.</p><h2>Step 2</h2><p>Deposit funds.</p>',
            'faq' => [
                ['q' => 'Is Bitcoin safe?', 'a' => 'It carries risk like any asset.'],
            ],
            'status' => 'published',
            'published_at' => now()->subDay(),
        ], $overrides));
    }

    public function test_published_money_page_renders_seo_and_disclaimer(): void
    {
        $page = $this->makePage();

        $response = $this->get('/guides/'.$page->slug);

        $response->assertOk();
        $response->assertSee($page->h1);
        $response->assertSee('informational purposes only', false);
    }

    public function test_draft_money_page_returns_404(): void
    {
        $page = $this->makePage(['status' => 'draft', 'slug' => 'draft-money-page-test', 'published_at' => null]);

        $this->get('/guides/'.$page->slug)->assertNotFound();
    }

    public function test_in_review_money_page_returns_404(): void
    {
        $page = $this->makePage(['status' => 'in_review', 'slug' => 'in-review-money-page-test']);

        $this->get('/guides/'.$page->slug)->assertNotFound();
    }

    public function test_jsonld_article_and_faqpage_present(): void
    {
        $page = $this->makePage(['slug' => 'jsonld-money-page-test']);

        $response = $this->get('/guides/'.$page->slug);

        $response->assertOk();
        $response->assertSee('"@type":"Article"', false);
        $response->assertSee('"@type":"FAQPage"', false);
    }

    public function test_views_counter_increments_for_regular_visitor(): void
    {
        $page = $this->makePage(['slug' => 'views-money-page-test']);

        $this->get('/guides/'.$page->slug)->assertOk();

        $this->assertSame(1, $page->fresh()->views);
    }

    public function test_views_counter_does_not_increment_for_bots(): void
    {
        $page = $this->makePage(['slug' => 'bot-money-page-test']);

        $this->get('/guides/'.$page->slug, ['User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'])
            ->assertOk();

        $this->assertSame(0, $page->fresh()->views);
    }

    public function test_published_money_page_appears_in_sitemap(): void
    {
        $page = $this->makePage(['slug' => 'sitemap-money-page-test']);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertSee('/guides/'.$page->slug, false);
    }
}
