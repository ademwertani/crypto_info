<?php

namespace App\Http\Controllers;

use App\Models\Cryptocurrency;
use App\Services\SeoService;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class CompareController extends Controller
{
    private function allCoins(): \Illuminate\Support\Collection
    {
        $data = Cache::remember('compare_coin_list', 600, function () {
            return Cryptocurrency::orderBy('market_cap_rank')
                ->select('slug', 'name', 'symbol', 'image_url', 'market_cap_rank')
                ->get()
                ->toArray();
        });

        return collect($data)->map(fn ($item) => (object) $item);
    }

    public function chooser(): View
    {
        $seo = new SeoService();
        $seo->title       = 'Compare Cryptocurrencies — Side-by-Side Price Analysis | CryptoInfo';
        $seo->description = 'Compare any two cryptocurrencies side-by-side: price, market cap, volume, ATH and 24h performance.';
        $seo->canonical   = route('crypto.compare.chooser');

        return view('market.compare-chooser', [
            'allCoins' => $this->allCoins(),
            'seo'      => $seo,
        ]);
    }

    public function show(string $slugA, string $slugB): View
    {
        $rowA = Cache::remember("crypto_detail_{$slugA}", 300, function () use ($slugA) {
            return Cryptocurrency::where('slug', $slugA)->firstOrFail()->toArray();
        });

        $rowB = Cache::remember("crypto_detail_{$slugB}", 300, function () use ($slugB) {
            return Cryptocurrency::where('slug', $slugB)->firstOrFail()->toArray();
        });

        $coinA = (new Cryptocurrency())->forceFill($rowA);
        $coinB = (new Cryptocurrency())->forceFill($rowB);

        $seo = new SeoService();
        $seo->title       = "{$coinA->name} vs {$coinB->name} — Price & Market Comparison | CryptoInfo";
        $seo->description = "Compare {$coinA->name} and {$coinB->name}: price, market cap, volume, ATH, supply and 24h performance side-by-side.";
        $seo->canonical   = route('crypto.compare', ['slugA' => $slugA, 'slugB' => $slugB]);

        return view('market.compare', [
            'coinA'    => $coinA,
            'coinB'    => $coinB,
            'allCoins' => $this->allCoins(),
            'seo'      => $seo,
        ]);
    }
}
