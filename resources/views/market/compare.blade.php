@extends('layouts.app')
@section('content')

<div class="max-w-6xl mx-auto">

    {{-- Breadcrumb --}}
    <nav class="text-xs text-slate-500 mb-5" aria-label="Breadcrumb">
        <ol class="flex items-center gap-1.5">
            <li><a href="{{ route('crypto.index') }}" class="hover:text-white transition">Home</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-slate-300">Compare</li>
        </ol>
    </nav>

    @php
        $a    = $coinA;
        $b    = $coinB;
        $chgA = (float)($a->price_change_percentage_24h_in_currency ?? 0);
        $chgB = (float)($b->price_change_percentage_24h_in_currency ?? 0);
        $chg7A = (float)($a->price_change_percentage_7d_in_currency ?? 0);
        $chg7B = (float)($b->price_change_percentage_7d_in_currency ?? 0);
    @endphp

    {{-- Header --}}
    <div class="text-center mb-8 animate-fade-in">
        <h1 class="text-3xl font-extrabold text-white mb-1">
            <a href="{{ route('crypto.show', $a->slug) }}" class="hover:text-blue-400 transition">{{ $a->name }}</a>
            <span class="text-slate-600 mx-3 font-light">vs</span>
            <a href="{{ route('crypto.show', $b->slug) }}" class="hover:text-blue-400 transition">{{ $b->name }}</a>
        </h1>
        <p class="text-slate-400 text-sm">{{ __('compare.subtitle') }}</p>
    </div>

    {{-- Live price cards --}}
    <div class="grid grid-cols-2 gap-4 mb-6 animate-fade-in-delay-1"
         x-data="compareCoins('{{ $a->slug }}', {{ (float)$a->current_price }}, {{ $chgA }}, '{{ $b->slug }}', {{ (float)$b->current_price }}, {{ $chgB }})"
         x-init="init()">

        {{-- Coin A --}}
        <div class="glass rounded-2xl p-5 text-center border border-blue-800/20">
            <div class="flex items-center justify-center gap-2.5 mb-3">
                @if($a->image_url)
                    <img src="{{ $a->image_url }}" alt="{{ $a->name }}" class="h-9 w-9 rounded-full" loading="lazy">
                @endif
                <div class="text-left">
                    <p class="font-bold text-white leading-tight">{{ $a->name }}</p>
                    <p class="text-xs text-slate-500 uppercase font-medium">{{ $a->symbol }}</p>
                </div>
                @if($a->market_cap_rank)
                    <span class="ml-auto text-xs font-semibold text-slate-500 bg-slate-800 rounded px-1.5 py-0.5">#{{ $a->market_cap_rank }}</span>
                @endif
            </div>
            <p class="text-3xl font-black text-white tabular-nums mb-1" :class="flashA" x-text="priceA"></p>
            <p class="text-sm font-semibold tabular-nums" :class="chgColorA" x-text="chgLabelA"></p>
            <p class="text-xs text-slate-500 mt-1">24h change</p>
        </div>

        {{-- Coin B --}}
        <div class="glass rounded-2xl p-5 text-center border border-purple-800/20">
            <div class="flex items-center justify-center gap-2.5 mb-3">
                @if($b->image_url)
                    <img src="{{ $b->image_url }}" alt="{{ $b->name }}" class="h-9 w-9 rounded-full" loading="lazy">
                @endif
                <div class="text-left">
                    <p class="font-bold text-white leading-tight">{{ $b->name }}</p>
                    <p class="text-xs text-slate-500 uppercase font-medium">{{ $b->symbol }}</p>
                </div>
                @if($b->market_cap_rank)
                    <span class="ml-auto text-xs font-semibold text-slate-500 bg-slate-800 rounded px-1.5 py-0.5">#{{ $b->market_cap_rank }}</span>
                @endif
            </div>
            <p class="text-3xl font-black text-white tabular-nums mb-1" :class="flashB" x-text="priceB"></p>
            <p class="text-sm font-semibold tabular-nums" :class="chgColorB" x-text="chgLabelB"></p>
            <p class="text-xs text-slate-500 mt-1">24h change</p>
        </div>
    </div>

    {{-- Chart section --}}
    <div class="glass rounded-2xl overflow-hidden mb-6 animate-fade-in-delay-2">

        {{-- Chart toolbar --}}
        <div class="flex items-center gap-3 px-5 py-3 border-b border-slate-800/60 flex-wrap">

            {{-- Mode toggle --}}
            <div class="flex rounded-lg overflow-hidden border border-slate-700 text-xs">
                <button id="mode-overlay" onclick="setMode('overlay')"
                        class="px-3 py-1.5 bg-blue-600 text-white font-medium transition">
                    {{ __('compare.overlay') }}
                </button>
                <button id="mode-dual" onclick="setMode('dual')"
                        class="px-3 py-1.5 text-slate-400 hover:bg-slate-700 hover:text-white transition">
                    {{ __('compare.dual_price') }}
                </button>
            </div>

            {{-- Legend --}}
            <div class="flex items-center gap-4 text-xs">
                <span class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full bg-blue-500 shrink-0"></span>
                    <span class="text-slate-300 font-medium">{{ $a->name }}</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full bg-purple-500 shrink-0"></span>
                    <span class="text-slate-300 font-medium">{{ $b->name }}</span>
                </span>
            </div>

            {{-- Timeframes --}}
            <div class="flex gap-0.5 ml-auto">
                @foreach(['1W','1M','3M','1Y'] as $tf)
                <button onclick="setTf('{{ $tf }}')" id="cmp-tf-{{ $tf }}"
                        class="cmp-tf-btn rounded-md px-2.5 py-1 text-xs font-medium text-slate-400 hover:bg-slate-700 hover:text-white transition {{ $tf === '1W' ? 'bg-slate-700 text-white' : '' }}">
                    {{ $tf }}
                </button>
                @endforeach
            </div>
        </div>

        {{-- Crosshair info --}}
        <div id="cmp-info" class="flex items-center gap-5 px-5 py-1.5 text-xs text-slate-500 border-b border-slate-800/40 min-h-[26px]">
            <span id="cmp-date"></span>
            <span id="cmp-val-a" class="text-blue-400 font-medium tabular-nums"></span>
            <span id="cmp-val-b" class="text-purple-400 font-medium tabular-nums"></span>
        </div>

        {{-- Chart canvas --}}
        <div id="compare-chart" class="w-full h-[380px]"></div>
    </div>

    {{-- Metrics comparison table --}}
    <div class="glass rounded-2xl overflow-hidden mb-6 animate-fade-in-delay-3">
        <div class="px-5 py-3.5 border-b border-slate-800/60">
            <h2 class="font-semibold text-white text-sm">{{ __('compare.side_by_side') }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-800/50">
                        <th class="px-5 py-2.5 text-left w-40">{{ __('compare.metric') }}</th>
                        <th class="px-5 py-2.5 text-right text-blue-400">{{ $a->name }}</th>
                        <th class="px-5 py-2.5 text-right text-purple-400">{{ $b->name }}</th>
                        <th class="px-5 py-2.5 text-right w-24">{{ __('compare.edge') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40">
                    @php
                        $rows = [
                            [
                                'label'  => 'Price',
                                'valA'   => '$' . ($a->current_price >= 1 ? number_format((float)$a->current_price, 2) : number_format((float)$a->current_price, 6)),
                                'valB'   => '$' . ($b->current_price >= 1 ? number_format((float)$b->current_price, 2) : number_format((float)$b->current_price, 6)),
                                'winner' => null,
                                'classA' => 'text-white', 'classB' => 'text-white',
                            ],
                            [
                                'label'  => 'Market Cap',
                                'valA'   => $a->market_cap ? '$'.number_format((float)$a->market_cap/1e9,2).'B' : '—',
                                'valB'   => $b->market_cap ? '$'.number_format((float)$b->market_cap/1e9,2).'B' : '—',
                                'winner' => ($a->market_cap??0) > ($b->market_cap??0) ? 'A' : 'B',
                                'classA' => 'text-slate-300', 'classB' => 'text-slate-300',
                            ],
                            [
                                'label'  => '24h Volume',
                                'valA'   => $a->total_volume ? '$'.number_format((float)$a->total_volume/1e9,2).'B' : '—',
                                'valB'   => $b->total_volume ? '$'.number_format((float)$b->total_volume/1e9,2).'B' : '—',
                                'winner' => ($a->total_volume??0) > ($b->total_volume??0) ? 'A' : 'B',
                                'classA' => 'text-slate-300', 'classB' => 'text-slate-300',
                            ],
                            [
                                'label'  => '24h Change',
                                'valA'   => ($chgA>=0?'+':'').number_format($chgA,2).'%',
                                'valB'   => ($chgB>=0?'+':'').number_format($chgB,2).'%',
                                'winner' => $chgA > $chgB ? 'A' : ($chgB > $chgA ? 'B' : null),
                                'classA' => $chgA>=0?'text-emerald-400':'text-red-400',
                                'classB' => $chgB>=0?'text-emerald-400':'text-red-400',
                            ],
                            [
                                'label'  => '7d Change',
                                'valA'   => ($chg7A>=0?'+':'').number_format($chg7A,2).'%',
                                'valB'   => ($chg7B>=0?'+':'').number_format($chg7B,2).'%',
                                'winner' => $chg7A > $chg7B ? 'A' : ($chg7B > $chg7A ? 'B' : null),
                                'classA' => $chg7A>=0?'text-emerald-400':'text-red-400',
                                'classB' => $chg7B>=0?'text-emerald-400':'text-red-400',
                            ],
                            [
                                'label'  => 'Rank',
                                'valA'   => $a->market_cap_rank ? '#'.$a->market_cap_rank : '—',
                                'valB'   => $b->market_cap_rank ? '#'.$b->market_cap_rank : '—',
                                'winner' => ($a->market_cap_rank??9999) < ($b->market_cap_rank??9999) ? 'A' : 'B',
                                'classA' => 'text-slate-300', 'classB' => 'text-slate-300',
                            ],
                            [
                                'label'  => 'All-Time High',
                                'valA'   => $a->ath ? '$'.number_format((float)$a->ath, (float)$a->ath>=1?2:6) : '—',
                                'valB'   => $b->ath ? '$'.number_format((float)$b->ath, (float)$b->ath>=1?2:6) : '—',
                                'winner' => null,
                                'classA' => 'text-slate-300', 'classB' => 'text-slate-300',
                            ],
                            [
                                'label'  => 'ATH vs Now',
                                'valA'   => $a->ath_change_percentage !== null ? number_format((float)$a->ath_change_percentage,1).'%' : '—',
                                'valB'   => $b->ath_change_percentage !== null ? number_format((float)$b->ath_change_percentage,1).'%' : '—',
                                'winner' => ($a->ath_change_percentage??-999) > ($b->ath_change_percentage??-999) ? 'A' : 'B',
                                'classA' => (($a->ath_change_percentage??0)>=0)?'text-emerald-400':'text-red-400',
                                'classB' => (($b->ath_change_percentage??0)>=0)?'text-emerald-400':'text-red-400',
                            ],
                            [
                                'label'  => 'Circulating Supply',
                                'valA'   => $a->circulating_supply ? number_format((float)$a->circulating_supply/1e6,2).'M' : '—',
                                'valB'   => $b->circulating_supply ? number_format((float)$b->circulating_supply/1e6,2).'M' : '—',
                                'winner' => null,
                                'classA' => 'text-slate-400', 'classB' => 'text-slate-400',
                            ],
                        ];
                    @endphp
                    @foreach($rows as $row)
                    <tr class="hover:bg-slate-800/20 transition-colors">
                        <td class="px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wide">{{ $row['label'] }}</td>
                        <td class="px-5 py-3 text-right font-semibold tabular-nums {{ $row['classA'] }}">
                            {{ $row['valA'] }}
                            @if(($row['winner']??null) === 'A')
                                <span class="ml-1.5 inline-flex items-center rounded bg-emerald-900/50 px-1.5 py-0.5 text-[9px] font-bold text-emerald-400 border border-emerald-800/40">{{ __('compare.better') }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right font-semibold tabular-nums {{ $row['classB'] }}">
                            {{ $row['valB'] }}
                            @if(($row['winner']??null) === 'B')
                                <span class="ml-1.5 inline-flex items-center rounded bg-emerald-900/50 px-1.5 py-0.5 text-[9px] font-bold text-emerald-400 border border-emerald-800/40">{{ __('compare.better') }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right text-xs">
                            @if(($row['winner']??null) === 'A')
                                <span class="text-blue-400 font-semibold">{{ strtoupper($a->symbol) }}</span>
                            @elseif(($row['winner']??null) === 'B')
                                <span class="text-purple-400 font-semibold">{{ strtoupper($b->symbol) }}</span>
                            @else
                                <span class="text-slate-700">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Try other comparisons --}}
    <div class="glass rounded-xl p-5">
        <h2 class="text-sm font-semibold text-white mb-3">{{ __('compare.try_other') }}</h2>
        <div class="flex flex-wrap gap-2">
            @foreach(['bitcoin','ethereum','solana','binancecoin','ripple','cardano','dogecoin','avalanche-2','polkadot','chainlink'] as $slug)
                @if($slug !== $a->slug)
                    <a href="{{ route('crypto.compare', ['slugA' => $a->slug, 'slugB' => $slug]) }}"
                       class="rounded-lg border border-slate-700/60 px-3 py-1.5 text-xs text-slate-400 hover:border-blue-600/50 hover:text-blue-400 transition capitalize">
                        {{ $a->symbol }} vs {{ $slug }}
                    </a>
                @endif
                @if($slug !== $b->slug)
                    <a href="{{ route('crypto.compare', ['slugA' => $b->slug, 'slugB' => $slug]) }}"
                       class="rounded-lg border border-slate-700/60 px-3 py-1.5 text-xs text-slate-400 hover:border-purple-600/50 hover:text-purple-400 transition capitalize">
                        {{ $b->symbol }} vs {{ $slug }}
                    </a>
                @endif
            @endforeach
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/lightweight-charts@4.2.0/dist/lightweight-charts.standalone.production.js"
        integrity="sha384-OK7vELvjHdhUFi31JYioPIcRHTROLdcDa6ZsNWgvgLaKj+9JqhU0Ad8g4wz3CXjA"
        crossorigin="anonymous"></script>
<script>
// ── Alpine: live prices for both coins ───────────────────────────────────
function compareCoins(slugA, initPriceA, initChgA, slugB, initPriceB, initChgB) {
    return {
        slugA, _priceA: initPriceA, _chgA: initChgA, flashA: '',
        slugB, _priceB: initPriceB, _chgB: initChgB, flashB: '',
        _timerA: null, _timerB: null,

        init() {
            this.$watch('$store.liveprices.prices', prices => {
                const la = prices[this.slugA];
                if (la && la.price !== this._priceA) {
                    this._doFlash('A', la.price > this._priceA);
                    this._priceA = la.price;
                    this._chgA   = la.change_24h;
                }
                const lb = prices[this.slugB];
                if (lb && lb.price !== this._priceB) {
                    this._doFlash('B', lb.price > this._priceB);
                    this._priceB = lb.price;
                    this._chgB   = lb.change_24h;
                }
            }, { deep: true });
        },

        _doFlash(coin, up) {
            const cls = up ? 'flash-up' : 'flash-down';
            if (coin === 'A') {
                clearTimeout(this._timerA);
                this.flashA = cls;
                this._timerA = setTimeout(() => { this.flashA = ''; }, 1100);
            } else {
                clearTimeout(this._timerB);
                this.flashB = cls;
                this._timerB = setTimeout(() => { this.flashB = ''; }, 1100);
            }
        },

        _fmt(p) {
            if (!p) return '$0.00';
            if (p < 0.01) return '$' + p.toFixed(6);
            if (p < 1)    return '$' + p.toFixed(4);
            return '$' + p.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        get priceA()   { return this._fmt(this._priceA); },
        get priceB()   { return this._fmt(this._priceB); },
        get chgLabelA(){ return (this._chgA >= 0 ? '▲ +' : '▼ ') + Math.abs(this._chgA).toFixed(2) + '%'; },
        get chgLabelB(){ return (this._chgB >= 0 ? '▲ +' : '▼ ') + Math.abs(this._chgB).toFixed(2) + '%'; },
        get chgColorA(){ return this._chgA >= 0 ? 'text-emerald-400' : 'text-red-400'; },
        get chgColorB(){ return this._chgB >= 0 ? 'text-emerald-400' : 'text-red-400'; },
    };
}

// ── Compare chart ─────────────────────────────────────────────────────────
const SLUG_A   = '{{ $a->slug }}';
const SLUG_B   = '{{ $b->slug }}';
const CGBASE   = 'https://api.coingecko.com/api/v3';
const TF_DAYS  = { '1W': 7, '1M': 30, '3M': 90, '1Y': 365 };

let cmpChart, seriesA, seriesB;
let currentMode = 'overlay';
let currentDays = 7;

function initCompareChart() {
    const el = document.getElementById('compare-chart');
    cmpChart = LightweightCharts.createChart(el, {
        layout:    { background: { color: '#0f172a' }, textColor: '#64748b' },
        grid:      { vertLines: { color: '#1e293b' }, horzLines: { color: '#1e293b' } },
        crosshair: { mode: LightweightCharts.CrosshairMode.Normal },
        rightPriceScale: { borderColor: '#1e293b' },
        leftPriceScale:  { visible: false, borderColor: '#1e293b' },
        timeScale: { borderColor: '#1e293b', timeVisible: true, secondsVisible: false },
        width:  el.clientWidth,
        height: 380,
    });

    seriesA = cmpChart.addAreaSeries({
        lineColor:   '#3b82f6',
        topColor:    'rgba(59,130,246,0.15)',
        bottomColor: 'rgba(59,130,246,0)',
        lineWidth: 2,
        title: '{{ $a->symbol }}',
    });

    seriesB = cmpChart.addAreaSeries({
        lineColor:   '#a855f7',
        topColor:    'rgba(168,85,247,0.1)',
        bottomColor: 'rgba(168,85,247,0)',
        lineWidth: 2,
        title: '{{ $b->symbol }}',
    });

    // Crosshair info
    cmpChart.subscribeCrosshairMove(param => {
        if (!param.time) {
            document.getElementById('cmp-date').textContent  = '';
            document.getElementById('cmp-val-a').textContent = '';
            document.getElementById('cmp-val-b').textContent = '';
            return;
        }
        const d = new Date(param.time * 1000);
        document.getElementById('cmp-date').textContent = d.toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' });

        const da = param.seriesData.get(seriesA);
        const db = param.seriesData.get(seriesB);

        if (da) {
            const v = da.value;
            document.getElementById('cmp-val-a').textContent =
                currentMode === 'overlay'
                    ? '{{ $a->symbol }}: ' + (v >= 0 ? '+' : '') + v.toFixed(2) + '%'
                    : '{{ $a->symbol }}: $' + v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 4 });
        }
        if (db) {
            const v = db.value;
            document.getElementById('cmp-val-b').textContent =
                currentMode === 'overlay'
                    ? '{{ $b->symbol }}: ' + (v >= 0 ? '+' : '') + v.toFixed(2) + '%'
                    : '{{ $b->symbol }}: $' + v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 4 });
        }
    });

    new ResizeObserver(() => cmpChart.applyOptions({ width: el.clientWidth })).observe(el);
}

