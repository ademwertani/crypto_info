<?php

namespace Tests\Feature;

use App\Filament\Resources\MoneyPages\Pages\CreateMoneyPage;
use App\Filament\Resources\MoneyPages\Pages\EditMoneyPage;
use App\Filament\Resources\MoneyPages\Pages\ListMoneyPages;
use App\Models\Cryptocurrency;
use App\Models\MoneyPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MoneyPageAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_away_from_the_admin_panel(): void
    {
        $this->get('/admin/money-pages')->assertRedirect('/admin/login');
    }

    public function test_a_full_money_page_can_be_created_from_filament_and_appears_on_the_site(): void
    {
        $user = User::factory()->create();

        $bitcoin = Cryptocurrency::create(['name' => 'Bitcoin', 'symbol' => 'BTC', 'slug' => 'bitcoin']);

        $related = MoneyPage::create([
            'type' => 'wallet_review',
            'cluster' => 'wallets',
            'h1' => 'Best Wallets 2026',
            'slug' => 'best-wallets-2026',
            'body_html' => '<p>Body</p>',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        Livewire::actingAs($user)
            ->test(CreateMoneyPage::class)
            ->fillForm([
                'h1' => 'Best Crypto Exchanges 2026',
                'type' => 'best_list',
                'cluster' => 'exchanges',
                'status' => 'published',
                'intro_html' => '<p>Our top picks for 2026.</p>',
                'body_html' => '<h2>Top Picks</h2><p>Details about the best exchanges.</p>',
                'faq' => [
                    ['q' => 'What is the best exchange?', 'a' => 'It depends on your trading volume and region.'],
                ],
                'cta_config' => [
                    ['label' => 'Trade on Binance', 'href' => 'https://www.binance.com/en/register?ref=CRYPTOINFO', 'network' => 'binance'],
                ],
                'related_coin_ids' => [$bitcoin->id],
                'related_page_ids' => [$related->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $page = MoneyPage::where('h1', 'Best Crypto Exchanges 2026')->firstOrFail();

        $this->assertNotSame('', $page->slug);
        $this->assertSame([$bitcoin->id], $page->related_coin_ids);
        $this->assertSame([$related->id], $page->related_page_ids);

        $response = $this->get('/guides/'.$page->slug);
        $response->assertOk();
        $response->assertSee('Best Crypto Exchanges 2026');
        $response->assertSee('Trade on Binance');
        $response->assertSee('data-affiliate-network="binance"', false);
        $response->assertSee('Bitcoin');
        $response->assertSee('Best Wallets 2026');
    }

    public function test_publish_and_unpublish_table_actions_toggle_site_visibility(): void
    {
        $user = User::factory()->create();
        $page = MoneyPage::create([
            'type' => 'how_to',
            'cluster' => 'exchanges',
            'h1' => 'Draft Toggle Example',
            'slug' => 'draft-toggle-example',
            'body_html' => '<p>Body</p>',
            'status' => 'draft',
        ]);

        Livewire::actingAs($user)
            ->test(ListMoneyPages::class)
            ->callTableAction('publish', $page);

        $this->assertSame('published', $page->fresh()->status);
        $this->assertNotNull($page->fresh()->published_at);
        $this->get('/guides/'.$page->slug)->assertOk();

        Livewire::actingAs($user)
            ->test(ListMoneyPages::class)
            ->callTableAction('unpublish', $page);

        $this->assertSame('draft', $page->fresh()->status);
        $this->get('/guides/'.$page->slug)->assertNotFound();
    }

    public function test_editing_a_money_page_updates_it_on_the_site(): void
    {
        $user = User::factory()->create();
        $page = MoneyPage::create([
            'type' => 'how_to',
            'cluster' => 'exchanges',
            'h1' => 'Original Title',
            'slug' => 'original-money-page-title',
            'body_html' => '<p>Body</p>',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        Livewire::actingAs($user)
            ->test(EditMoneyPage::class, ['record' => $page->getRouteKey()])
            ->fillForm(['h1' => 'Updated Title'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Updated Title', $page->fresh()->h1);
        $this->get('/guides/'.$page->slug)->assertSee('Updated Title');
    }
}
