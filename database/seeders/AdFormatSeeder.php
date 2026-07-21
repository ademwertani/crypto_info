<?php

namespace Database\Seeders;

use App\Models\AdFormat;
use Illuminate\Database\Seeder;

class AdFormatSeeder extends Seeder
{
    /**
     * Provisional pricing placeholders — the user still needs to set real
     * rates in Filament (see "action manuelle semaine 3" in the module
     * spec). Never presented as a firm quote, just a starting point.
     */
    private const PRICE_PLACEHOLDER = 'Contact us for current rates — pricing under review';

    public function run(): void
    {
        foreach ($this->formats() as $data) {
            // Skip if it already exists — never overwrite pricing an admin
            // has since edited by hand in Filament.
            AdFormat::firstOrCreate(['slug' => $data['slug']], $data);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function formats(): array
    {
        return [
            [
                'slug' => 'sponsored-articles',
                'name' => 'Sponsored Articles',
                'description' => 'A dedicated article about your project, product or service, published in our Blog or News section and written to read like genuine editorial content rather than an ad.',
                'specs' => [
                    'Published in the Blog or News section',
                    'Up to 1,000 words, written or reviewed by our editorial team',
                    'Includes up to 2 links back to your site',
                    'Stays live permanently (not a time-limited placement)',
                ],
                'price_range' => self::PRICE_PLACEHOLDER,
                'sort_order' => 1,
                'status' => 'published',
            ],
            [
                'slug' => 'banner-ads',
                'name' => 'Banner Ads',
                'description' => 'Display banner placements across high-traffic pages (homepage, market listings, coin pages) targeting a crypto-native audience already comparing exchanges and wallets.',
                'specs' => [
                    'Standard sizes: 728x90 or 300x250',
                    'Placed above the fold on high-traffic pages',
                    'Rotates with a maximum of 3 other advertisers per slot',
                    'Billed as a fixed placement, not per-click',
                ],
                'price_range' => self::PRICE_PLACEHOLDER,
                'sort_order' => 2,
                'status' => 'published',
            ],
            [
                'slug' => 'press-releases',
                'name' => 'Press Releases',
                'description' => 'Distribution of your press release through our News section, clearly labeled as a press release and lightly edited for clarity and formatting.',
                'specs' => [
                    'Distributed via the News section',
                    'Clearly labeled as a press release, not editorial content',
                    'Light editing for clarity and formatting only — content stays yours',
                    'No guaranteed placement duration',
                ],
                'price_range' => self::PRICE_PLACEHOLDER,
                'sort_order' => 3,
                'status' => 'published',
            ],
        ];
    }
}
