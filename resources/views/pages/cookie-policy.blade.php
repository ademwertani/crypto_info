@extends('layouts.app')
@section('content')

<div class="max-w-3xl mx-auto">
    <nav class="text-xs text-slate-500 mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center gap-1.5">
            <li><a href="{{ route('crypto.index') }}" class="hover:text-white transition">Home</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-slate-300">Cookie Policy</li>
        </ol>
    </nav>

    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-white mb-2">Cookie Policy</h1>
        <p class="text-slate-400">Last updated: {{ date('F d, Y') }}</p>
    </div>

    <div class="space-y-5 text-sm text-slate-400 leading-relaxed">
        <div class="glass rounded-2xl p-6">
            <h2 class="font-bold text-white mb-3">1. What Are Cookies</h2>
            <p>Cookies are small text files stored on your device by your browser. We use only the strictly necessary cookies described below to operate CryptoInfo.</p>
        </div>

        <div class="glass rounded-2xl p-6">
            <h2 class="font-bold text-white mb-3">2. Cookies We Currently Use</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="text-[10px] uppercase tracking-wider text-slate-500 border-b border-slate-800">
                            <th class="py-2 text-left">Cookie</th>
                            <th class="py-2 text-left">Purpose</th>
                            <th class="py-2 text-left">Type</th>
                            <th class="py-2 text-right">Duration</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        <tr>
                            <td class="py-2 font-mono">cryptoinfo_session</td>
                            <td class="py-2">Keeps your session (language, theme) working</td>
                            <td class="py-2">Strictly necessary</td>
                            <td class="py-2 text-right">Session</td>
                        </tr>
                        <tr>
                            <td class="py-2 font-mono">XSRF-TOKEN</td>
                            <td class="py-2">Cross-Site Request Forgery protection</td>
                            <td class="py-2">Strictly necessary</td>
                            <td class="py-2 text-right">Session</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="mt-3">We do <strong class="text-slate-300">not</strong> currently set any analytics, advertising or third-party tracking cookies.</p>
        </div>

        <div class="glass rounded-2xl p-6 border border-blue-500/20 bg-blue-950/10">
            <h2 class="font-bold text-white mb-3">3. Future Advertising Cookies</h2>
            <p class="mb-2">CryptoInfo is preparing to work with advertising partners in the future. If and when advertising is enabled on this site:</p>
            <ul class="space-y-1.5">
                <li>- This page will be updated to list every advertising/analytics cookie in use, its purpose and duration.</li>
                <li>- A consent management platform (CMP) will be displayed to EU/UK/California visitors (and anywhere else required by law) before any non-essential cookie is set, in line with GDPR/ePrivacy and CCPA requirements.</li>
                <li>- Visitors will be able to accept, reject or customize consent at any time via a cookie preferences link in the footer.</li>
            </ul>
            <p class="mt-2 text-xs text-slate-600">Advertising is currently disabled site-wide (<code class="rounded bg-slate-800 px-1">ADS_ENABLED=false</code>). No advertising or tracking script is loaded today.</p>
        </div>

        <div class="glass rounded-2xl p-6">
            <h2 class="font-bold text-white mb-3">4. Managing Cookies</h2>
            <p>Most browsers let you block or delete cookies via their settings. Blocking strictly necessary cookies may affect basic site functionality (e.g. language preference, form submission).</p>
        </div>

        <div class="glass rounded-2xl p-6">
            <h2 class="font-bold text-white mb-3">5. Contact</h2>
            <p>Questions about this Cookie Policy? Use the <a href="{{ route('pages.contact') }}" class="text-blue-400 hover:underline">contact page</a>. See also our <a href="{{ route('pages.privacy') }}" class="text-blue-400 hover:underline">Privacy Policy</a>.</p>
        </div>
    </div>
</div>

@endsection
