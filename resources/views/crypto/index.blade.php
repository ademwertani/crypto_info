@extends('layouts.app')
@section('content')

{{-- Phase 1-3: Hero + trust indicators + market status (page 1, no search) --}}
@if(!empty($stats) && $search === '')
    @include('partials.hero', ['stats' => $stats])
    @include('partials.trust-indicators')
    @include('partials.market-status')
@endif

@include('partials.ad-rectangle', ['position' => 'market-top'])

<div class="mb-5 flex items-end justify-between gap-4 flex-wrap">
    <div>
        <h2 class="text-xl font-bold text-white">Today's Cryptocurrency Prices</h2>
        <p class="text-sm text-slate-400 mt-0.5">
            Showing {{ $cryptos->firstItem() }}–{{ $cryptos->lastItem() }} of {{ number_format($cryptos->total()) }} assets
            &nbsp;·&nbsp;
            <span x-show="$store.liveprices.binanceConnected" x-cloak class="inline-flex items-center gap-1 text-emerald-400">
                <span class="relative flex h-1.5 w-1.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                </span>
                Live via Binance
            </span>
            <span x-show="!$store.liveprices.binanceConnected && $store.liveprices.reverbConnected" x-cloak class="text-blue-400">Live</span>
            <span x-show="!$store.liveprices.connected" x-cloak class="text-slate-500">Synced every 10 min</span>
        </p>
    </div>
    <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="{{ route('market.gainers') }}"  class="rounded-lg border border-slate-700 px-3 py-1.5 text-xs text-slate-400 hover:text-emerald-400 hover:border-emerald-700/60 transition">📈 Gainers</a>
        <a href="{{ route('market.losers') }}"   class="rounded-lg border border-slate-700 px-3 py-1.5 text-xs text-slate-400 hover:text-red-400 hover:border-red-900/60 transition">📉 Losers</a>
        <a href="{{ route('market.trending') }}" class="rounded-lg border border-slate-700 px-3 py-1.5 text-xs text-slate-400 hover:text-blue-400 hover:border-blue-800/60 transition">🔥 Trending</a>
        <a href="{{ route('crypto.compare', ['slugA'=>'bitcoin','slugB'=>'ethereum']) }}"
           class="rounded-lg border border-blue-800/40 bg-blue-950/20 px-3 py-1.5 text-xs text-blue-400 hover:bg-blue-900/30 transition">⚖️ Compare</a>
    </div>
</div>

@if ($search !== '')
    <div class="mb-4 flex items-center gap-2 text-sm text-slate-400">
        <span>Results for <span class="font-semibold text-white">"{{ e($search) }}"</span></span>
        <a href="{{ route('crypto.index') }}" class="ml-2 rounded border border-slate-700 px-2 py-0.5 text-xs hover:border-slate-500 hover:text-white transition">✕ Clear</a>
    </div>
@endif

@if ($cryptos->isEmpty())
    <div class="flex flex-col items-center justify-center gap-3 py-20 text-slate-500">
        <p class="font-medium">No cryptocurrencies found.</p>
        @if ($search !== '')
            <a href="{{ route('crypto.index') }}" class="text-blue-400 hover:underline text-sm">View all →</a>
        @else
            <p class="text-sm">Run <code class="rounded bg-slate-800 px-1">php artisan app:fetch-crypto-data</code></p>
        @endif
    </div>
@else

