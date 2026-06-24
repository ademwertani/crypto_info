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
            <p class="mb-2"><strong class="text-slate-300">Information you provide:</strong> If you create an account, we collect your email address and a hashed password. If you subscribe to our newsletter, we collect your email address and optional name.</p>
            <p><strong class="text-slate-300">Automatically collected:</strong> Standard server logs (IP address, browser type, pages visited, timestamps). We use this only for security monitoring and performance analysis.</p>
        </div>

        <div class="glass rounded-2xl p-6">
            <h2 class="font-bold text-white mb-3">2. How We Use Your Information</h2>
            <ul class="space-y-1.5">
                <li>→ To provide and improve the CryptoInfo service</li>
                <li>→ To send you newsletters you subscribed to (you can unsubscribe anytime)</li>
                <li>→ To send price alerts you configured</li>
                <li>→ To detect and prevent abuse</li>
                <li>→ We do <strong class="text-slate-300">not</strong> sell your data to third parties</li>
                <li>→ We do <strong class="text-slate-300">not</strong> use your data for advertising profiling</li>
            </ul>
        </div>

        <div class="glass rounded-2xl p-6">
            <h2 class="font-bold text-white mb-3">3. Cookies</h2>
            <p class="mb-2">We use a minimal set of cookies:</p>
            <ul class="space-y-1.5 text-xs">
                <li><code class="rounded bg-slate-800 px-1">cryptoinfo_session</code> — Session management (required)</li>
                <li><code class="rounded bg-slate-800 px-1">XSRF-TOKEN</code> — CSRF protection (required)</li>
                <li><code class="rounded bg-slate-800 px-1">remember_web_*</code> — "Remember me" auth (optional, only if you check the box)</li>
            </ul>
            <p class="mt-2">We do not use tracking cookies, analytics cookies (Google Analytics, etc.) or third-party advertising cookies.</p>
        </div>

        <div class="glass rounded-2xl p-6">
            <h2 class="font-bold text-white mb-3">4. Third-Party Services</h2>
            <p class="mb-2">We use the following third-party services whose privacy policies apply when your browser contacts them:</p>
            <ul class="space-y-1.5">
                <li>→ <strong class="text-slate-300">Google Fonts</strong> — Font delivery (your browser fetches fonts from Google)</li>
                <li>→ <strong class="text-slate-300">CoinGecko</strong> — No direct user data is sent; data is fetched server-side</li>
                <li>→ <strong class="text-slate-300">Alternative.me</strong> — No direct user data is sent; data is fetched server-side</li>
            </ul>
        </div>

        <div class="glass rounded-2xl p-6">
            <h2 class="font-bold text-white mb-3">5. Data Retention</h2>
            <p>Account data is retained as long as your account exists. Newsletter subscriptions are retained until you unsubscribe. Server logs are purged after 30 days.</p>
        </div>

        <div class="glass rounded-2xl p-6">
            <h2 class="font-bold text-white mb-3">6. Your Rights</h2>
            <p>You have the right to access, correct or delete your personal data. To exercise these rights, <a href="{{ route('pages.contact') }}" class="text-blue-400 hover:underline">contact us</a>. We will respond within 30 days.</p>
        </div>

        <div class="glass rounded-2xl p-6">
            <h2 class="font-bold text-white mb-3">7. Contact</h2>
            <p>For privacy-related inquiries: <a href="{{ route('pages.contact') }}" class="text-blue-400 hover:underline">Contact page</a>.</p>
        </div>

    </div>
</div>

@endsection
