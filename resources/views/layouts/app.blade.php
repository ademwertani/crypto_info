@php $isRtl = app()->getLocale() === 'ar'; @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      class="dark"
      @if($isRtl) dir="rtl" @endif>
<head>
    <script>if(localStorage.getItem('theme')==='light'){document.documentElement.classList.remove('dark');document.documentElement.classList.add('light');}else{document.documentElement.classList.add('dark');}</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f172a">

    @isset($seo)
        <title>{{ $seo->title }}</title>
        <meta name="description" content="{{ $seo->description }}">
        @if($seo->canonical)
            <link rel="canonical" href="{{ $seo->canonical }}">
        @endif
        <meta property="og:type"        content="{{ $seo->og_type ?? 'website' }}">
        <meta property="og:title"       content="{{ $seo->title }}">
        <meta property="og:description" content="{{ $seo->description }}">
        <meta property="og:url"         content="{{ $seo->canonical ?? request()->url() }}">
        <meta property="og:image"       content="{{ $seo->image ?? asset('images/og-default.png') }}">
        <meta property="og:site_name"   content="CryptoInfo">
        <meta name="twitter:card"        content="summary_large_image">
        <meta name="twitter:title"       content="{{ $seo->title }}">
        <meta name="twitter:description" content="{{ $seo->description }}">
        <meta name="twitter:image"       content="{{ $seo->image ?? asset('images/og-default.png') }}">
        @if(!empty($seo->jsonld))
            <script type="application/ld+json">{!! json_encode($seo->jsonld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
        @endif
    @else
        <title>@yield('title', 'Crypto Info') — Live Cryptocurrency Prices</title>
        <meta name="description" content="Real-time cryptocurrency prices, market cap, volume and analytics. Track Bitcoin, Ethereum and 250+ coins with live WebSocket updates.">
    @endisset

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if($isRtl)
        {{-- Cairo for Arabic — excellent readability for RTL --}}
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @else
        <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if($isRtl)
    <style>
        body, * { font-family: 'Cairo', 'Segoe UI', sans-serif !important; }
        .tabular-nums { font-variant-numeric: tabular-nums; }
        /* Flip padding for RTL nav */
        .rtl-flip { transform: scaleX(-1); }
    </style>
    @endif

    @stack('head')
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col antialiased"
      x-data="{ mobileMenuOpen: false }"
      x-init="$store.liveprices.init()">

{{-- ── Global ticker bar ─────────────────────────────────────────────────── --}}
@include('partials.global-ticker')

{{-- ── Navbar ─────────────────────────────────────────────────────────────── --}}
<header class="sticky top-0 z-50 border-b border-slate-800/80 bg-slate-950/95 backdrop-blur-md">
    <div class="mx-auto max-w-screen-xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between gap-3">

            {{-- Logo --}}
            <a href="{{ route('crypto.index') }}" class="flex items-center gap-2.5 shrink-0 group">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-blue-700 font-bold text-white text-sm shadow-lg shadow-blue-500/20 group-hover:shadow-blue-500/40 transition-shadow">
                    CI
                </div>
                <div class="flex flex-col leading-none">
                    <span class="text-base font-bold tracking-tight text-white">Crypto<span class="text-blue-400">Info</span></span>
                    <span class="text-[9px] text-slate-500 uppercase tracking-widest font-medium">Live Market Data</span>
                </div>
            </a>

            {{-- Search --}}
            <form method="GET" action="{{ route('crypto.index') }}"
                  class="flex-1 max-w-sm hidden sm:block"
                  role="search"
                  aria-label="{{ __('nav.search_placeholder') }}">
                <div class="relative">
                    <label for="global-search" class="sr-only">{{ __('common.search') }}</label>
                    <svg class="pointer-events-none absolute {{ $isRtl ? 'right-3' : 'left-3' }} top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                    </svg>
                    <input id="global-search" type="search" name="search" value="{{ request('search') }}"
                           placeholder="{{ __('nav.search_placeholder') }}" autocomplete="off"
                           class="w-full rounded-lg border border-slate-700/60 bg-slate-800/60 py-2 {{ $isRtl ? 'pr-10 pl-4' : 'pl-10 pr-4' }} text-sm text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500/80 focus:bg-slate-800 focus:ring-1 focus:ring-blue-500/40 transition-all">
                </div>
            </form>

            {{-- Desktop nav --}}
            <nav class="hidden lg:flex items-center gap-1 text-sm text-slate-400" aria-label="Main navigation">
                <a href="{{ route('crypto.index') }}"
                   class="px-3 py-1.5 rounded-lg hover:bg-slate-800 hover:text-white transition {{ request()->routeIs('crypto.index') ? 'bg-slate-800 text-white' : '' }}">
                    {{ __('nav.market') }}
                </a>
                <a href="{{ route('market.gainers') }}"
                   class="px-3 py-1.5 rounded-lg hover:bg-slate-800 hover:text-white transition {{ request()->routeIs('market.gainers') ? 'bg-slate-800 text-white' : '' }}">
                    {{ __('nav.gainers') }}
                </a>
                <a href="{{ route('market.losers') }}"
                   class="px-3 py-1.5 rounded-lg hover:bg-slate-800 hover:text-white transition {{ request()->routeIs('market.losers') ? 'bg-slate-800 text-white' : '' }}">
                    {{ __('nav.losers') }}
                </a>
                <a href="{{ route('market.trending') }}"
                   class="px-3 py-1.5 rounded-lg hover:bg-slate-800 hover:text-white transition {{ request()->routeIs('market.trending') ? 'bg-slate-800 text-white' : '' }}">
                    {{ __('nav.trending') }}
                </a>
                <a href="{{ route('market.fear-greed') }}"
                   class="px-3 py-1.5 rounded-lg hover:bg-slate-800 hover:text-white transition {{ request()->routeIs('market.fear-greed') ? 'bg-slate-800 text-white' : '' }}">
                    {{ __('nav.fear_greed') }}
                </a>
                <a href="{{ route('crypto.compare.chooser') }}"
                   class="px-3 py-1.5 rounded-lg hover:bg-slate-800 hover:text-white transition {{ request()->routeIs('crypto.compare*') ? 'bg-slate-800 text-white' : '' }}">
                    ⚖️ {{ __('nav.compare') }}
                </a>
            </nav>

            {{-- Right side: Live badge + lang switcher + mobile menu --}}
            <div class="flex items-center gap-2 shrink-0">

                {{-- LIVE indicator --}}
                <div x-show="$store.liveprices.connected" x-cloak
                     class="hidden sm:flex items-center gap-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-1 text-xs font-semibold text-emerald-400">
                    <span class="relative flex h-1.5 w-1.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                    </span>
                    {{ __('common.live') }}
                </div>

                {{-- Theme toggle --}}
                <button @click="$store.theme.toggle()"
                        class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition"
                        :aria-label="$store.theme.dark ? 'Switch to light mode' : 'Switch to dark mode'"
                        title="Toggle dark/light mode">
                    <svg x-show="$store.theme.dark" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <svg x-show="!$store.theme.dark" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>

                {{-- Language switcher --}}
                @include('partials.language-switcher')

                {{-- Mobile menu toggle --}}
                <button @click="mobileMenuOpen = !mobileMenuOpen"
                        class="lg:hidden p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition"
                        :aria-expanded="mobileMenuOpen"
                        aria-label="{{ __('nav.toggle_menu') }}">
                    <svg x-show="!mobileMenuOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="mobileMenuOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div x-show="mobileMenuOpen"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="lg:hidden border-t border-slate-800 bg-slate-950 px-4 py-3">

        {{-- Mobile search --}}
        <form method="GET" action="{{ route('crypto.index') }}" class="mb-3"
              role="search" aria-label="{{ __('nav.search_placeholder') }}">
            <label for="mobile-search" class="sr-only">{{ __('common.search') }}</label>
            <input id="mobile-search" type="search" name="search" value="{{ request('search') }}"
                   placeholder="{{ __('nav.search_placeholder') }}"
                   class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-2 text-sm text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500">
        </form>

        <nav class="grid grid-cols-2 gap-1 text-sm mb-3" aria-label="Mobile navigation">
            <a href="{{ route('crypto.index') }}"      class="px-3 py-2 rounded-lg hover:bg-slate-800 text-slate-300">{{ __('nav.market') }}</a>
            <a href="{{ route('market.gainers') }}"    class="px-3 py-2 rounded-lg hover:bg-slate-800 text-slate-300">{{ __('nav.gainers') }}</a>
            <a href="{{ route('market.losers') }}"     class="px-3 py-2 rounded-lg hover:bg-slate-800 text-slate-300">{{ __('nav.losers') }}</a>
            <a href="{{ route('market.trending') }}"   class="px-3 py-2 rounded-lg hover:bg-slate-800 text-slate-300">{{ __('nav.trending') }}</a>
            <a href="{{ route('market.fear-greed') }}" class="px-3 py-2 rounded-lg hover:bg-slate-800 text-slate-300">{{ __('nav.fear_greed') }}</a>
            <a href="{{ route('market.bitcoin-dominance') }}" class="px-3 py-2 rounded-lg hover:bg-slate-800 text-slate-300">BTC Dominance</a>
            <a href="{{ route('crypto.compare.chooser') }}" class="px-3 py-2 rounded-lg hover:bg-slate-800 text-slate-300">⚖️ {{ __('nav.compare') }}</a>
        </nav>

        {{-- Mobile theme toggle --}}
        <div class="border-t border-slate-800 pt-3 mb-3">
            <button @click="$store.theme.toggle()"
                    class="flex items-center gap-2 w-full px-3 py-2 rounded-lg hover:bg-slate-800 text-slate-300 text-sm transition">
                <svg x-show="$store.theme.dark" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <svg x-show="!$store.theme.dark" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                <span x-text="$store.theme.dark ? 'Switch to Light Mode' : 'Switch to Dark Mode'"></span>
            </button>
        </div>

        {{-- Mobile language switcher --}}
        <div class="border-t border-slate-800 pt-3">
            <p class="text-[10px] uppercase tracking-widest text-slate-600 mb-2 px-1">{{ __('lang.label') }}</p>
            <div class="flex flex-wrap gap-1.5">
                @foreach(['en'=>['gb','EN','English'],'fr'=>['fr','FR','Français'],'ar'=>['sa','AR','العربية'],'es'=>['es','ES','Español'],'de'=>['de','DE','Deutsch'],'pt'=>['br','PT','Português']] as $code=>[$flag,$short,$label])
                <a href="{{ route('locale.switch', $code) }}"
                   class="flex items-center gap-1 rounded-lg border px-2.5 py-1 text-xs font-medium transition
                          {{ app()->getLocale() === $code
                             ? 'border-blue-600 bg-blue-600/20 text-blue-400'
                             : 'border-slate-700 text-slate-400 hover:border-slate-500 hover:text-white' }}">
                    <img
                        src="https://flagcdn.com/w20/{{ $flag }}.png"
                        srcset="https://flagcdn.com/w40/{{ $flag }}.png 2x"
                        alt="{{ $label }}"
                        class="h-3.5 w-5 rounded-sm object-cover"
                        width="20"
                        height="14">
                    <span>{{ $short }}</span>
                </a>
                @endforeach
            </div>
            <div class="hidden">
                @foreach(['en'=>['🇬🇧','EN'],'fr'=>['🇫🇷','FR'],'ar'=>['🇸🇦','AR'],'es'=>['🇪🇸','ES'],'de'=>['🇩🇪','DE'],'pt'=>['🇧🇷','PT']] as $code=>[$flag,$short])
                <a href="{{ route('locale.switch', $code) }}"
                   class="flex items-center gap-1 rounded-lg border px-2.5 py-1 text-xs font-medium transition
                          {{ app()->getLocale() === $code
                             ? 'border-blue-600 bg-blue-600/20 text-blue-400'
                             : 'border-slate-700 text-slate-400 hover:border-slate-500 hover:text-white' }}">
                    <span>{{ $flag }}</span><span>{{ $short }}</span>
                </a>
                @endforeach
            </div>
        </div>
    </div>

    @include('partials.ad-leaderboard')
</header>

{{-- ── Main ────────────────────────────────────────────────────────────────── --}}
<main class="flex-1 mx-auto w-full max-w-screen-xl px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
    @yield('content')
</main>

{{-- ── Footer ─────────────────────────────────────────────────────────────── --}}
<footer class="border-t border-slate-800/60 bg-slate-950 pt-10 pb-6 text-slate-500">
    <div class="mx-auto max-w-screen-xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-8 mb-8">

            {{-- Brand --}}
            <div class="col-span-2 md:col-span-1">
                <a href="{{ route('crypto.index') }}" class="flex items-center gap-2 mb-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-blue-700 font-bold text-white text-sm">CI</div>
                    <span class="font-bold text-white">CryptoInfo</span>
                </a>
                <p class="text-xs leading-relaxed mb-3">{{ __('footer.tagline') }}</p>
                <div class="flex items-center gap-1.5 text-xs text-emerald-400">
                    <span class="relative flex h-1.5 w-1.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                    </span>
                    {{ __('footer.live_data') }}
                </div>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">{{ __('footer.market') }}</p>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('market.gainers') }}"           class="hover:text-white transition">{{ __('footer.top_gainers') }}</a></li>
                    <li><a href="{{ route('market.losers') }}"            class="hover:text-white transition">{{ __('footer.top_losers') }}</a></li>
                    <li><a href="{{ route('market.trending') }}"          class="hover:text-white transition">{{ __('footer.trending') }}</a></li>
                    <li><a href="{{ route('market.fear-greed') }}"        class="hover:text-white transition">{{ __('footer.fear_greed') }}</a></li>
                    <li><a href="{{ route('market.bitcoin-dominance') }}" class="hover:text-white transition">{{ __('footer.btc_dominance') }}</a></li>
                    <li><a href="{{ route('market.global-cap') }}"        class="hover:text-white transition">{{ __('footer.market_cap') }}</a></li>
                </ul>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">{{ __('footer.content') }}</p>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('api.docs') }}"   class="hover:text-white transition">{{ __('footer.api_docs') }}</a></li>
                </ul>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">{{ __('footer.company') }}</p>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('pages.about') }}"       class="hover:text-white transition">{{ __('footer.about') }}</a></li>
                    <li><a href="{{ route('pages.methodology') }}" class="hover:text-white transition">{{ __('footer.methodology') }}</a></li>
                    <li><a href="{{ route('pages.contact') }}"     class="hover:text-white transition">{{ __('footer.contact') }}</a></li>
                    <li><a href="{{ route('pages.privacy') }}"     class="hover:text-white transition">{{ __('footer.privacy') }}</a></li>
                    <li><a href="{{ route('pages.terms') }}"       class="hover:text-white transition">{{ __('footer.terms') }}</a></li>
                </ul>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">{{ __('footer.trade') }}</p>
                <ul class="space-y-2 text-sm">
                    <li><a href="https://www.binance.com/en/register?ref=CRYPTOINFO" target="_blank" rel="noopener sponsored" class="hover:text-yellow-400 transition">Binance ↗</a></li>
                    <li><a href="https://www.bybit.com/en/register?affiliate_id=CRYPTOINFO" target="_blank" rel="noopener sponsored" class="hover:text-orange-400 transition">Bybit ↗</a></li>
                    <li><a href="https://www.okx.com/join/CRYPTOINFO" target="_blank" rel="noopener sponsored" class="hover:text-blue-400 transition">OKX ↗</a></li>
                </ul>
            </div>
        </div>

        {{-- Trust badges --}}
        <div class="border-t border-slate-800/60 pt-6 mb-4">
            <div class="flex flex-wrap justify-center gap-4 text-xs text-slate-600">
                <span class="flex items-center gap-1.5"><span class="text-emerald-500">✓</span> {{ __('trust.realtime') }}</span>
                <span class="flex items-center gap-1.5"><span class="text-emerald-500">✓</span> {{ __('trust.coingecko') }}</span>
                <span class="flex items-center gap-1.5"><span class="text-emerald-500">✓</span> {{ __('trust.coins') }}</span>
                <span class="flex items-center gap-1.5"><span class="text-emerald-500">✓</span> {{ __('trust.free') }}</span>
                <span class="flex items-center gap-1.5"><span class="text-emerald-500">✓</span> {{ __('trust.transparent') }}</span>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-between items-center gap-2 text-xs text-slate-600">
            <p>
                {{ __('footer.data_by') }}
                <a href="https://www.coingecko.com" target="_blank" rel="noopener" class="text-blue-500 hover:text-blue-400">CoinGecko</a>
                &amp;
                <a href="https://alternative.me" target="_blank" rel="noopener" class="text-blue-500 hover:text-blue-400">Alternative.me</a>
                &nbsp;·&nbsp;
                {{ str_replace(':year', date('Y'), __('footer.copyright')) }}
                {{ __('footer.not_financial') }}.
            </p>
            <p>
                <a href="/sitemap.xml" class="hover:text-slate-400">{{ __('footer.sitemap') }}</a>
                &nbsp;·&nbsp;
                <a href="/robots.txt" class="hover:text-slate-400">{{ __('footer.robots') }}</a>
            </p>
        </div>
    </div>
</footer>

@stack('scripts')
</body>
</html>
