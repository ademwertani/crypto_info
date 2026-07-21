<?php

namespace Tests\Feature;

use App\Models\MoneyPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeneratePagesCommandTest extends TestCase
{
    use RefreshDatabase;

    private function validAnthropicPayload(): array
    {
        $body = json_encode([
            'meta_title' => 'How to Buy Bitcoin Safely in 2026',
            'meta_description' => 'Learn the safest way to buy Bitcoin: comparing exchanges, understanding fees, and basic security tips for beginners today.',
            'intro_html' => '<p>Buying Bitcoin can feel intimidating at first, but the process is straightforward once you understand the basics.</p>',
            'body_html' => '<h2>How to Buy</h2><p>'.str_repeat('Lorem ipsum dolor sit amet consectetur. ', 100).'</p>'
                .'<h2>Where to Buy</h2><p>'.str_repeat('Choose a reputable exchange. ', 50).'</p>'
                .'<h2>Fees to Watch</h2><p>'.str_repeat('Fees vary by platform. ', 50).'</p>'
                .'<h2>Security</h2><p>'.str_repeat('Enable two-factor authentication. ', 50).'</p>',
            'faq' => [
                ['q' => 'Is Bitcoin safe to buy?', 'a' => 'Yes, when using a reputable exchange with proper security practices.'],
                ['q' => 'How much Bitcoin should I buy?', 'a' => 'Only ever invest what you can afford to lose.'],
                ['q' => 'Do I need a wallet?', 'a' => 'A wallet gives you full control of your funds after buying.'],
                ['q' => 'What fees should I expect?', 'a' => 'Fees vary by exchange, typically a small percentage per trade.'],
            ],
            'cta_labels' => [
                ['label' => 'Buy on Binance', 'network' => 'binance', 'placement' => 'guide_cta'],
            ],
        ]);

        return [
            'id' => 'msg_test',
            'type' => 'message',
            'role' => 'assistant',
            'content' => [['type' => 'text', 'text' => $body]],
            'model' => 'claude-sonnet-5',
            'stop_reason' => 'end_turn',
            'usage' => ['input_tokens' => 100, 'output_tokens' => 500],
        ];
    }

    private function malformedAnthropicPayload(): array
    {
        return [
            'id' => 'msg_bad',
            'type' => 'message',
            'role' => 'assistant',
            'content' => [['type' => 'text', 'text' => 'Sorry, here is not quite { valid json']],
            'model' => 'claude-sonnet-5',
            'stop_reason' => 'end_turn',
        ];
    }

    public function test_dry_run_creates_nothing_and_calls_no_api(): void
    {
        Http::fake();

        $this->artisan('pages:generate', ['--cluster' => 'exchanges', '--limit' => 5, '--dry-run' => true])
            ->assertSuccessful();

        $this->assertDatabaseCount('money_pages', 0);
        Http::assertNothingSent();
    }

    public function test_generates_requested_number_of_drafts_for_cluster(): void
    {
        config(['services.anthropic.api_key' => 'test-key']);
        Http::fake(['api.anthropic.com/*' => Http::response($this->validAnthropicPayload(), 200)]);

        $this->artisan('pages:generate', ['--cluster' => 'exchanges', '--limit' => 5])
            ->assertSuccessful();

        $this->assertDatabaseCount('money_pages', 5);
        $this->assertSame(5, MoneyPage::where('cluster', 'exchanges')->where('status', 'draft')->count());
        Http::assertSentCount(5);
    }

    public function test_rerun_is_idempotent_and_makes_no_new_api_calls(): void
    {
        config(['services.anthropic.api_key' => 'test-key']);
        Http::fake(['api.anthropic.com/*' => Http::response($this->validAnthropicPayload(), 200)]);

        // "exchanges" has exactly 8 entries in config/money_pages_pipeline.php —
        // a --limit high enough to exhaust the whole cluster on the first run,
        // so the second run has nothing left to generate and is a true no-op.
        $this->artisan('pages:generate', ['--cluster' => 'exchanges', '--limit' => 8])->assertSuccessful();
        $this->assertDatabaseCount('money_pages', 8);
        Http::assertSentCount(8);

        $this->artisan('pages:generate', ['--cluster' => 'exchanges', '--limit' => 8])->assertSuccessful();
        $this->assertDatabaseCount('money_pages', 8);
        Http::assertSentCount(8);
    }

    public function test_malformed_json_response_is_skipped_without_crashing(): void
    {
        config(['services.anthropic.api_key' => 'test-key']);
        Http::fake(['api.anthropic.com/*' => Http::sequence()
            ->push($this->malformedAnthropicPayload(), 200)
            ->push($this->validAnthropicPayload(), 200),
        ]);

        $this->artisan('pages:generate', ['--cluster' => 'exchanges', '--limit' => 1])
            ->assertSuccessful();

        $this->assertDatabaseCount('money_pages', 1);
        Http::assertSentCount(2);
    }

    public function test_missing_related_coin_slug_does_not_crash(): void
    {
        config(['services.anthropic.api_key' => 'test-key']);
        config(['money_pages_pipeline.pages' => [
            ['title' => 'How to Buy Fakecoin', 'type' => 'buy_asset', 'cluster' => 'exchanges', 'related_coin_slugs' => ['nonexistent-coin-xyz']],
        ]]);
        Http::fake(['api.anthropic.com/*' => Http::response($this->validAnthropicPayload(), 200)]);

        $this->artisan('pages:generate', ['--cluster' => 'exchanges', '--limit' => 1])
            ->assertSuccessful();

        $page = MoneyPage::first();
        $this->assertNotNull($page);
        $this->assertSame([], $page->related_coin_ids);
    }

    public function test_missing_api_key_fails_fast(): void
    {
        config(['services.anthropic.api_key' => null]);
        Http::fake();

        $this->artisan('pages:generate', ['--cluster' => 'exchanges', '--limit' => 1])
            ->assertFailed();

        $this->assertDatabaseCount('money_pages', 0);
        Http::assertNothingSent();
    }
}
