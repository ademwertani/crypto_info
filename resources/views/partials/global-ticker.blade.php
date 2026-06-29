{{-- Global market ticker bar — injected at the very top of every page --}}
@php
    $ticker = Cache::remember('global_ticker_stats', 300, function () {
        try {
            return app(\App\Services\GlobalMarketService::class)->getGlobalStats();
        } catch (\Throwable $e) {
            return [];
        }
    });
    $coins = !empty($ticker['active_cryptocurrencies']) ? number_format($ticker['active_cryptocurrencies']) : '250+';
    $mcap  = !empty($ticker['total_market_cap']) ? '$' . number_format($ticker['total_market_cap'] / 1e12, 2) . 'T' : '—';
@endphp

<div class="border-b border-slate-800/60 bg-slate-900/80 backdrop-blur-sm text-xs text-slate-400" role="status" aria-label="Live global market stats">
    <div class="mx-auto max-w-screen-xl px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-8 gap-4 overflow-x-auto scrollbar-none">
            <div class="flex items-center gap-4 shrink-0">
                <span class="whitespace-nowrap text-slate-300">{{ $coins }} cryptos</span>
                <span class="hidden sm:inline whitespace-nowrap">{{ $mcap }} cap</span>
            </div>
            <span class="hidden sm:inline whitespace-nowrap text-emerald-400 font-medium">Live</span>
        </div>
    </div>
</div>