async function loadChartData() {
    const days = currentDays;
    const interval = days <= 7 ? 'hourly' : 'daily';
    try {
        const [rA, rB] = await Promise.all([
            fetch(`${CGBASE}/coins/${SLUG_A}/market_chart?vs_currency=usd&days=${days}&interval=${interval}`),
            fetch(`${CGBASE}/coins/${SLUG_B}/market_chart?vs_currency=usd&days=${days}&interval=${interval}`),
        ]);
        const [dA, dB] = await Promise.all([rA.json(), rB.json()]);

        const pricesA = (dA.prices || []).map(p => [Math.floor(p[0]/1000), p[1]]);
        const pricesB = (dB.prices || []).map(p => [Math.floor(p[0]/1000), p[1]]);

        if (currentMode === 'overlay') {
            // Normalize both to % return from first data point
            const base0A = pricesA[0]?.[1] ?? 1;
            const base0B = pricesB[0]?.[1] ?? 1;
            seriesA.setData(pricesA.map(([t,v]) => ({ time: t, value: parseFloat(((v/base0A-1)*100).toFixed(4)) })));
            seriesB.setData(pricesB.map(([t,v]) => ({ time: t, value: parseFloat(((v/base0B-1)*100).toFixed(4)) })));

            // Show single right axis (% scale)
            cmpChart.applyOptions({ rightPriceScale: { visible: true }, leftPriceScale: { visible: false } });
            seriesA.applyOptions({ priceScaleId: 'right' });
            seriesB.applyOptions({ priceScaleId: 'right' });
        } else {
            // Dual scale: A on right, B on left
            cmpChart.applyOptions({ rightPriceScale: { visible: true }, leftPriceScale: { visible: true } });
            seriesA.applyOptions({ priceScaleId: 'right' });
            seriesB.applyOptions({ priceScaleId: 'left' });
            seriesA.setData(pricesA.map(([t,v]) => ({ time: t, value: v })));
            seriesB.setData(pricesB.map(([t,v]) => ({ time: t, value: v })));
        }

        cmpChart.timeScale().fitContent();
    } catch (err) {
        console.warn('[Compare] Chart fetch error:', err.message);
    }
}

function setMode(mode) {
    currentMode = mode;
    document.getElementById('mode-overlay').className = mode === 'overlay'
        ? 'px-3 py-1.5 bg-blue-600 text-white font-medium transition'
        : 'px-3 py-1.5 text-slate-400 hover:bg-slate-700 hover:text-white transition';
    document.getElementById('mode-dual').className = mode === 'dual'
        ? 'px-3 py-1.5 bg-blue-600 text-white font-medium transition'
        : 'px-3 py-1.5 text-slate-400 hover:bg-slate-700 hover:text-white transition';
    loadChartData();
}

function setTf(tf) {
    currentDays = TF_DAYS[tf];
    document.querySelectorAll('.cmp-tf-btn').forEach(b => {
        b.classList.remove('bg-slate-700', 'text-white');
        b.classList.add('text-slate-400');
    });
    const btn = document.getElementById('cmp-tf-' + tf);
    if (btn) { btn.classList.add('bg-slate-700', 'text-white'); btn.classList.remove('text-slate-400'); }
    loadChartData();
}

initCompareChart();
loadChartData();
</script>
@endpush
