@extends('layouts.app')
@section('content')

<div class="max-w-2xl mx-auto">

    <nav class="text-xs text-slate-500 mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center gap-1.5">
            <li><a href="{{ route('crypto.index') }}" class="hover:text-white transition">Home</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-slate-300">Contact</li>
        </ol>
    </nav>

    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-white mb-2">Contact Us</h1>
        <p class="text-slate-400">Questions, data corrections, partnership inquiries or feedback.</p>
    </div>

    @if(session('contact_sent'))
        <div class="glass rounded-2xl p-6 mb-6 border border-emerald-500/20 bg-emerald-950/10">
            <div class="flex items-center gap-3 text-emerald-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <p class="font-semibold">Message sent! We'll reply within 24 hours.</p>
            </div>
        </div>
    @endif

    <div class="grid sm:grid-cols-5 gap-6">

        {{-- Form --}}
        <div class="sm:col-span-3">
            <form method="POST" action="{{ route('pages.contact') }}" class="glass rounded-2xl p-6 space-y-4" aria-label="Contact form">
                @csrf
                <div>
                    <label for="contact-name" class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Name</label>
                    <input id="contact-name" type="text" name="name" value="{{ old('name') }}" required
                           class="w-full rounded-xl border border-slate-700 bg-slate-800/60 px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/40 transition-all"
                           placeholder="Your name">
                    @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="contact-email" class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Email</label>
                    <input id="contact-email" type="email" name="email" value="{{ old('email') }}" required
                           class="w-full rounded-xl border border-slate-700 bg-slate-800/60 px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/40 transition-all"
                           placeholder="you@example.com">
                    @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="contact-subject" class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Subject</label>
                    <select id="contact-subject" name="subject"
                            class="w-full rounded-xl border border-slate-700 bg-slate-800/60 px-4 py-2.5 text-sm text-slate-100 outline-none focus:border-blue-500 transition-all">
                        <option value="general">General Question</option>
                        <option value="data">Data Error / Correction</option>
                        <option value="partnership">Partnership / Advertising</option>
                        <option value="api">API Access</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label for="contact-message" class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Message</label>
                    <textarea id="contact-message" name="message" rows="5" required
                              class="w-full rounded-xl border border-slate-700 bg-slate-800/60 px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/40 transition-all resize-none"
                              placeholder="Describe your question or report…">{{ old('message') }}</textarea>
                    @error('message') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <button type="submit"
                        class="w-full rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-slate-950 shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30 transition-all">
                    Send Message
                </button>
            </form>
        </div>

        {{-- Info sidebar --}}
        <div class="sm:col-span-2 space-y-4">
            <div class="glass rounded-2xl p-5">
                <h2 class="font-semibold text-white text-sm mb-3">Response Times</h2>
                <div class="space-y-2 text-xs text-slate-400">
                    <div class="flex justify-between"><span>General questions</span><span class="text-slate-300">24h</span></div>
                    <div class="flex justify-between"><span>Data corrections</span><span class="text-slate-300">6h</span></div>
                    <div class="flex justify-between"><span>Partnerships</span><span class="text-slate-300">48h</span></div>
                </div>
            </div>
            <div class="glass rounded-2xl p-5">
                <h2 class="font-semibold text-white text-sm mb-3">Useful Links</h2>
                <ul class="space-y-2 text-xs">
                    <li><a href="{{ route('pages.methodology') }}" class="text-blue-400 hover:underline">Our Data Methodology</a></li>
                    <li><a href="{{ route('pages.about') }}" class="text-blue-400 hover:underline">About CryptoInfo</a></li>
                    <li><a href="{{ route('pages.privacy') }}" class="text-blue-400 hover:underline">Privacy Policy</a></li>
                    <li><a href="{{ route('pages.terms') }}" class="text-blue-400 hover:underline">Terms of Service</a></li>
                    <li><a href="{{ route('api.docs') }}" class="text-blue-400 hover:underline">API Documentation</a></li>
                </ul>
            </div>
        </div>

    </div>
</div>

@endsection
