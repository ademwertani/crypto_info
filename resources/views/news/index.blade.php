@extends('layouts.app')
@section('content')

<nav class="text-xs text-slate-500 mb-6" aria-label="Breadcrumb">
    <ol class="flex items-center gap-1.5">
        <li><a href="{{ route('crypto.index') }}" class="hover:text-white transition">Home</a></li>
        <li aria-hidden="true">/</li>
        <li class="text-slate-300">{{ __('news.title') }}</li>
    </ol>
</nav>

<div class="mb-6">
    <h1 class="text-3xl font-extrabold text-white mb-2">{{ __('news.title') }}</h1>
    <p class="text-slate-400">{{ __('news.subtitle') }}</p>
</div>

{{-- Search --}}
<form method="GET" action="{{ route('news.index') }}" class="mb-6 max-w-md" role="search">
    <label for="news-search" class="sr-only">{{ __('news.search_placeholder') }}</label>
    <div class="relative">
        <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400"
             fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
        </svg>
        <input id="news-search" type="search" name="search" value="{{ $search }}"
               placeholder="{{ __('news.search_placeholder') }}" autocomplete="off"
               class="w-full rounded-lg border border-slate-700/60 bg-slate-800/60 py-2 pl-10 pr-4 text-sm text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500/80 focus:bg-slate-800 focus:ring-1 focus:ring-blue-500/40 transition-all">
    </div>
</form>

@if($news->isEmpty())
    <div class="flex flex-col items-center justify-center gap-3 py-20 text-slate-500">
        <p class="font-medium">{{ __('news.no_results') }}</p>
    </div>
@else

<div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
    @foreach($news as $post)
    <a href="{{ route('news.show', $post->slug) }}" class="glass rounded-2xl overflow-hidden group flex flex-col">
        <div class="h-40 w-full bg-slate-800/60 overflow-hidden shrink-0">
            @if($post->featured_image_url)
                <img src="{{ $post->featured_image_url }}" alt="{{ e($post->title) }}"
                     class="h-40 w-full object-cover group-hover:scale-105 transition-transform duration-300"
                     width="400" height="160" loading="lazy">
            @else
                <div class="h-40 w-full flex items-center justify-center bg-gradient-to-br from-slate-800 to-slate-900 text-slate-600 text-xs font-semibold uppercase tracking-wider">
                    CryptoInfo
                </div>
            @endif
        </div>
        <div class="p-5 flex flex-col flex-1">
            <h2 class="font-bold text-white leading-snug mb-2 group-hover:text-blue-400 transition-colors">{{ $post->title }}</h2>
            @if($post->excerpt)
                <p class="text-sm text-slate-400 leading-relaxed line-clamp-3 mb-3">{{ $post->excerpt }}</p>
            @endif
            <div class="mt-auto flex items-center justify-between">
                <p class="text-xs text-slate-600">{{ __('news.published_on') }} {{ optional($post->published_at)->format('M d, Y') }}</p>
                <span class="text-xs font-medium text-blue-400 group-hover:underline">{{ __('news.read_more') }} →</span>
            </div>
        </div>
    </a>
    @endforeach
</div>

<div class="mt-8 flex justify-center">
    {{ $news->links() }}
</div>

@endif

@endsection
