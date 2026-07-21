@extends('layouts.app')
@section('content')

<div class="max-w-3xl mx-auto">

    <nav class="text-xs text-slate-500 mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center gap-1.5">
            <li><a href="{{ route('crypto.index') }}" class="hover:text-white transition">Home</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-slate-300">Terms of Service</li>
        </ol>
    </nav>

    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-white mb-2">Terms of Service</h1>
        <p class="text-slate-400">Last updated: {{ date('F d, Y') }}</p>
    </div>

    <div class="space-y-5 text-sm text-slate-400 leading-relaxed">

        <div class="glass rounded-2xl p-6">
            <h2 class="font-bold text-white mb-3">1. Acceptance of Terms</h2>
            <p>By accessing or using CryptoInfo ("the Service"), you agree to be bound by these Terms of Service. If you do not agree, do not use the Service.</p>
        </div>

        <div class="glass rounded-2xl p-6 border border-yellow-500/20 bg-yellow-950/10">
            <h2 class="font-bold text-yellow-400 mb-3">⚠️ 2. Not Financial Advice</h2>
            <p>CryptoInfo provides market data and information for <strong class="text-slate-300">informational and educational purposes only</strong>. Nothing on this site constitutes financial, investment, trading or legal advice. Cryptocurrency trading carries significant risk. Always consult a qualified financial advisor before making investment decisions. CryptoInfo is not liable for any losses arising from use of information on this site.</p>
        </div>

        <div class="glass rounded-2xl p-6">
            <h2 class="font-bold text-white mb-3">3. Data Accuracy</h2>
            <p>We strive to display accurate market data sourced from CoinGecko and Alternative.me. However, we do not guarantee the accuracy, completeness or timeliness of data. Prices may be delayed. Do not use CryptoInfo as the sole basis for trading decisions.</p>
        </div>

        <div class="glass rounded-2xl p-6">
            <h2 class="font-bold text-white mb-3">4. Acceptable Use</h2>
            <p class="mb-2">You agree not to:</p>
            <ul class="space-y-1.5">
                <li>→ Scrape, crawl or harvest data from the site in bulk without prior written consent</li>
                <li>→ Use the service for any illegal purpose</li>
                <li>→ Attempt to disrupt, compromise or gain unauthorized access to our systems</li>
                <li>→ Redistribute our data commercially without a license agreement</li>
            </ul>
        </div>

        <div class="glass rounded-2xl p-6">
            <h2 class="font-bold text-white mb-3">5. Intellectual Property</h2>
            <p>The CryptoInfo brand, design and original content are owned by CryptoInfo. Underlying cryptocurrency market data is sourced from CoinGecko (see their <a href="https://www.coingecko.com/en/terms" target="_blank" rel="noopener" class="text-blue-400 hover:underline">Terms</a>).</p>
        </div>

        <div class="glass rounded-2xl p-6">
            <h2 class="font-bold text-white mb-3">6. Limitation of Liability</h2>
            <p>To the maximum extent permitted by law, CryptoInfo and its operators shall not be liable for any direct, indirect, incidental, special or consequential damages arising from use of the Service or inability to use the Service.</p>
        </div>

        <div class="glass rounded-2xl p-6 border border-yellow-500/20 bg-yellow-950/10">
            <h2 class="font-bold text-yellow-400 mb-3">8. Affiliate Disclosure</h2>
            <p>CryptoInfo participates in affiliate/referral programs with cryptocurrency exchanges, including Binance, Bybit and OKX. Some links on this site (for example in the footer "Trade" section) are affiliate links: if you sign up or trade through them, CryptoInfo may earn a commission, <strong class="text-slate-300">at no additional cost to you</strong>. Affiliate links are marked with <code class="rounded bg-slate-800 px-1 text-xs">rel="sponsored"</code> and are never a factor in the market data, prices or rankings displayed elsewhere on the site. See our <a href="{{ route('pages.privacy') }}" class="text-blue-400 hover:underline">Privacy Policy</a> for how affiliate-link clicks are measured.</p>
        </div>

        <div class="glass rounded-2xl p-6">
            <h2 class="font-bold text-white mb-3">9. Changes to Terms</h2>
            <p>We may update these Terms at any time. Continued use of the Service after changes constitutes acceptance of the new Terms. We will update the "Last updated" date above when changes are made.</p>
        </div>

    </div>
</div>

@endsection
