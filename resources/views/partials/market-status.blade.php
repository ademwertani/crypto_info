{{-- Phase 3 — Live Market Status widget --}}
<aside class="mb-6 animate-fade-in-delay-2" aria-label="Data source status">
    <div class="glass rounded-xl px-5 py-3.5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 shrink-0">Data Sources</p>
            <div class="flex flex-wrap gap-x-5 gap-y-2 text-xs">
                {{-- CoinGecko --}}
                <div class="flex items-center gap-2">
                    <span class="relative flex h-1.5 w-1.5 shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                    </span>
                    <span class="text-slate-400">CoinGecko <span class="text-emerald-400 font-medium">Online</span></span>
                </div>

                {{-- Binance WebSocket --}}
                <div class="flex items-center gap-2">
                    <span x-show="$store.liveprices.connected" x-cloak class="relative flex h-1.5 w-1.5 shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                    </span>
                    <span x-show="!$store.liveprices.connected" x-cloak class="h-1.5 w-1.5 rounded-full bg-slate-600 shrink-0"></span>
                    <span class="text-slate-400">
                        WebSocket
                        <span x-show="$store.liveprices.connected" x-cloak class="text-emerald-400 font-medium">Connected</span>
                        <span x-show="!$store.liveprices.connected" x-cloak class="text-slate-500">Connecting…</span>
                    </span>
                </div>

                {{-- Alt.me / FNG --}}
                <div class="flex items-center gap-2">
                    <span class="relative flex h-1.5 w-1.5 shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                    </span>
                    <span class="text-slate-400">Fear&Greed API <span class="text-emerald-400 font-medium">Online</span></span>
                </div>

                {{-- Last sync --}}
                <div class="flex items-center gap-2 text-slate-500">
                    <svg class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Last sync: <time datetime="{{ now()->toISOString() }}">{{ now()->format('H:i') }} UTC</time></span>
                </div>
            </div>
        </div>
    </div>
</aside>
