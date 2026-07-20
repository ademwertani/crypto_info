@extends('layouts.app')
@section('content')

<div class="max-w-3xl mx-auto prose-invert">

    <nav class="text-xs text-slate-500 mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center gap-1.5">
            <li><a href="{{ route('crypto.index') }}" class="hover:text-white transition">Home</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-slate-300">About Us</li>
        </ol>
    </nav>

    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-white mb-2">About CryptoInfo</h1>
        <p class="text-slate-400">Our mission, team and commitment to transparency.</p>
    </div>

    <div class="space-y-6">

        <div class="glass rounded-2xl p-6">
            <h2 class="text-lg font-bold text-white mb-3">Our Mission</h2>
            <div class="space-y-3 text-sm text-slate-400 leading-relaxed">
                <p>CryptoInfo was built with one goal: <strong class="text-slate-300">give every investor access to the same institutional-grade crypto market data</strong>, for free, without barriers.</p>
                <p>We believe that market data should be transparent, accurate and accessible. Whether you're a seasoned trader or just starting your crypto journey, you deserve the same real-time insights that professional desks use every day.</p>
                <p>We are powered by the <strong class="text-slate-300">CoinGecko API</strong>, one of the most trusted cryptocurrency data providers in the world, with over 900 million monthly API requests across the industry.</p>
            </div>
        </div>

        <div class="glass rounded-2xl p-6">
            <h2 class="text-lg font-bold text-white mb-4">What We Track</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                @foreach([
                    ['250+', 'Cryptocurrencies', 'Real-time prices, market cap, volume, supply'],
                    ['Live', 'Price Updates', 'WebSocket broadcast every time data refreshes'],
                    ['10 min', 'Refresh Cycle', 'Prices synced from CoinGecko every 10 minutes'],
                    ['5', 'Market Signals', 'Price, volume, dominance, Fear & Greed, trends'],
                ] as [$val, $title, $desc])
                <div class="flex gap-3">
                    <div class="shrink-0 text-2xl font-black text-blue-400 w-14 text-right tabular-nums">{{ $val }}</div>
                    <div>
                        <p class="font-semibold text-white text-sm">{{ $title }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $desc }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="glass rounded-2xl p-6">
            <h2 class="text-lg font-bold text-white mb-3">Data Sources</h2>
            <div class="space-y-3">
                @foreach([
                    ['CoinGecko API', 'https://www.coingecko.com', 'Market prices, market caps, volumes, circulating supply, all-time highs, price change percentages, coin metadata.', 'text-emerald-400'],
                    ['Alternative.me', 'https://alternative.me', 'Crypto Fear & Greed Index — daily sentiment scoring based on volatility, momentum, social media, surveys, dominance and trends.', 'text-orange-400'],
                    ['Laravel Reverb', null, 'WebSocket server that broadcasts live price updates to connected browsers the moment new data is fetched.', 'text-blue-400'],
                ] as [$name, $url, $desc, $color])
                <div class="flex gap-3 text-sm">
                    <div class="shrink-0 w-1 rounded-full {{ str_replace('text-', 'bg-', $color) }}"></div>
                    <div>
                        <p class="font-semibold text-slate-200">
                            @if($url)
                                <a href="{{ $url }}" target="_blank" rel="noopener" class="{{ $color }} hover:underline">{{ $name }} ↗</a>
                            @else
                                <span class="{{ $color }}">{{ $name }}</span>
                            @endif
                        </p>
                        <p class="text-slate-500 text-xs mt-0.5">{{ $desc }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="glass rounded-2xl p-6">
            <h2 class="text-lg font-bold text-white mb-3">Our Commitment</h2>
            <div class="space-y-2 text-sm text-slate-400 leading-relaxed">
                <p>✅ <strong class="text-slate-300">Free forever</strong> — No paywalls, no "premium plans" for basic data.</p>
                <p>✅ <strong class="text-slate-300">No manipulation</strong> — We display data as-is from our sources. No sponsored rankings or paid placements in data tables.</p>
                <p>✅ <strong class="text-slate-300">Transparent methodology</strong> — How we collect, cache and display data is fully documented.</p>
                <p>✅ <strong class="text-slate-300">Not financial advice</strong> — We provide market data only. Always do your own research before investing.</p>
            </div>
        </div>

    </div>
</div>

@endsection
