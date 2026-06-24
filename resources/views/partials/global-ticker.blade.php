{{-- Global market ticker bar — injected at the very top of every page --}}
@php
    $ticker = Cache::remember('global_ticker_stats', 300, function () {
        try {
            $svc = app(\App\Services\GlobalMarketService::class);
            return $svc->getGlobalStats();
        } catch (\Throwable $e) {
            return [];
        }
    });
    $mcap   = !empty($ticker['total_market_cap'])   ? '$' . number_format($ticker['total_market_cap'] / 1e12, 2) . 'T'  : '—';
    $vol    = !empty($ticker['total_volume_24h'])    ? '$' . number_format($ticker['total_volume_24h'] / 1e9, 1)  . 'B'  : '—';
    $btcD   = !empty($ticker['btc_dominance'])       ? number_format($ticker['btc_dominance'], 1) . '%' : '—';
    $ethD   = !empty($ticker['eth_dominance'])       ? number_format($ticker['eth_dominance'], 1) . '%' : '—';
    $fng    = $ticker['fng_value']          ?? null;
    $fngLbl = $ticker['fng_classification'] ?? '';
    $coins  = !empty($ticker['active_cryptocurrencies']) ? number_format($ticker['active_cryptocurrencies']) : '250+';
    $mcChg  = $ticker['market_cap_change_24h'] ?? 0;
    $mcChgColor = $mcChg >= 0 ? 'text-emerald-400' : 'text-red-400';
    $mcChgSign  = $mcChg >= 0 ? '+' : '';
@endphp

<div class="border-b border-slate-800/60 bg-slate-900/80 backdrop-blur-sm text-xs text-slate-400" role="status" aria-label="Live global market stats">
    <div class="mx-auto max-w-screen-xl px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-8 gap-6 overflow-x-auto scrollbar-none">

            {{-- Static items --}}
            <div class="flex items-center gap-6 shrink-0">
                <span class="flex items-center gap-1.5 whitespace-nowrap">
                    <span class="text-slate-500">Cryptos:</span>
                    <span class="font-medium text-slate-300">{{ $coins }}</span>
                </span>
                <span class="hidden sm:flex items-center gap-1.5 whitespace-nowrap">
                    <span class="text-slate-500">Market Cap:</span>
                    <span class="font-medium text-slate-300">{{ $mcap }}</span>
                    @if($mcChg != 0)
                        <span class="font-medium {{ $mcChgColor }}">{{ $mcChgSign }}{{ number_format($mcChg, 1) }}%</span>
                    @endif
                </span>
                <span class="hidden md:flex items-center gap-1.5 whitespace-nowrap">
                    <span class="text-slate-500">24h Vol:</span>
                    <span class="font-medium text-slate-300">{{ $vol }}</span>
                </span>
                <a href="{{ route('market.bitcoin-dominance') }}" class="hidden lg:flex items-center gap-1.5 whitespace-nowrap hover:text-blue-400 transition">
                    <span class="text-slate-500">BTC Dom:</span>
                    <span class="font-medium text-orange-400">{{ $btcD }}</span>
                </a>
                <span class="hidden xl:flex items-center gap-1.5 whitespace-nowrap">
                    <span class="text-slate-500">ETH Dom:</span>
                    <span class="font-medium text-blue-400">{{ $ethD }}</span>
                </span>
                @if($fng !== null)
                    <a href="{{ route('market.fear-greed') }}" class="hidden lg:flex items-center gap-1.5 whitespace-nowrap hover:text-white transition">
                        <span class="text-slate-500">F&G:</span>
                        <span class="font-semibold {{ \App\Services\GlobalMarketService::fngColor((int)$fng) }}">{{ $fng }} — {{ $fngLbl }}</span>
                    </a>
                @endif
            </div>

            {{-- Right side --}}
            <div class="flex items-center gap-4 shrink-0 ml-auto">
                <span class="hidden sm:flex items-center gap-1.5 whitespace-nowrap">
                    <span class="relative flex h-1.5 w-1.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                    </span>
                    <span class="text-emerald-400 font-medium">Live</span>
                </span>
                <a href="{{ route('pages.methodology') }}" class="hidden md:block whitespace-nowrap hover:text-white transition">
                    Data by CoinGecko
                </a>
            </div>
        </div>
    </div>
</div>
