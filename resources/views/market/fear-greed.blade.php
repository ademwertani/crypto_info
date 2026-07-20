@extends('layouts.app')
@section('content')

<div class="max-w-3xl mx-auto">

    {{-- Breadcrumb --}}
    <nav class="text-xs text-slate-500 mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center gap-1.5">
            <li><a href="{{ route('crypto.index') }}" class="hover:text-white transition">Home</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-slate-300">Fear &amp; Greed Index</li>
        </ol>
    </nav>

    <div class="mb-8 text-center">
        <h1 class="text-3xl font-extrabold text-white mb-2">Crypto Fear &amp; Greed Index</h1>
        <p class="text-slate-400">Live market sentiment score — updated daily by <a href="https://alternative.me" target="_blank" rel="noopener" class="text-blue-400 hover:underline">Alternative.me</a></p>
    </div>

    @php
        $fng   = (int)($stats['fng_value'] ?? 50);
        $label = $stats['fng_classification'] ?? 'Neutral';
        $color = \App\Services\GlobalMarketService::fngColor($fng);
        $deg   = -90 + ($fng / 100) * 180; // gauge rotation -90° to +90°
    @endphp

    {{-- Gauge card --}}
    <div class="glass rounded-2xl p-8 mb-6 text-center">
        {{-- Semi-circle gauge --}}
        <div class="relative inline-block w-56 h-28 overflow-hidden mb-4" aria-hidden="true">
            <svg viewBox="0 0 200 100" class="w-full h-full">
                {{-- Background arc --}}
                <path d="M 10,100 A 90,90 0 0,1 190,100" fill="none" stroke="#1e293b" stroke-width="18" stroke-linecap="round"/>
                {{-- Red zone (extreme fear) --}}
                <path d="M 10,100 A 90,90 0 0,1 55,19" fill="none" stroke="#ef4444" stroke-width="18" stroke-linecap="round" opacity="0.7"/>
                {{-- Orange zone (fear) --}}
                <path d="M 55,19 A 90,90 0 0,1 100,10" fill="none" stroke="#f97316" stroke-width="18" stroke-linecap="round" opacity="0.7"/>
                {{-- Yellow zone (neutral) --}}
                <path d="M 100,10 A 90,90 0 0,1 145,19" fill="none" stroke="#eab308" stroke-width="18" stroke-linecap="round" opacity="0.7"/>
                {{-- Green zone (greed) --}}
                <path d="M 145,19 A 90,90 0 0,1 190,100" fill="none" stroke="#10b981" stroke-width="18" stroke-linecap="round" opacity="0.7"/>
                {{-- Needle --}}
                <line x1="100" y1="100" x2="100" y2="20"
                      stroke="white" stroke-width="2.5" stroke-linecap="round"
                      class="fng-needle"
                      style="transform-origin: 100px 100px; transform: rotate({{ $deg }}deg)"/>
                <circle cx="100" cy="100" r="5" fill="white"/>
            </svg>
        </div>

        <p class="text-6xl font-black {{ $color }} tabular-nums mb-2">{{ $fng }}</p>
        <p class="text-xl font-bold text-white mb-1">{{ $label }}</p>
        @php
            $updatedAt = $stats['fng_updated_at'] ?? null;
        @endphp
        <p class="text-xs text-slate-500">Score out of 100 · Updated: {{ $updatedAt ? \Carbon\Carbon::parse($updatedAt)->format('M d, Y H:i') : 'Live feed' }}</p>
    </div>

    {{-- Scale legend --}}
    <div class="glass rounded-xl p-4 mb-6">
        <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">What does it mean?</h2>
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 text-xs">
            @foreach([
                ['0–24', 'Extreme Fear', 'bg-red-900/40 text-red-400 border-red-800/40'],
                ['25–44', 'Fear', 'bg-orange-900/40 text-orange-400 border-orange-800/40'],
                ['45–55', 'Neutral', 'bg-yellow-900/40 text-yellow-400 border-yellow-800/40'],
                ['56–74', 'Greed', 'bg-emerald-900/40 text-emerald-400 border-emerald-800/40'],
                ['75–100', 'Extreme Greed', 'bg-emerald-900/60 text-emerald-300 border-emerald-700/40'],
            ] as [$range, $name, $cls])
                <div class="flex flex-col items-center text-center rounded-lg border px-3 py-2 {{ $cls }}">
                    <span class="font-bold text-sm">{{ $range }}</span>
                    <span class="mt-0.5">{{ $name }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Explanation --}}
    <div class="glass rounded-xl p-6 mb-6">
        <h2 class="font-semibold text-white mb-3">How is it calculated?</h2>
        <div class="space-y-3 text-sm text-slate-400 leading-relaxed">
            <p>The Crypto Fear &amp; Greed Index aggregates 6 market signals into a single 0–100 score:</p>
            <ul class="space-y-1.5 list-none">
                @foreach([
                    ['Volatility', '25%', 'Compares current BTC volatility to 30 &amp; 90-day averages'],
                    ['Market Momentum/Volume', '25%', 'Compares current volume &amp; momentum to averages'],
                    ['Social Media Sentiment', '15%', 'Positive vs. negative crypto posts on social networks'],
                    ['Surveys', '15%', 'Weekly crypto sentiment polls'],
                    ['Bitcoin Dominance', '10%', 'Rising BTC dominance = fear, falling = greed'],
                    ['Google Trends', '10%', 'Bitcoin search trends and related queries'],
                ] as [$factor, $weight, $desc])
                <li class="flex items-start gap-3">
                    <span class="mt-0.5 shrink-0 rounded bg-blue-500/10 border border-blue-500/20 px-1.5 py-0.5 text-[10px] font-bold text-blue-400">{{ $weight }}</span>
                    <span><strong class="text-slate-300">{{ $factor }}</strong> — {!! $desc !!}</span>
                </li>
                @endforeach
            </ul>
            <p class="text-xs text-slate-600 mt-2">
                Data source: <a href="https://alternative.me/crypto/fear-and-greed-index/" target="_blank" rel="noopener" class="text-blue-500 hover:underline">Alternative.me</a>.
            </p>
        </div>
    </div>

    {{-- Trading implications --}}
    <div class="glass rounded-xl p-6">
        <h2 class="font-semibold text-white mb-3">How to use it?</h2>
        <div class="grid sm:grid-cols-2 gap-4 text-sm">
            <div class="rounded-lg bg-red-950/30 border border-red-800/30 p-4">
                <p class="font-semibold text-red-400 mb-2">😱 Extreme Fear (0–24)</p>
                <p class="text-slate-400 text-xs leading-relaxed">Historically a <strong class="text-slate-300">buying opportunity</strong>. Markets are often oversold. Investors are panicking — contrarians may find value.</p>
            </div>
            <div class="rounded-lg bg-emerald-950/30 border border-emerald-800/30 p-4">
                <p class="font-semibold text-emerald-400 mb-2">🤑 Extreme Greed (75–100)</p>
                <p class="text-slate-400 text-xs leading-relaxed">Markets may be <strong class="text-slate-300">due for a correction</strong>. FOMO is driving prices. A prudent moment to reassess risk exposure.</p>
            </div>
        </div>
        <p class="text-[11px] text-slate-600 mt-4">⚠️ This is not financial advice. Always do your own research.</p>
    </div>

</div>

@endsection
