@extends('layouts.app')
@section('content')

<div class="max-w-3xl mx-auto">
    <nav class="text-xs text-slate-500 mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center gap-1.5">
            <li><a href="{{ route('crypto.index') }}" class="hover:text-white transition">Home</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-slate-300">Privacy Policy</li>
        </ol>
    </nav>

    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-white mb-2">Privacy Policy</h1>
        <p class="text-slate-400">Last updated: {{ date('F d, Y') }}</p>
    </div>

    <div class="space-y-5 text-sm text-slate-400 leading-relaxed">
        <div class="glass rounded-2xl p-6">
            <h2 class="font-bold text-white mb-3">1. Information We Collect</h2>
            <p><strong class="text-slate-300">Automatically collected:</strong> standard server logs such as IP address, browser type, pages visited and timestamps. We use this for security monitoring and performance analysis.</p>
        </div>

        <div class="glass rounded-2xl p-6">
            <h2 class="font-bold text-white mb-3">2. How We Use Information</h2>
            <ul class="space-y-1.5">
                <li>- To provide and improve CryptoInfo.</li>
                <li>- To detect and prevent abuse.</li>
                <li>- We do <strong class="text-slate-300">not</strong> sell personal data.</li>
                <li>- We do <strong class="text-slate-300">not</strong> use advertising profiling.</li>
            </ul>
        </div>

        <div class="glass rounded-2xl p-6">
            <h2 class="font-bold text-white mb-3">3. Cookies</h2>
            <p class="mb-2">We use only technical cookies needed by Laravel:</p>
            <ul class="space-y-1.5 text-xs">
                <li><code class="rounded bg-slate-800 px-1">cryptoinfo_session</code> - session management</li>
                <li><code class="rounded bg-slate-800 px-1">XSRF-TOKEN</code> - CSRF protection</li>
            </ul>
            <p class="mt-2">We do not use tracking cookies, Google Analytics or third-party advertising cookies.</p>
        </div>

        <div class="glass rounded-2xl p-6">
            <h2 class="font-bold text-white mb-3">4. Third-Party Services</h2>
            <p class="mb-2">We use third-party services for market data and assets:</p>
            <ul class="space-y-1.5">
                <li>- <strong class="text-slate-300">Google Fonts</strong> for font delivery.</li>
                <li>- <strong class="text-slate-300">CoinGecko</strong> for cryptocurrency market data.</li>
                <li>- <strong class="text-slate-300">Alternative.me</strong> for market sentiment data.</li>
            </ul>
        </div>

        <div class="glass rounded-2xl p-6">
            <h2 class="font-bold text-white mb-3">5. Data Retention</h2>
            <p>Server logs are purged after 30 days where operationally possible.</p>
        </div>

    </div>
</div>

@endsection
