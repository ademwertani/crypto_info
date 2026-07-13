<?php

namespace Database\Seeders;

use App\Models\NewsPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * ⚠️ EXAMPLE CONTENT — FOR DEMONSTRATION ONLY.
 *
 * These entries exist to show a working News module end to end. Real
 * content should be managed from the Filament dashboard at /admin.
 */
class NewsPostSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            [
                'title'       => 'Bitcoin Holds Above Key Support as Traders Watch Macro Data',
                'excerpt'     => 'Bitcoin consolidated near a closely watched support level this week as traders weighed upcoming macroeconomic releases against ongoing exchange inflow trends.',
                'meta_title'  => 'Bitcoin Holds Above Key Support as Traders Watch Macro Data',
                'meta_description' => 'A look at Bitcoin\'s recent price consolidation and the macro factors traders are watching this week.',
                'published_days_ago' => 1,
                'content' => '<h2 class="text-lg font-bold text-white mt-0 mb-3">Price Action Overview</h2>
<p class="mb-3">Bitcoin traded in a tight range this week, holding above a support level that has attracted buying interest on previous tests. Trading volume stayed moderate, suggesting neither strong conviction to break higher nor to sell aggressively.</p>
<h2 class="text-lg font-bold text-white mt-6 mb-3">What Traders Are Watching</h2>
<p class="mb-3">Market participants are focused on upcoming macroeconomic data releases, which have historically triggered short-term volatility across risk assets, including crypto. Exchange inflow and outflow data is also being monitored as a proxy for near-term selling or accumulation pressure.</p>
<p class="mb-3">As always, short-term price commentary should be treated as informational only and not as trading advice.</p>',
            ],
            [
                'title'       => 'Ethereum Layer-2 Activity Continues to Climb',
                'excerpt'     => 'Transaction activity across major Ethereum layer-2 networks has continued to grow, according to on-chain data, as users seek lower fees for everyday transactions.',
                'meta_title'  => 'Ethereum Layer-2 Activity Continues to Climb',
                'meta_description' => 'On-chain data shows continued growth in Ethereum layer-2 transaction activity.',
                'published_days_ago' => 3,
                'content' => '<h2 class="text-lg font-bold text-white mt-0 mb-3">Layer-2 Growth</h2>
<p class="mb-3">On-chain data indicates that transaction counts on several major Ethereum layer-2 networks have continued to trend upward, a shift often attributed to significantly lower transaction fees compared to using the Ethereum base layer directly.</p>
<h2 class="text-lg font-bold text-white mt-6 mb-3">Why It Matters</h2>
<p class="mb-3">Layer-2 networks are designed to process transactions off the main Ethereum chain while still inheriting its security guarantees, before settling back to the base layer. Continued growth in usage is often viewed by industry participants as a sign of broader scaling adoption.</p>',
            ],
            [
                'title'       => 'Stablecoin Market Capitalization Reaches New Milestone',
                'excerpt'     => 'The combined market capitalization of major stablecoins has reached a new milestone, reflecting continued demand for on-chain dollar-equivalent assets.',
                'meta_title'  => 'Stablecoin Market Capitalization Reaches New Milestone',
                'meta_description' => 'A look at the growth of the stablecoin market and what is driving continued demand.',
                'published_days_ago' => 5,
                'content' => '<h2 class="text-lg font-bold text-white mt-0 mb-3">A New Milestone</h2>
<p class="mb-3">The total market capitalization of major stablecoins has climbed to a new high, according to aggregated market data, continuing a trend of steady growth over recent quarters.</p>
<h2 class="text-lg font-bold text-white mt-6 mb-3">Demand Drivers</h2>
<p class="mb-3">Stablecoins are widely used as a trading pair on exchanges, for moving value between platforms, and as a way to temporarily step out of more volatile assets. Continued growth in these use cases has been a consistent driver of stablecoin supply.</p>',
            ],
        ];

        foreach ($posts as $data) {
            $daysAgo = $data['published_days_ago'];
            unset($data['published_days_ago']);

            NewsPost::updateOrCreate(
                ['slug' => Str::slug($data['title'])],
                array_merge($data, [
                    'status'       => 'published',
                    'published_at' => now()->subDays($daysAgo),
                ])
            );
        }
    }
}
