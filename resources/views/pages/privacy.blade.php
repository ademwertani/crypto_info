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
            <p class="mb-2"><strong class="text-slate-300">Essential cookies</strong> (always active, no consent required) — needed by Laravel to run the site:</p>
            <ul class="space-y-1.5 text-xs">
                <li><code class="rounded bg-slate-800 px-1">cryptoinfo_session</code> - session management</li>
                <li><code class="rounded bg-slate-800 px-1">XSRF-TOKEN</code> - CSRF protection</li>
            </ul>
            <p class="mt-3 mb-2"><strong class="text-slate-300">Optional analytics cookies</strong> — Google Analytics 4 and Microsoft Clarity, used to understand how visitors use the site (pages viewed, clicks, general navigation patterns). These are <strong class="text-slate-300">only</strong> set after you explicitly accept them in our cookie banner; nothing from Google or Microsoft is loaded before that. You can accept, reject or change your choice at any time via the "Cookies" link in the footer — your decision is stored in your browser (localStorage) and can be withdrawn just as easily as it was given.</p>
            <p class="mt-2">We do not use advertising or third-party marketing cookies, and analytics data is never used to build advertising profiles.</p>
        </div>

        <div class="glass rounded-2xl p-6">
            <h2 class="font-bold text-white mb-3">4. Third-Party Services</h2>
            <p class="mb-2">We use third-party services for market data, assets, and — only with your consent — analytics:</p>
            <ul class="space-y-1.5">
                <li>- <strong class="text-slate-300">Google Fonts</strong> for font delivery.</li>
                <li>- <strong class="text-slate-300">CoinGecko</strong> for cryptocurrency market data.</li>
                <li>- <strong class="text-slate-300">Alternative.me</strong> for market sentiment data.</li>
                <li>- <strong class="text-slate-300">Google Analytics 4</strong> (optional, consent-gated) for audience analytics.</li>
                <li>- <strong class="text-slate-300">Microsoft Clarity</strong> (optional, consent-gated) for usage/heatmap analytics.</li>
            </ul>
            <p class="mt-2">CryptoInfo also participates in affiliate programs (Binance, Bybit, OKX — see our <a href="{{ route('pages.terms') }}" class="text-blue-400 hover:underline">Terms of Service</a> for the affiliate disclosure). Clicking an affiliate link is recorded as an anonymous event (which link, which page) if you've accepted analytics cookies; it does not identify you personally.</p>
        </div>

        <div class="glass rounded-2xl p-6">
            <h2 class="font-bold text-white mb-3">5. Data Retention</h2>
            <p>Server logs are purged after 30 days where operationally possible.</p>
        </div>

        {{-- Legal entity name and postal address to be added here once
             provided — required for a complete GDPR Art. 13 "data controller"
             notice. Do not fabricate placeholder values in the meantime. --}}
        <div class="glass rounded-2xl p-6">
            <h2 class="font-bold text-white mb-3">6. Contact Us</h2>
            <p>For any question about this Privacy Policy or to exercise your data rights (access, correction, deletion), contact us at
                <a href="mailto:{{ config('services.advertise.contact_email') }}" class="text-blue-400 hover:underline">{{ config('services.advertise.contact_email') }}</a>.
            </p>
        </div>

    </div>
</div>

@endsection