<div class="overflow-x-auto rounded-xl border border-slate-800/80 bg-slate-900/40">
    <table class="w-full text-sm" aria-label="Cryptocurrency prices">
        <thead>
            <tr class="border-b border-slate-800 bg-slate-900/80 text-[11px] uppercase tracking-wider text-slate-500">
                <th scope="col" class="px-4 py-3 text-right w-10">#</th>
                <th scope="col" class="px-4 py-3 text-left min-w-[180px]">Name</th>
                <th scope="col" class="px-4 py-3 text-right">Price</th>
                <th scope="col" class="px-4 py-3 text-right w-16">1h %</th>
                <th scope="col" class="px-4 py-3 text-right w-16">24h %</th>
                <th scope="col" class="px-4 py-3 text-right w-16">7d %</th>
                <th scope="col" class="px-4 py-3 text-right min-w-[110px]">Market Cap</th>
                <th scope="col" class="px-4 py-3 text-right min-w-[110px]">Volume (24h)</th>
                <th scope="col" class="hidden lg:table-cell px-4 py-3 text-center w-24">7d Chart</th>
                <th scope="col" class="hidden xl:table-cell px-4 py-3 text-center w-16">Compare</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-800/50">
            @foreach ($cryptos as $coin)
            @php
                $spark  = $coin->sparkline_7d ? (is_string($coin->sparkline_7d) ? json_decode($coin->sparkline_7d, true) : $coin->sparkline_7d) : [];
                $chg7d  = (float)($coin->price_change_percentage_7d_in_currency ?? 0);
            @endphp
            <tr class="group hover:bg-slate-800/30 transition-colors cursor-pointer coin-row"
                x-data="priceRow('{{ $coin->slug }}', {{ (float)$coin->current_price }}, {{ (float)($coin->price_change_percentage_24h_in_currency ?? 0) }})"
                x-init="init()"
                :class="flashClass"
                tabindex="0"
                aria-label="View {{ e($coin->name) }} price details"
                onclick="window.location='{{ route('crypto.show', $coin->slug) }}'"
                onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();window.location='{{ route('crypto.show', $coin->slug) }}';}">

                <td class="px-4 py-3.5 text-right text-slate-500 font-mono text-xs">{{ $coin->market_cap_rank ?? '—' }}</td>

                <td class="px-4 py-3.5">
                    <a href="{{ route('crypto.show', $coin->slug) }}" class="flex items-center gap-3" onclick="event.stopPropagation()">
                        @if ($coin->image_url)
                            <img src="{{ $coin->image_url }}" alt="{{ e($coin->name) }}" class="h-7 w-7 rounded-full shrink-0" loading="lazy" width="28" height="28">
                        @else
                            <div class="h-7 w-7 rounded-full bg-slate-700 flex items-center justify-center text-xs font-bold shrink-0" aria-hidden="true">{{ strtoupper(substr($coin->symbol,0,2)) }}</div>
                        @endif
                        <div>
                            <p class="font-semibold text-white group-hover:text-blue-400 transition-colors leading-tight">{{ e($coin->name) }}</p>
                            <p class="text-[11px] text-slate-500 uppercase font-medium">{{ e($coin->symbol) }}</p>
                        </div>
                    </a>
                </td>

                {{-- Live price via Binance --}}
                <td class="px-4 py-3.5 text-right font-semibold tabular-nums text-white" x-text="formattedPrice"></td>

                {{-- 1h change --}}
                <td class="px-4 py-3.5 text-right tabular-nums">
                    @if ($coin->price_change_percentage_1h_in_currency !== null)
                        <x-percent-badge :value="$coin->price_change_percentage_1h_in_currency" />
                    @else <span class="text-slate-600">—</span> @endif
                </td>

                {{-- 24h change — live via Binance --}}
                <td class="px-4 py-3.5 text-right tabular-nums">
                    <span :class="change24hColor" x-text="change24hLabel" class="font-medium"></span>
                </td>

                {{-- 7d change --}}
                <td class="px-4 py-3.5 text-right tabular-nums">
                    @if ($coin->price_change_percentage_7d_in_currency !== null)
                        <x-percent-badge :value="$coin->price_change_percentage_7d_in_currency" />
                    @else <span class="text-slate-600">—</span> @endif
                </td>

                {{-- Market Cap --}}
                <td class="px-4 py-3.5 text-right tabular-nums text-slate-300">
                    @if ($coin->market_cap) ${{ number_format((float)$coin->market_cap/1e9, 2) }}B
                    @else <span class="text-slate-600">—</span> @endif
                </td>

                {{-- Volume --}}
                <td class="px-4 py-3.5 text-right tabular-nums text-slate-300">
                    @if ($coin->total_volume) ${{ number_format((float)$coin->total_volume/1e9, 2) }}B
                    @else <span class="text-slate-600">—</span> @endif
                </td>

                {{-- Sparkline 7d --}}
                <td class="hidden lg:table-cell px-4 py-3.5 text-center">
                    @if(!empty($spark))
                        <svg class="sparkline mx-auto"
                             width="80" height="32"
                             data-prices="{{ json_encode(array_values($spark)) }}"
                             data-change="{{ $chg7d }}"
                             aria-label="{{ $chg7d >= 0 ? 'Up' : 'Down' }} {{ abs($chg7d) }}% in 7 days"
                             role="img">
                        </svg>
                    @else
                        <span class="text-slate-700 text-xs">—</span>
                    @endif
                </td>

                {{-- Compare shortcut --}}
                <td class="hidden xl:table-cell px-4 py-3 text-center"
                    onclick="event.stopPropagation()"
                    onkeydown="event.stopPropagation()">
                    <a href="{{ route('crypto.compare', ['slugA' => $coin->slug, 'slugB' => 'bitcoin']) }}"
                       class="rounded-md border border-slate-700/50 px-2 py-1 text-[10px] text-slate-500 hover:border-blue-600/50 hover:text-blue-400 transition"
                       title="Compare {{ $coin->name }} vs Bitcoin">
                        vs BTC
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if ($cryptos->hasPages())
    <div class="mt-6 flex justify-center">
        {{ $cryptos->appends(['search' => $search])->links() }}
    </div>
