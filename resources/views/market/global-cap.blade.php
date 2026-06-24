@extends('layouts.app')
@section('content')

<div class="max-w-5xl mx-auto">

    <nav class="text-xs text-slate-500 mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center gap-1.5">
            <li><a href="{{ route('crypto.index') }}" class="hover:text-white transition">Home</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-slate-300">Global Crypto Market Cap</li>
        </ol>
    </nav>

    @php
        $mcap    = !empty($stats['total_market_cap'])  ? $stats['total_market_cap']  : 0;
        $vol     = !empty($stats['total_volume_24h'])  ? $stats['total_volume_24h']  : 0;
        $btcD    = (float)($stats['btc_dominance']  ?? 0);
        $ethD    = (float)($stats['eth_dominance']  ?? 0);
        $mcChg   = (float)($stats['market_cap_change_24h'] ?? 0);
        $coins   = (int)($stats['active_cryptocurrencies'] ?? 0);
        $markets = (int)($stats['markets'] ?? 0);
    @endphp

    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-white mb-2">Global Crypto Market Cap</h1>
        <p class="text-slate-400">Total value of all cryptocurrencies combined — the broadest measure of crypto market size.</p>
    </div>

    {{-- Hero stats --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="glass stat-card rounded-2xl p-5 text-center">
            <p class="text-[11px] uppercase tracking-wider text-slate-500 mb-2">Total Market Cap</p>
            <p class="text-3xl font-black text-white tabular-nums">${{ number_format($mcap / 1e12, 2) }}T</p>
            @if($mcChg != 0)
                <p class="text-xs {{ $mcChg >= 0 ? 'text-emerald-400' : 'text-red-400' }} mt-1.5 font-medium">
                    {{ $mcChg >= 0 ? '▲' : '▼' }} {{ abs(round($mcChg, 2)) }}% (24h)
                </p>
            @endif
        </div>
        <div class="glass stat-card rounded-2xl p-5 text-center">
            <p class="text-[11px] uppercase tracking-wider text-slate-500 mb-2">24h Volume</p>
            <p class="text-3xl font-black text-white tabular-nums">${{ number_format($vol / 1e9, 1) }}B</p>
            <p class="text-xs text-slate-500 mt-1.5">Global trading volume</p>
        </div>
        <div class="glass stat-card rounded-2xl p-5 text-center">
            <p class="text-[11px] uppercase tracking-wider text-slate-500 mb-2">Active Cryptos</p>
            <p class="text-3xl font-black text-white tabular-nums">{{ $coins ? number_format($coins) : '250+' }}</p>
            <p class="text-xs text-slate-500 mt-1.5">Listed coins</p>
        </div>
        <div class="glass stat-card rounded-2xl p-5 text-center">
            <p class="text-[11px] uppercase tracking-wider text-slate-500 mb-2">Exchanges</p>
            <p class="text-3xl font-black text-white tabular-nums">{{ $markets ? number_format($markets) : '600+' }}</p>
            <p class="text-xs text-slate-500 mt-1.5">Active markets</p>
        </div>
    </div>

    {{-- Dominance visual --}}
    <div class="glass rounded-xl p-5 mb-6">
        <h2 class="text-sm font-semibold text-white mb-3">Market Dominance</h2>
        <div class="h-6 rounded-full overflow-hidden flex mb-3" role="img" aria-label="BTC {{ number_format($btcD,1) }}%, ETH {{ number_format($ethD,1) }}%, Others {{ number_format(max(0,100-$btcD-$ethD),1) }}%">
            <div class="bg-orange-500 h-full transition-all" style="width: {{ $btcD }}%"></div>
            <div class="bg-blue-500 h-full transition-all" style="width: {{ $ethD }}%"></div>
            <div class="bg-slate-600 h-full flex-1"></div>
        </div>
        <div class="grid grid-cols-3 gap-4 text-xs">
            <div class="text-center">
                <div class="flex items-center justify-center gap-1.5 text-orange-400 font-bold text-base mb-0.5">{{ number_format($btcD, 1) }}%</div>
                <p class="text-slate-500">Bitcoin (BTC)</p>
            </div>
            <div class="text-center">
                <div class="flex items-center justify-center gap-1.5 text-blue-400 font-bold text-base mb-0.5">{{ number_format($ethD, 1) }}%</div>
                <p class="text-slate-500">Ethereum (ETH)</p>
            </div>
            <div class="text-center">
                <div class="flex items-center justify-center gap-1.5 text-slate-300 font-bold text-base mb-0.5">{{ number_format(max(0, 100 - $btcD - $ethD), 1) }}%</div>
                <p class="text-slate-500">All others</p>
            </div>
        </div>
    </div>

    {{-- Top 20 --}}
    <div class="glass rounded-xl overflow-hidden mb-6">
        <div class="px-5 py-3.5 border-b border-slate-800/60">
            <h2 class="font-semibold text-white text-sm">Top 20 by Market Cap</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-800/60">
                        <th class="px-4 py-2.5 text-right w-8">#</th>
                        <th class="px-4 py-2.5 text-left">Coin</th>
                        <th class="px-4 py-2.5 text-right">Price</th>
                        <th class="px-4 py-2.5 text-right">24h %</th>
                        <th class="px-4 py-2.5 text-right">Market Cap</th>
                        <th class="px-4 py-2.5 text-right">Vol (24h)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40">
                    @foreach($top20 as $coin)
                    @php $chg = (float)($coin->price_change_percentage_24h_in_currency ?? 0); @endphp
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="px-4 py-3 text-right text-slate-500 font-mono text-xs">{{ $coin->market_cap_rank }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('crypto.show', $coin->slug) }}" class="flex items-center gap-2.5 group">
                                @if($coin->image_url)
                                    <img src="{{ $coin->image_url }}" alt="{{ $coin->name }}" class="h-6 w-6 rounded-full" loading="lazy">
                                @endif
                                <div>
                                    <p class="font-medium text-white group-hover:text-blue-400 transition">{{ $coin->name }}</p>
                                    <p class="text-xs text-slate-500 uppercase">{{ $coin->symbol }}</p>
                                </div>
                            </a>
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums text-white font-medium">
                            ${{ number_format((float)$coin->current_price, $coin->current_price >= 1 ? 2 : 6) }}
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums {{ $chg >= 0 ? 'text-emerald-400' : 'text-red-400' }} font-medium">
                            {{ $chg >= 0 ? '▲' : '▼' }} {{ abs($chg) }}%
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums text-slate-300">
                            ${{ number_format((float)$coin->market_cap / 1e9, 2) }}B
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums text-slate-400">
                            ${{ number_format((float)$coin->total_volume / 1e9, 2) }}B
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Explanation --}}
    <div class="glass rounded-xl p-6">
        <h2 class="font-semibold text-white mb-3">Understanding Global Market Cap</h2>
        <div class="space-y-2 text-sm text-slate-400 leading-relaxed">
            <p>The <strong class="text-slate-300">global crypto market cap</strong> is calculated by summing (current price × circulating supply) for all listed cryptocurrencies. It represents the total market value of the entire crypto ecosystem.</p>
            <p><strong class="text-slate-300">Why it matters:</strong> Market cap is the primary metric for comparing the relative size of cryptocurrencies and assessing overall market health. A rising global market cap indicates capital inflows; a falling one reflects outflows or price declines.</p>
            <p class="text-xs text-slate-600 mt-2">Data: <a href="https://www.coingecko.com/en/global-charts" target="_blank" rel="noopener" class="text-blue-500 hover:underline">CoinGecko Global Charts</a> · Updated every 10 minutes.</p>
        </div>
    </div>

</div>
@endsection
