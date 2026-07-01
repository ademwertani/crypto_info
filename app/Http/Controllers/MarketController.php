<?php

namespace App\Http\Controllers;

use App\Models\Cryptocurrency;
use App\Services\GlobalMarketService;
use App\Services\SeoService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MarketController extends Controller
{
    private const CACHE_TTL = 300;
    private const LIMIT     = 50;

    public function gainers(GlobalMarketService $gms): View
    {
        $rows = Cache::remember('crypto_gainers', self::CACHE_TTL, function () {
            return DB::table('cryptocurrencies')
                ->whereNotNull('price_change_percentage_24h_in_currency')
                ->orderByDesc('price_change_percentage_24h_in_currency')
                ->limit(self::LIMIT)
                ->get()
                ->toArray();
        });

        $coins = $this->hydrate($rows);
        $seo   = SeoService::forMarket('gainers');
        $stats = $gms->getGlobalStats();

        return view('market.gainers', compact('coins', 'seo', 'stats'));
    }

    public function losers(GlobalMarketService $gms): View
    {
        $rows = Cache::remember('crypto_losers', self::CACHE_TTL, function () {
            return DB::table('cryptocurrencies')
                ->whereNotNull('price_change_percentage_24h_in_currency')
                ->orderBy('price_change_percentage_24h_in_currency')
                ->limit(self::LIMIT)
                ->get()
                ->toArray();
        });

        $coins = $this->hydrate($rows);
        $seo   = SeoService::forMarket('losers');
        $stats = $gms->getGlobalStats();

        return view('market.losers', compact('coins', 'seo', 'stats'));
    }

    public function trending(GlobalMarketService $gms): View
    {
        $rows = Cache::remember('crypto_trending', self::CACHE_TTL, function () {
            return DB::table('cryptocurrencies')
                ->whereNotNull('total_volume')
                ->orderByDesc('total_volume')
                ->limit(self::LIMIT)
                ->get()
                ->toArray();
        });

        $coins = $this->hydrate($rows);
        $seo   = SeoService::forMarket('trending');
        $stats = $gms->getGlobalStats();

        return view('market.trending', compact('coins', 'seo', 'stats'));
    }

    public function fearGreed(GlobalMarketService $gms): View
    {
        $stats = $gms->getGlobalStats();

        $seo = new SeoService();
        $seo->title       = 'Crypto Fear & Greed Index — Live Market Sentiment | CryptoInfo';
        $seo->description = 'Track the Crypto Fear & Greed Index in real time. Understand whether the market is driven by fear or greed to make smarter investment decisions.';
        $seo->canonical   = route('market.fear-greed');

        return view('market.fear-greed', compact('stats', 'seo'));
    }

    public function bitcoinDominance(GlobalMarketService $gms): View
    {
        $stats = $gms->getGlobalStats();

        $rows = Cache::remember('crypto_top10', self::CACHE_TTL, function () {
            return DB::table('cryptocurrencies')
                ->orderBy('market_cap_rank')
                ->limit(10)
                ->get()
                ->toArray();
        });
        $top10 = $this->hydrate($rows);

        $seo = new SeoService();
        $seo->title       = 'Bitcoin Dominance Today — BTC Market Cap vs Altcoins | CryptoInfo';
        $seo->description = "Bitcoin dominance is {$stats['btc_dominance']}% of the total crypto market cap. Track BTC dominance vs Ethereum and altcoins live.";
        $seo->canonical   = route('market.bitcoin-dominance');

        return view('market.bitcoin-dominance', compact('stats', 'top10', 'seo'));
    }

    public function globalMarketCap(GlobalMarketService $gms): View
    {
        $stats = $gms->getGlobalStats();

        $rows = Cache::remember('crypto_top20', self::CACHE_TTL, function () {
            return DB::table('cryptocurrencies')
                ->orderBy('market_cap_rank')
                ->limit(20)
                ->get()
                ->toArray();
        });
        $top20 = $this->hydrate($rows);

        $totalTrillion = number_format($stats['total_market_cap'] / 1e12, 2);

        $seo = new SeoService();
        $seo->title       = 'Global Crypto Market Cap — Total Cryptocurrency Market | CryptoInfo';
        $seo->description = "Total cryptocurrency market cap is \${$totalTrillion}T. Track Bitcoin, Ethereum and altcoin market caps live.";
        $seo->canonical   = route('market.global-cap');

        return view('market.global-cap', compact('stats', 'top20', 'seo'));
    }

    private function hydrate(array $rows): \Illuminate\Support\Collection
    {
        return collect($rows)->map(function (array|object $row) {
            return (new Cryptocurrency())->forceFill((array) $row);
        });
    }
}
