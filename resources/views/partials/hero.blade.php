{{-- Phase 1 — Premium hero section for homepage (page 1, no search) --}}
@php
    $mcap   = !empty($stats['total_market_cap'])   ? '$' . number_format($stats['total_market_cap'] / 1e12, 2) . 'T'  : '$2.4T';
    $vol    = !empty($stats['total_volume_24h'])    ? '$' . number_format($stats['total_volume_24h'] / 1e9, 1)  . 'B'  : '$94B';
    $btcD   = !empty($stats['btc_dominance'])       ? number_format($stats['btc_dominance'], 1) . '%' : '53%';
    $coins  = !empty($stats['active_cryptocurrencies']) ? number_format($stats['active_cryptocurrencies']) : '250+';
    $fng    = $stats['fng_value'] ?? 50;
    $fngLbl = $stats['fng_classification'] ?? 'Neutral';
    $fngColor = \App\Services\GlobalMarketService::fngColor((int)$fng);
    $mcChg  = $stats['market_cap_change_24h'] ?? 0;
    $mcChgColor = $mcChg >= 0 ? 'text-emerald-400' : 'text-red-400';
    $mcChgSign  = $mcChg >= 0 ? '▲' : '▼';
@endphp

<section class="relative overflow-hidden rounded-2xl mb-6 animate-fade-in" aria-label="Market overview">
    {{-- Background gradient --}}
    <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-blue-950/30 to-slate-900 pointer-events-none" aria-hidden="true"></div>
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-blue-900/20 via-transparent to-transparent pointer-events-none" aria-hidden="true"></div>

    <div class="relative px-6 py-8 lg:px-10 lg:py-10">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">

            {{-- Left: Headline --}}
            <div class="max-w-xl">
                <div class="flex items-center gap-2 mb-3">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-500/10 border border-blue-500/20 px-3 py-1 text-xs font-semibold text-blue-400">
                        <span class="relative flex h-1.5 w-1.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-blue-500"></span>
                        </span>
                        Real-Time Market Data
                    </span>
                    <span class="text-xs text-slate-500">Updated every 10 min</span>
                </div>

                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold leading-tight mb-2">
                    <span class="gradient-text">Real-Time Crypto</span><br>
                    <span class="text-white">Intelligence</span>
                </h1>
                <p class="text-slate-400 text-sm leading-relaxed">
                    Track live prices, market caps, volume and sentiment for <strong class="text-slate-300">{{ $coins }} cryptocurrencies</strong>.
                    Powered by CoinGecko · Free · No registration required.
                </p>
            </div>

            {{-- Right: Key metrics grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-2 xl:grid-cols-4 gap-3 lg:shrink-0">
                {{-- Market Cap --}}
                <a href="{{ route('market.global-cap') }}"
                   class="glass stat-card rounded-xl px-4 py-3 group" aria-label="Total market cap">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Market Cap</p>
                    <p class="text-lg font-bold text-white tabular-nums">{{ $mcap }}</p>
                    @if($mcChg != 0)
                        <p class="text-xs {{ $mcChgColor }} mt-0.5 font-medium">
                            {{ $mcChgSign }} {{ abs(round($mcChg, 1)) }}% (24h)
                        </p>
                    @endif
                </a>

                {{-- 24h Volume --}}
                <div class="glass stat-card rounded-xl px-4 py-3">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 mb-1">24h Volume</p>
                    <p class="text-lg font-bold text-white tabular-nums">{{ $vol }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Global trading</p>
                </div>

                {{-- BTC Dominance --}}
                <a href="{{ route('market.bitcoin-dominance') }}"
                   class="glass stat-card rounded-xl px-4 py-3 group" aria-label="Bitcoin dominance">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 mb-1">BTC Dominance</p>
                    <p class="text-lg font-bold text-orange-400 tabular-nums">{{ $btcD }}</p>
                    <div class="mt-1.5 h-1 rounded-full bg-slate-700">
                        <div class="h-1 rounded-full bg-gradient-to-r from-orange-500 to-orange-400 transition-all"
                             style="width: {{ $btcD }}" aria-hidden="true"></div>
                    </div>
                </a>

                {{-- Fear & Greed --}}
                <a href="{{ route('market.fear-greed') }}"
                   class="glass stat-card rounded-xl px-4 py-3 group" aria-label="Fear and greed index: {{ $fng }}">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Fear & Greed</p>
                    <p class="text-lg font-bold {{ $fngColor }} tabular-nums">{{ $fng }}/100</p>
                    <p class="text-xs {{ $fngColor }} mt-0.5 font-medium">{{ $fngLbl }}</p>
                </a>
            </div>
        </div>

        {{-- Quick links --}}
        <div class="flex flex-wrap gap-2 mt-6 pt-5 border-t border-slate-800/60">
            <span class="text-xs text-slate-600 self-center mr-1">Quick:</span>
            @foreach([
                ['label' => '🔥 Trending',       'route' => 'market.trending'],
                ['label' => '📈 Top Gainers',     'route' => 'market.gainers'],
                ['label' => '📉 Top Losers',      'route' => 'market.losers'],
                ['label' => '🌍 Market Cap',      'route' => 'market.global-cap'],
                ['label' => '⚖️ Compare',         'route' => 'crypto.compare', 'params' => ['slugA' => 'bitcoin', 'slugB' => 'ethereum']],
            ] as $link)
                <a href="{{ isset($link['params']) ? route($link['route'], $link['params']) : route($link['route']) }}"
                   class="rounded-lg bg-slate-800/60 border border-slate-700/40 px-3 py-1.5 text-xs text-slate-300 hover:bg-slate-700 hover:text-white hover:border-slate-600 transition-all">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</section>
