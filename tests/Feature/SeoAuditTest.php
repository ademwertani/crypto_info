<?php

namespace Tests\Feature;

use App\Filament\Widgets\SeoHealthOverview;
use App\Filament\Widgets\SeoTopIssuesTable;
use App\Models\Cryptocurrency;
use App\Models\MoneyPage;
use App\Models\User;
use App\Services\SeoAuditor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SeoAuditTest extends TestCase
{
    use RefreshDatabase;

    private function validMetaDescription(): string
    {
        // Deterministic 140-char string — comfortably inside the 120-160
        // window the audit checks for, easier to reason about than padding
        // a real sentence to an exact length.
        return substr(str_repeat('Lorem ipsum dolor sit amet consectetur adipiscing elit sed do. ', 3), 0, 140);
    }

    private function longBody(): string
    {
        return '<h2>Section</h2><p>'.str_repeat('word ', 320).'</p>';
    }

    private function findAudited($pages, string $type, int $id): ?array
    {
        return $pages->first(fn (array $p) => $p['type'] === $type && $p['id'] === $id);
    }

    public function test_command_runs_successfully_and_exports_csv_across_mixed_content(): void
    {
        Storage::fake('local');

        MoneyPage::create([
            'type' => 'how_to',
            'cluster' => 'exchanges',
            'h1' => 'How to Buy Bitcoin',
            'slug' => 'how-to-buy-bitcoin-clean',
            'meta_title' => 'How to Buy Bitcoin',
            'meta_description' => $this->validMetaDescription(),
            'body_html' => $this->longBody(),
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        MoneyPage::create([
            'type' => 'best_list',
            'cluster' => 'exchanges',
            'h1' => 'Best Exchanges',
            'slug' => 'thin-broken-page',
            'meta_title' => str_repeat('X', 70),
            'meta_description' => null,
            'body_html' => '<h1>Duplicate heading</h1><p>Too short.</p><a href="/guides/does-not-exist">broken</a><img src="x.png">',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        Cryptocurrency::create([
            'name' => 'Bitcoin', 'symbol' => 'BTC', 'slug' => 'bitcoin',
            'current_price' => 65000, 'price_change_percentage_24h_in_currency' => 1.5,
        ]);

        $this->artisan('seo:audit')->assertSuccessful();

        $files = Storage::disk('local')->files('seo-audits');
        $this->assertNotEmpty($files, 'Expected a CSV report to be written to storage/app/seo-audits.');
        $this->assertStringContainsString('thin-broken-page', Storage::disk('local')->get($files[0]));
    }

    public function test_a_clean_page_scores_100(): void
    {
        $page = MoneyPage::create([
            'type' => 'how_to',
            'cluster' => 'exchanges',
            'h1' => 'A Clean Guide Title',
            'slug' => 'a-clean-guide-title',
            'meta_title' => 'A Clean Guide Title',
            'meta_description' => $this->validMetaDescription(),
            'body_html' => $this->longBody(),
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $pages = app(SeoAuditor::class)->run()['pages'];
        $audited = $this->findAudited($pages, 'money_page', $page->id);

        $this->assertNotNull($audited);
        $this->assertSame([], $audited['issues']);
        $this->assertSame(100, $audited['score']);
    }

    public function test_detects_duplicate_h1_thin_content_missing_description_and_broken_link(): void
    {
        $page = MoneyPage::create([
            'type' => 'best_list',
            'cluster' => 'exchanges',
            'h1' => 'Best Exchanges',
            'slug' => 'best-exchanges-bad-example',
            'meta_title' => 'Best Exchanges',
            'meta_description' => null,
            'body_html' => '<h1>Duplicate</h1><p>Too short.</p><a href="/guides/does-not-exist">broken</a>',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $pages = app(SeoAuditor::class)->run()['pages'];
        $audited = $this->findAudited($pages, 'money_page', $page->id);

        $this->assertContains('meta_description', $audited['issues']);
        $this->assertContains('h1_multiple', $audited['issues']);
        $this->assertContains('thin_content', $audited['issues']);
        $this->assertContains('broken_internal_links', $audited['issues']);
        $this->assertLessThan(100, $audited['score']);
    }

    public function test_valid_internal_link_is_not_flagged_as_broken(): void
    {
        Cryptocurrency::create(['name' => 'Bitcoin', 'symbol' => 'BTC', 'slug' => 'bitcoin']);

        $page = MoneyPage::create([
            'type' => 'how_to',
            'cluster' => 'exchanges',
            'h1' => 'Links to Bitcoin',
            'slug' => 'links-to-bitcoin',
            'meta_title' => 'Links to Bitcoin',
            'meta_description' => $this->validMetaDescription(),
            'body_html' => $this->longBody().'<a href="/currencies/bitcoin">Bitcoin</a>',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $pages = app(SeoAuditor::class)->run()['pages'];
        $audited = $this->findAudited($pages, 'money_page', $page->id);

        $this->assertNotContains('broken_internal_links', $audited['issues']);
    }

    public function test_crypto_pages_are_audited_without_the_image_alt_check(): void
    {
        $coin = Cryptocurrency::create([
            'name' => 'Obscure Coin',
            'symbol' => 'OBS',
            'slug' => 'obscure-coin',
            'current_price' => 0.01,
            'price_change_percentage_24h_in_currency' => -2.3,
            'description' => 'Very short description.',
        ]);

        $pages = app(SeoAuditor::class)->run()['pages'];
        $audited = $this->findAudited($pages, 'crypto', $coin->id);

        $this->assertNotNull($audited);
        $this->assertNotContains('images_missing_alt', $audited['issues']);
        // Most CoinGecko descriptions are short/empty — this is a real, expected signal, not a bug.
        $this->assertContains('thin_content', $audited['issues']);
    }

    public function test_dashboard_widgets_render_for_authenticated_admin(): void
    {
        $user = User::factory()->create();

        MoneyPage::create([
            'type' => 'how_to',
            'cluster' => 'exchanges',
            'h1' => 'Widget Test Page',
            'slug' => 'widget-test-page',
            'body_html' => '<p>Body</p>',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        Livewire::actingAs($user)->test(SeoHealthOverview::class)->assertOk();
        Livewire::actingAs($user)->test(SeoTopIssuesTable::class)->assertOk();
    }
}