@endif

@endif
@endsection

@push('scripts')
<script>
// ── Sparkline renderer ───────────────────────────────────────────────────
(function renderSparklines() {
    const svgs = document.querySelectorAll('svg.sparkline');
    svgs.forEach(svg => {
        const prices = JSON.parse(svg.dataset.prices || '[]');
        const change = Number.parseFloat(svg.dataset.change || '0');
        if (prices.length < 2) return;

        const W = 80, H = 32, PAD = 2;
        const min = Math.min(...prices);
        const max = Math.max(...prices);
        const rng = max - min || 1;

        const pts = prices.map((p, i) => {
            const x = PAD + (i / (prices.length - 1)) * (W - PAD * 2);
            const y = PAD + (1 - (p - min) / rng) * (H - PAD * 2);
            return `${x.toFixed(1)},${y.toFixed(1)}`;
        });

        const color = change >= 0 ? '#10b981' : '#ef4444';
        const fillColor = change >= 0 ? 'rgba(16,185,129,0.08)' : 'rgba(239,68,68,0.08)';

        // Filled area
        const first = pts[0].split(',');
        const last  = pts[pts.length - 1].split(',');
        const polygon = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
        polygon.setAttribute('points', [
            `${PAD},${H - PAD}`,
            ...pts,
            `${W - PAD},${H - PAD}`
        ].join(' '));
        polygon.setAttribute('fill', fillColor);
        svg.appendChild(polygon);

        // Line
        const polyline = document.createElementNS('http://www.w3.org/2000/svg', 'polyline');
        polyline.setAttribute('points', pts.join(' '));
        polyline.setAttribute('fill', 'none');
        polyline.setAttribute('stroke', color);
        polyline.setAttribute('stroke-width', '1.5');
        polyline.setAttribute('stroke-linejoin', 'round');
        polyline.setAttribute('stroke-linecap', 'round');
        svg.appendChild(polyline);
    });
})();

// ── Price row Alpine component ────────────────────────────────────────────
function priceRow(slug, initialPrice, initialChange24h) {
    return {
        slug,
        price:     initialPrice,
        change24h: initialChange24h,
        flashClass: '',
        _timer:     null,

        init() {
            this.$watch('$store.liveprices.prices', (prices) => {
                const live = prices[this.slug];
                if (!live) return;
                const prev = this.price;
                if (live.price !== prev) {
                    this._flash(live.price > prev ? 'flash-up' : 'flash-down');
                    this.price     = live.price;
                    this.change24h = live.change_24h;
                }
            }, { deep: true });
        },

        _flash(cls) {
            clearTimeout(this._timer);
            this.flashClass = cls;
            this._timer = setTimeout(() => { this.flashClass = ''; }, 1100);
        },

        get formattedPrice() {
            const p = this.price;
            if (!p) return '$0.00';
            if (p < 0.000001) return '$' + p.toFixed(10);
            if (p < 0.0001)   return '$' + p.toFixed(8);
            if (p < 0.01)     return '$' + p.toFixed(6);
            if (p < 1)        return '$' + p.toFixed(4);
            return '$' + p.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        get change24hColor() {
            return this.change24h >= 0 ? 'text-emerald-400' : 'text-red-400';
        },
        get change24hLabel() {
            const sign = this.change24h >= 0 ? '▲' : '▼';
            return `${sign} ${Math.abs(this.change24h).toFixed(2)}%`;
        },
    };
}
</script>
@endpush
