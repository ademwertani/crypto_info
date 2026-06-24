@extends('layouts.app')
@section('content')

<div class="max-w-3xl mx-auto">

    <nav class="text-xs text-slate-500 mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center gap-1.5">
            <li><a href="{{ route('crypto.index') }}" class="hover:text-white transition">Home</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-slate-300">Our Data Methodology</li>
        </ol>
    </nav>

    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-white mb-2">Our Data Methodology</h1>
        <p class="text-slate-400">How CryptoInfo collects, processes and displays cryptocurrency market data.</p>
        <p class="text-xs text-slate-600 mt-2">Last updated: {{ date('F Y') }}</p>
    </div>

    <div class="space-y-6">

        <div class="glass rounded-2xl p-6">
            <h2 class="text-lg font-bold text-white mb-4">1. Data Sources</h2>
            <div class="space-y-4 text-sm text-slate-400">
                <div>
                    <p class="font-semibold text-slate-200 mb-1">CoinGecko API (Primary Source)</p>
                    <p>All cryptocurrency price data is fetched from the <strong class="text-slate-300">CoinGecko public API</strong> (<code class="rounded bg-slate-800 px-1 text-xs">api.coingecko.com/api/v3</code>).</p>
                    <ul class="mt-2 space-y-1 text-xs list-none">
                        <li>→ Endpoint used: <code class="rounded bg-slate-800 px-1">/coins/markets</code> with <code class="rounded bg-slate-800 px-1">vs_currency=usd</code></li>
                        <li>→ Fields: name, symbol, slug, current_price, market_cap, total_volume, circulating_supply, ath, price_change_1h/24h/7d, market_cap_rank, image</li>
                        <li>→ Coverage: Top 250 coins by market cap</li>
                        <li>→ Global stats: <code class="rounded bg-slate-800 px-1">/global</code> endpoint (total market cap, BTC/ETH dominance, active coins)</li>
                    </ul>
                </div>
                <div>
                    <p class="font-semibold text-slate-200 mb-1">Alternative.me (Fear & Greed Index)</p>
                    <p>The <a href="{{ route('market.fear-greed') }}" class="text-blue-400 hover:underline">Fear & Greed Index</a> is fetched from <code class="rounded bg-slate-800 px-1 text-xs">api.alternative.me/fng/?limit=1</code>. This is a third-party sentiment score — CryptoInfo does not calculate or modify it.</p>
                </div>
            </div>
        </div>

        <div class="glass rounded-2xl p-6">
            <h2 class="text-lg font-bold text-white mb-4">2. Data Freshness & Caching</h2>
            <div class="space-y-3 text-sm text-slate-400 leading-relaxed">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="text-[10px] uppercase tracking-wider text-slate-500 border-b border-slate-800">
                                <th class="py-2 text-left">Data Type</th>
                                <th class="py-2 text-right">Fetch Frequency</th>
                                <th class="py-2 text-right">Cache TTL</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 text-slate-400">
                            <tr><td class="py-2">Cryptocurrency prices</td><td class="py-2 text-right">Every 10 min (artisan command)</td><td class="py-2 text-right">5 min (page cache)</td></tr>
                            <tr><td class="py-2">Global market stats</td><td class="py-2 text-right">On demand (per request)</td><td class="py-2 text-right">10 min</td></tr>
                            <tr><td class="py-2">Fear & Greed Index</td><td class="py-2 text-right">On demand (per request)</td><td class="py-2 text-right">10 min</td></tr>
                            <tr><td class="py-2">Gainers / Losers / Trending</td><td class="py-2 text-right">Derived from price data</td><td class="py-2 text-right">5 min</td></tr>
                            <tr><td class="py-2">AI Price Explanations</td><td class="py-2 text-right">When price change &gt; 2%</td><td class="py-2 text-right">30 min</td></tr>
                        </tbody>
                    </table>
                </div>
                <p class="text-xs">Caching is implemented with <strong class="text-slate-300">Laravel Cache</strong> (file-based in development, Redis in production). Prices displayed on the homepage may be up to 5 minutes old. The WebSocket live layer (<a href="#realtime" class="text-blue-400 hover:underline">see §4</a>) reduces perceived latency.</p>
            </div>
        </div>

        <div class="glass rounded-2xl p-6">
            <h2 class="text-lg font-bold text-white mb-4">3. Price Calculation</h2>
            <div class="space-y-2 text-sm text-slate-400 leading-relaxed">
                <p>CryptoInfo does <strong class="text-slate-300">not</strong> calculate prices. All prices are provided by CoinGecko which aggregates prices across hundreds of exchanges using a volume-weighted average.</p>
                <p>Percentage changes (1h, 24h, 7d) are also provided by CoinGecko and represent the price change over the specified period relative to the price at the start of that period.</p>
                <p>Market cap = current_price × circulating_supply (as reported by CoinGecko).</p>
            </div>
        </div>

        <div id="realtime" class="glass rounded-2xl p-6">
            <h2 class="text-lg font-bold text-white mb-4">4. Real-Time Updates</h2>
            <div class="space-y-2 text-sm text-slate-400 leading-relaxed">
                <p>When new price data is fetched by the <code class="rounded bg-slate-800 px-1 text-xs">app:fetch-crypto-data</code> artisan command, a <strong class="text-slate-300">WebSocket broadcast</strong> is sent via <strong class="text-slate-300">Laravel Reverb</strong> to all connected browsers.</p>
                <p>Browsers receive the <code class="rounded bg-slate-800 px-1 text-xs">PriceUpdated</code> event via <strong class="text-slate-300">Laravel Echo</strong> on the public <code class="rounded bg-slate-800 px-1 text-xs">crypto-prices</code> channel. Alpine.js <code class="rounded bg-slate-800 px-1 text-xs">$store.liveprices</code> updates the DOM reactively, flashing green (price up) or red (price down).</p>
            </div>
        </div>

        <div class="glass rounded-2xl p-6">
            <h2 class="text-lg font-bold text-white mb-4">5. Gainers, Losers & Trending</h2>
            <div class="space-y-2 text-sm text-slate-400 leading-relaxed">
                <p><strong class="text-slate-300">Gainers</strong>: Top 50 coins ordered by descending 24h price change percentage.</p>
                <p><strong class="text-slate-300">Losers</strong>: Top 50 coins ordered by ascending 24h price change percentage.</p>
                <p><strong class="text-slate-300">Trending</strong>: Top 50 coins ordered by descending 24h trading volume.</p>
                <p class="text-xs text-slate-600">Note: "Trending" is a proxy metric (volume) — not the same as CoinGecko's own trending list which uses search volume.</p>
            </div>
        </div>

        <div class="glass rounded-2xl p-6">
            <h2 class="text-lg font-bold text-white mb-3">6. Corrections & Errors</h2>
            <p class="text-sm text-slate-400">If you notice data that appears incorrect or delayed, please <a href="{{ route('pages.contact') }}" class="text-blue-400 hover:underline">contact us</a>. We investigate all data quality reports within 24 hours.</p>
        </div>

    </div>
</div>

@endsection
