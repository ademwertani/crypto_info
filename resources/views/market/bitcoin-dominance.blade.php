@extends('layouts.app')
@section('content')

<div class="max-w-4xl mx-auto">

    {{-- Breadcrumb --}}
    <nav class="text-xs text-slate-500 mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center gap-1.5">
            <li><a href="{{ route('crypto.index') }}" class="hover:text-white transition">Home</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-slate-300">Bitcoin Dominance</li>
        </ol>
    </nav>

    @php
        $btcD    = (float)($stats['btc_dominance'] ?? 0);
        $ethD    = (float)($stats['eth_dominance'] ?? 0);
        $otherD  = max(0, 100 - $btcD - $ethD);
        $mcap    = !empty($stats['total_market_cap']) ? '$' . number_format($stats['total_market_cap'] / 1e12, 2) . 'T' : '—';
        $btcMcap = $top10->firstWhere('slug', 'bitcoin');
        $ethMcap = $top10->firstWhere('slug', 'ethereum');
    @endphp

    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-white mb-2">Bitcoin Dominance</h1>
        <p class="text-slate-400">BTC's share of the total crypto market cap — a key sentiment indicator.</p>
    </div>

    {{-- Main stat cards --}}
    <div class="grid sm:grid-cols-3 gap-4 mb-6">
        <div class="glass rounded-2xl p-6 text-center border-orange-500/10">
            <p class="text-[11px] uppercase tracking-wider text-slate-500 mb-2">BTC Dominance</p>
            <p class="text-5xl font-black text-orange-400 tabular-nums">{{ number_format($btcD, 1) }}%</p>
            @if($btcMcap)
                <p class="text-xs text-slate-500 mt-2">${{ number_format((float)$btcMcap->market_cap / 1e9, 1) }}B market cap</p>
            @endif
        </div>
        <div class="glass rounded-2xl p-6 text-center">
            <p class="text-[11px] uppercase tracking-wider text-slate-500 mb-2">ETH Dominance</p>
            <p class="text-5xl font-black text-blue-400 tabular-nums">{{ number_format($ethD, 1) }}%</p>
            @if($ethMcap)
                <p class="text-xs text-slate-500 mt-2">${{ number_format((float)$ethMcap->market_cap / 1e9, 1) }}B market cap</p>
            @endif
        </div>
        <div class="glass rounded-2xl p-6 text-center">
            <p class="text-[11px] uppercase tracking-wider text-slate-500 mb-2">Others (Altcoins)</p>
            <p class="text-5xl font-black text-slate-300 tabular-nums">{{ number_format($otherD, 1) }}%</p>
            <p class="text-xs text-slate-500 mt-2">Total market: {{ $mcap }}</p>
        </div>
    </div>

    {{-- Stacked bar chart --}}
    <div class="glass rounded-xl p-5 mb-6" aria-label="Market dominance breakdown">
        <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">Market Dominance Breakdown</h2>
        <div class="h-8 rounded-full overflow-hidden flex" role="img" aria-label="BTC {{ number_format($btcD,1) }}%, ETH {{ number_format($ethD,1) }}%, Others {{ number_format($otherD,1) }}%">
            <div class="bg-orange-500 h-full transition-all" style="width: {{ $btcD }}%" title="Bitcoin {{ number_format($btcD,1) }}%"></div>
            <div class="bg-blue-500 h-full transition-all" style="width: {{ $ethD }}%" title="Ethereum {{ number_format($ethD,1) }}%"></div>
            <div class="bg-slate-600 h-full flex-1" title="Others {{ number_format($otherD,1) }}%"></div>
        </div>
        <div class="flex gap-5 mt-3 text-xs text-slate-400">
            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-orange-500 shrink-0"></span> Bitcoin {{ number_format($btcD, 1) }}%</span>
            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-blue-500 shrink-0"></span> Ethereum {{ number_format($ethD, 1) }}%</span>
            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-slate-600 shrink-0"></span> Altcoins {{ number_format($otherD, 1) }}%</span>
        </div>
    </div>

    {{-- Top 10 table --}}
    <div class="glass rounded-xl overflow-hidden mb-6">
        <div class="px-5 py-3.5 border-b border-slate-800/60">
            <h2 class="font-semibold text-white text-sm">Top 10 Coins by Market Cap</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-800/60">
                        <th class="px-5 py-2.5 text-right w-8">#</th>
                        <th class="px-5 py-2.5 text-left">Coin</th>
                        <th class="px-5 py-2.5 text-right">Price</th>
                        <th class="px-5 py-2.5 text-right">24h</th>
                        <th class="px-5 py-2.5 text-right">Market Cap</th>
                        <th class="px-5 py-2.5 text-right">Dominance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40">
                    @foreach($top10 as $coin)
                    @php
                        $totalMcap = $stats['total_market_cap'] ?? 1;
                        $coinDom   = $coin->market_cap ? ($coin->market_cap / $totalMcap * 100) : 0;
                        $chg       = (float)($coin->price_change_percentage_24h_in_currency ?? 0);
                    @endphp
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="px-5 py-3 text-right text-slate-500 font-mono text-xs">{{ $coin->market_cap_rank }}</td>
                        <td class="px-5 py-3">
                            <a href="{{ route('crypto.show', $coin->slug) }}" class="flex items-center gap-3 group">
                                @if($coin->image_url)
                                    <img src="{{ $coin->image_url }}" alt="{{ $coin->name }}" class="h-7 w-7 rounded-full" loading="lazy" width="28" height="28">
                                @endif
                                <div>
                                    <p class="font-semibold text-white group-hover:text-blue-400 transition">{{ $coin->name }}</p>
                                    <p class="text-xs text-slate-500 uppercase">{{ $coin->symbol }}</p>
                                </div>
                            </a>
                        </td>
                        <td class="px-5 py-3 text-right font-medium tabular-nums text-white">
                            ${{ number_format((float)$coin->current_price, $coin->current_price >= 1 ? 2 : 6) }}
                        </td>
                        <td class="px-5 py-3 text-right tabular-nums {{ $chg >= 0 ? 'text-emerald-400' : 'text-red-400' }} font-medium">
                            {{ $chg >= 0 ? '▲' : '▼' }} {{ abs($chg) }}%
                        </td>
                        <td class="px-5 py-3 text-right tabular-nums text-slate-300">
                            ${{ number_format((float)$coin->market_cap / 1e9, 1) }}B
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <div class="w-16 h-1.5 rounded-full bg-slate-700 overflow-hidden" aria-hidden="true">
                                    <div class="h-full rounded-full bg-blue-500" style="width: {{ min(100, $coinDom * 2) }}%"></div>
                                </div>
                                <span class="text-slate-300 font-medium tabular-nums w-10 text-right">{{ number_format($coinDom, 1) }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Explanation --}}
    <div class="glass rounded-xl p-6">
        <h2 class="font-semibold text-white mb-3">What does Bitcoin dominance mean?</h2>
        <div class="space-y-2 text-sm text-slate-400 leading-relaxed">
            <p><strong class="text-slate-300">High BTC dominance (60%+)</strong> typically signals a "Bitcoin season" — investors prefer the relative safety of BTC over riskier altcoins. Often occurs during bear markets or periods of uncertainty.</p>
            <p><strong class="text-slate-300">Low BTC dominance (below 40%)</strong> usually indicates an "altcoin season" — capital is rotating from Bitcoin into Ethereum and smaller coins, driven by DeFi, NFTs, or new narratives.</p>
            <p class="text-xs text-slate-600 mt-3">Data updated every 10 minutes from <a href="https://www.coingecko.com" target="_blank" rel="noopener" class="text-blue-500 hover:underline">CoinGecko</a>. See our <a href="{{ route('pages.methodology') }}" class="text-blue-500 hover:underline">methodology</a>.</p>
        </div>
    </div>

</div>
@endsection
