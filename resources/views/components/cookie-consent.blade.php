{{--
    Minimal, non-blocking GDPR cookie banner. Never covers the page (no
    backdrop/overlay) — it's a fixed bar the visitor can ignore and keep
    browsing. Decision is stored client-side only (localStorage, see the
    Alpine.store('consent') in resources/js/app.js) and can be reopened at
    any time via the "Cookies" link in the footer.
--}}
@php $isRtl = app()->getLocale() === 'ar'; @endphp

<div
    x-data
    x-show="$store.consent.show"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
    class="fixed inset-x-0 bottom-0 z-[100] px-4 pb-4 sm:px-6"
    role="dialog"
    aria-modal="false"
    aria-live="polite"
    aria-label="{{ __('cookie.title') }}"
    @if($isRtl) dir="rtl" @endif
>
    <div class="mx-auto max-w-screen-xl rounded-2xl border border-slate-700/60 bg-slate-900/98 shadow-2xl shadow-black/40 backdrop-blur-md p-5 sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div class="min-w-0">
                <p class="text-sm font-semibold text-white mb-1">{{ __('cookie.title') }}</p>
                <p class="text-xs text-slate-400 leading-relaxed max-w-2xl">
                    {{ __('cookie.message') }}
                    <a href="{{ route('pages.privacy') }}" class="text-blue-400 hover:underline">{{ __('cookie.privacy_link') }}</a>.
                </p>
                <div class="mt-2.5 flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-slate-500">
                    <span><strong class="text-slate-300">{{ __('cookie.essential_label') }}</strong> — {{ __('cookie.essential_desc') }}</span>
                    <span><strong class="text-slate-300">{{ __('cookie.analytics_label') }}</strong> — {{ __('cookie.analytics_desc') }}</span>
                </div>
            </div>

            <div class="flex shrink-0 items-center gap-2.5">
                <button
                    type="button"
                    @click="$store.consent.reject()"
                    class="rounded-lg border border-slate-700 px-4 py-2 text-xs font-semibold text-slate-300 hover:border-slate-500 hover:text-white transition"
                >
                    {{ __('cookie.reject') }}
                </button>
                <button
                    type="button"
                    @click="$store.consent.accept()"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-500 transition"
                >
                    {{ __('cookie.accept') }}
                </button>
            </div>

        </div>
    </div>
</div>
