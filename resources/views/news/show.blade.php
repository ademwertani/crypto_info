@extends('layouts.app')
@section('content')

<div class="max-w-3xl mx-auto">

    <nav class="text-xs text-slate-500 mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center gap-1.5">
            <li><a href="{{ route('crypto.index') }}" class="hover:text-white transition">Home</a></li>
            <li aria-hidden="true">/</li>
            <li><a href="{{ route('news.index') }}" class="hover:text-white transition">{{ __('news.title') }}</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-slate-300 truncate max-w-[16rem]">{{ $news->title }}</li>
        </ol>
    </nav>

    <div class="mb-5">
        <h1 class="text-3xl font-extrabold text-white mb-2 leading-tight">{{ $news->title }}</h1>
        <p class="text-xs text-slate-500">
            {{ __('news.published_on') }} {{ optional($news->published_at)->format('F d, Y') }}
            &nbsp;·&nbsp; {{ __('news.reading_time', ['count' => $news->reading_time]) }}
        </p>
    </div>

    @if($news->featured_image_url)
        <div class="mb-6 h-56 sm:h-72 w-full overflow-hidden rounded-2xl bg-slate-800/60">
            <img src="{{ $news->featured_image_url }}" alt="{{ e($news->title) }}"
                 class="h-full w-full object-cover" width="800" height="288" loading="lazy">
        </div>
    @endif

    <div class="mb-6">
        @include('partials.content-disclaimer')
    </div>

    <article class="glass rounded-2xl p-6 sm:p-8">
        <div class="article-section text-sm text-slate-300 leading-relaxed">
            {!! $news->content !!}
        </div>
    </article>

    @if($news->source_url)
    <p class="mt-4 text-xs text-slate-500">
        {{ __('news.source') }}:
        <a href="{{ $news->source_url }}" target="_blank" rel="noopener nofollow" class="text-blue-400 hover:underline">
            {{ $news->source_name ?: __('news.original_article') }} ↗
        </a>
    </p>
    @endif

    {{-- Share --}}
    <div class="mt-6 flex flex-wrap items-center gap-3">
        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('news.share') }}</span>
        <a href="https://twitter.com/intent/tweet?url={{ urlencode(route('news.show', $news->slug)) }}&text={{ urlencode($news->title) }}"
           target="_blank" rel="noopener" aria-label="Share on X"
           class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-700 text-slate-400 hover:border-blue-500 hover:text-white transition">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
        </a>
        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('news.show', $news->slug)) }}"
           target="_blank" rel="noopener" aria-label="Share on Facebook"
           class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-700 text-slate-400 hover:border-blue-500 hover:text-white transition">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M22 12.06C22 6.505 17.523 2 12 2S2 6.505 2 12.06c0 5.02 3.657 9.184 8.438 9.94v-7.03H7.898v-2.91h2.54V9.845c0-2.507 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.242 0-1.63.771-1.63 1.562v1.878h2.773l-.443 2.91h-2.33V22c4.78-.756 8.437-4.92 8.437-9.94z"/></svg>
        </a>
        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(route('news.show', $news->slug)) }}"
           target="_blank" rel="noopener" aria-label="Share on LinkedIn"
           class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-700 text-slate-400 hover:border-blue-500 hover:text-white transition">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 1 1 0-4.124 2.062 2.062 0 0 1 0 4.124zM7.114 20.452H3.558V9h3.556v11.452z"/></svg>
        </a>
        <a href="https://api.whatsapp.com/send?text={{ urlencode($news->title.' '.route('news.show', $news->slug)) }}"
           target="_blank" rel="noopener" aria-label="Share on WhatsApp"
           class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-700 text-slate-400 hover:border-blue-500 hover:text-white transition">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12.04 2c-5.52 0-10 4.48-10 10 0 1.77.46 3.45 1.27 4.9L2 22l5.25-1.38A9.94 9.94 0 0 0 12.04 22c5.52 0 10-4.48 10-10s-4.48-10-10-10zm0 18.13c-1.6 0-3.13-.43-4.46-1.24l-.32-.19-3.12.82.83-3.04-.2-.31A8.1 8.1 0 0 1 3.9 12c0-4.49 3.65-8.13 8.14-8.13S20.18 7.51 20.18 12s-3.65 8.13-8.14 8.13zm4.47-6.09c-.24-.12-1.44-.71-1.66-.79-.22-.08-.39-.12-.55.12-.16.24-.63.79-.77.95-.14.16-.28.18-.52.06-.24-.12-1.02-.38-1.94-1.2-.72-.64-1.2-1.43-1.35-1.67-.14-.24-.02-.37.11-.49.11-.11.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.55-1.32-.75-1.8-.2-.48-.4-.42-.55-.42-.14 0-.3-.02-.46-.02s-.42.06-.64.3c-.22.24-.85.83-.85 2.02 0 1.19.87 2.34.99 2.5.12.16 1.71 2.61 4.14 3.66.58.25 1.03.4 1.38.51.58.18 1.11.16 1.53.1.47-.07 1.44-.59 1.64-1.16.2-.57.2-1.06.14-1.16-.06-.1-.22-.16-.46-.28z"/></svg>
        </a>
    </div>

    @if($recent->isNotEmpty())
    <div class="mt-8">
        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('news.recent_news') }}</p>
        <div class="grid gap-4 sm:grid-cols-3">
            @foreach($recent as $r)
                <a href="{{ route('news.show', $r->slug) }}" class="glass rounded-xl p-4 hover:border-blue-700/50 transition">
                    <p class="font-semibold text-white text-sm leading-snug mb-1">{{ $r->title }}</p>
                    <p class="text-xs text-slate-500">{{ optional($r->published_at)->format('M d, Y') }}</p>
                </a>
            @endforeach
        </div>
    </div>
    @endif

    <div class="mt-8">
        <a href="{{ route('news.index') }}" class="text-sm text-blue-400 hover:underline">{{ __('news.back_to_news') }}</a>
    </div>

</div>

@endsection
