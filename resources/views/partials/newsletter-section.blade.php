{{-- Phase 10 — Newsletter subscription section --}}
<section class="border-t border-slate-800/60 bg-gradient-to-b from-slate-950 to-blue-950/10 py-12" aria-labelledby="newsletter-heading">
    <div class="mx-auto max-w-screen-xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto text-center">
            <div class="inline-flex items-center gap-2 rounded-full bg-blue-500/10 border border-blue-500/20 px-4 py-1.5 text-xs font-semibold text-blue-400 mb-4">
                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                </svg>
                Free Newsletter
            </div>
            <h2 id="newsletter-heading" class="text-2xl font-bold text-white mb-2">
                Daily Crypto Intelligence, Delivered
            </h2>
            <p class="text-slate-400 text-sm mb-6">
                Get the <strong class="text-slate-300">Daily Crypto Report</strong> every morning — top movers, market sentiment, Fear &amp; Greed Index and key news.
                No spam, unsubscribe anytime.
            </p>

            @if(session('newsletter_status'))
                <div class="inline-flex items-center gap-2 rounded-xl bg-emerald-500/10 border border-emerald-500/20 px-5 py-3 text-sm text-emerald-400 font-medium mb-4">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ session('newsletter_status') }}
                </div>
            @else
                <form method="POST" action="{{ route('newsletter.subscribe') }}"
                      class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto"
                      aria-label="Subscribe to newsletter">
                    @csrf
                    <label for="newsletter-email" class="sr-only">Email address</label>
                    <input id="newsletter-email" type="email" name="email" required
                           placeholder="your@email.com"
                           class="flex-1 rounded-xl border border-slate-700 bg-slate-800/70 px-4 py-3 text-sm text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/40 transition-all"
                           aria-required="true">
                    <button type="submit"
                            class="rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-slate-950 shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30 transition-all whitespace-nowrap">
                        Subscribe Free
                    </button>
                </form>
                @error('email')
                    <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                @enderror
            @endif

            <p class="text-[11px] text-slate-600 mt-4">
                Join thousands of traders. Weekly Market Insights also available.
                <a href="{{ route('pages.privacy') }}" class="underline hover:text-slate-400 transition">Privacy Policy</a>.
            </p>
        </div>
    </div>
</section>
