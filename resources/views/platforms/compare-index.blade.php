@extends('layouts.money-page')

@section('money-page-content')

<div class="max-w-3xl mx-auto">

    <nav class="text-xs text-slate-500 mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center gap-1.5">
            <li><a href="{{ route('crypto.index') }}" class="hover:text-white transition">Home</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-slate-300">{{ __('platforms.title') }}</li>
        </ol>
    </nav>

    <div class="mb-6">
        <h1 class="text-3xl font-extrabold text-white mb-2 leading-tight">{{ __('platforms.title') }}</h1>
        <p class="text-sm text-slate-400 max-w-2xl">{{ __('platforms.subtitle') }}</p>
    </div>

    @if ($comparisons->isEmpty())
        <div class="glass rounded-2xl p-8 text-center text-slate-500">
            {{ __('platforms.no_results') }}
        </div>
    @else
    <div class="space-y-3">
        @foreach ($comparisons as $cmp)
            @php $a = $cmp->platformA; $b = $cmp->platformB; @endphp
            @continue(! $a || ! $b)
            <details class="glass rounded-2xl p-5 group">
                <summary class="cursor-pointer list-none marker:content-none">
                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <div class="flex-1 text-center sm:text-left">
                            <p class="font-bold text-white">{{ $a->name }}</p>
                            <span class="inline-block mt-1 rounded-full bg-blue-950/60 border border-blue-700/40 px-3 py-1 text-xs font-semibold text-blue-300">
                                {{ __('platforms.best_for_label') }}: {{ $a->best_for }}
                            </span>
                        </div>

                        <span class="text-slate-600 font-semibold shrink-0" aria-hidden="true">vs</span>

                        <div class="flex-1 text-center sm:text-right">
                            <p class="font-bold text-white">{{ $b->name }}</p>
                            <span class="inline-block mt-1 rounded-full bg-purple-950/60 border border-purple-700/40 px-3 py-1 text-xs font-semibold text-purple-300">
                                {{ __('platforms.best_for_label') }}: {{ $b->best_for }}
                            </span>
                        </div>

                        <span class="text-slate-500 shrink-0 group-open:rotate-180 transition-transform">⌄</span>
                    </div>
                </summary>

                <div class="mt-5 pt-5 border-t border-slate-800/60 grid gap-5 sm:grid-cols-2">
                    @foreach ([$a, $b] as $platform)
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">{{ $platform->name }}</p>
                        <ul class="text-xs text-slate-400 space-y-1 mb-3">
                            <li>{{ __('platforms.kyc') }}: {{ $platform->requires_kyc ? __('platforms.yes') : __('platforms.no') }}</li>
                            <li>{{ __('platforms.cards') }}: {{ $platform->supports_cards ? __('platforms.yes') : __('platforms.no') }}</li>
                            <li>
                                {{ __('platforms.fees') }}: {{ $platform->fee_summary ?: '—' }}
                                @unless ($platform->isFeeVerified())
                                    <span class="text-amber-400">({{ __('platforms.fees_unverified') }})</span>
                                @endunless
                            </li>
                        </ul>
                        @if ($platform->affiliate_url)
                            <x-affiliate-link
                                :href="$platform->affiliate_url"
                                :network="$platform->slug"
                                placement="platform_compare"
                                class="inline-block rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-500 transition"
                            >{{ __('platforms.visit') }} {{ $platform->name }} ↗</x-affiliate-link>
                        @endif
                    </div>
                    @endforeach
                </div>

                <div class="mt-5 pt-5 border-t border-slate-800/60 text-sm text-slate-300 leading-relaxed">
                    {!! $cmp->verdict_html !!}
                </div>
            </details>
        @endforeach
    </div>
    @endif

</div>

@endsection
