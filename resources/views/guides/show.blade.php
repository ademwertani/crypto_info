@extends('layouts.money-page')

@section('money-page-content')

<div class="max-w-3xl mx-auto">

    @if($isPreview ?? false)
        <div class="mb-6 rounded-lg border border-amber-500/30 bg-amber-950/20 px-4 py-3 text-sm text-amber-300" role="status">
            <strong>Preview</strong> — status: {{ $moneyPage->status }}. This page is not live; only you can see this.
        </div>
    @endif

    <nav class="text-xs text-slate-500 mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center gap-1.5">
            <li><a href="{{ route('crypto.index') }}" class="hover:text-white transition">Home</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-slate-300 truncate max-w-[16rem]">{{ $moneyPage->h1 }}</li>
        </ol>
    </nav>

    <div class="mb-5">
        <span class="mb-3 inline-block rounded-md bg-blue-950/50 border border-blue-800/30 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-blue-400">
            {{ str_replace('_', ' ', $moneyPage->type) }}
        </span>
        <h1 class="text-3xl font-extrabold text-white mb-2 leading-tight">{{ $moneyPage->h1 }}</h1>
        <p class="text-xs text-slate-500">
            @if($moneyPage->author){{ $moneyPage->author }} &nbsp;·&nbsp; @endif
            {{ optional($moneyPage->published_at)->format('F d, Y') }}
            &nbsp;·&nbsp; {{ __('news.reading_time', ['count' => $moneyPage->reading_time_min]) }}
        </p>
    </div>

    @if($moneyPage->intro_html)
        <div class="mb-6 text-sm text-slate-300 leading-relaxed">
            {!! $moneyPage->intro_html !!}
        </div>
    @endif

    @if(!empty($toc))
    <nav class="mb-6 glass rounded-xl p-4" aria-label="{{ __('guides.toc_title') }}">
        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('guides.toc_title') }}</p>
        <ol class="space-y-1 text-sm">
            @foreach($toc as $item)
                <li><a href="#{{ $item['id'] }}" class="text-blue-400 hover:underline">{{ $item['label'] }}</a></li>
            @endforeach
        </ol>
    </nav>
    @endif

    <article class="glass rounded-2xl p-6 sm:p-8 article-section text-sm text-slate-300 leading-relaxed">
        {!! $moneyPage->bodyWithHeadingAnchors() !!}
    </article>

    @if(!empty($moneyPage->cta_config))
    <div class="mt-6 flex flex-wrap gap-3">
        @foreach($moneyPage->cta_config as $cta)
            @if(!empty($cta['href']) && !empty($cta['label']))
            <x-affiliate-link
                :href="$cta['href']"
                :network="$cta['network'] ?? 'other'"
                :placement="$cta['placement'] ?? 'guide_cta'"
                :coin="$cta['coin'] ?? null"
                class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-blue-500 transition"
            >{{ $cta['label'] }} ↗</x-affiliate-link>
            @endif
        @endforeach
    </div>
    @endif

    @if(!empty($moneyPage->faq))
    <div class="mt-8">
        <h2 class="mb-3 text-lg font-bold text-white">{{ __('guides.faq_title') }}</h2>
        <div class="space-y-2">
            @foreach($moneyPage->faq as $item)
                @if(!empty($item['q']) && !empty($item['a']))
                <details class="glass rounded-xl p-4 group">
                    <summary class="cursor-pointer font-semibold text-white text-sm marker:content-none flex items-center justify-between gap-3">
                        <span>{{ $item['q'] }}</span>
                        <span class="text-slate-500 shrink-0 group-open:rotate-180 transition-transform">⌄</span>
                    </summary>
                    <p class="mt-2 text-sm text-slate-400 leading-relaxed">{{ $item['a'] }}</p>
                </details>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    @if($relatedCoins->isNotEmpty())
    <div class="mt-8 glass rounded-2xl p-5">
        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('guides.related_coins') }}</p>
        <div class="flex flex-wrap gap-2">
            @foreach($relatedCoins as $coin)
                <a href="{{ route('crypto.show', $coin->slug) }}"
                   class="flex items-center gap-2 rounded-lg border border-slate-700 px-3 py-2 text-sm text-slate-300 hover:border-blue-500 hover:text-white transition">
                    @if($coin->image_url)
                        <img src="{{ $coin->image_url }}" alt="{{ e($coin->name) }}" class="h-5 w-5 rounded-full" width="20" height="20" loading="lazy">
                    @endif
                    {{ $coin->name }}
                    <span class="text-xs text-slate-500 uppercase">{{ $coin->symbol }}</span>
                </a>
            @endforeach
        </div>
    </div>
    @endif

    @if($relatedPages->isNotEmpty())
    <div class="mt-8">
        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('guides.related_guides') }}</p>
        <div class="grid gap-4 sm:grid-cols-3">
            @foreach($relatedPages as $p)
                <a href="{{ route('guides.show', $p->slug) }}" class="glass rounded-xl p-4 hover:border-blue-700/50 transition">
                    <p class="font-semibold text-white text-sm leading-snug">{{ $p->h1 }}</p>
                </a>
            @endforeach
        </div>
    </div>
    @endif

</div>

@endsection
