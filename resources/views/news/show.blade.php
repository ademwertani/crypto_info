@extends('layouts.app')
@section('title', $news->title)
@section('content')

<div class="mx-auto max-w-3xl">

    <nav class="mb-4 flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('news.index') }}" class="hover:text-white">News</a>
        <span>/</span>
        <span class="line-clamp-1 text-slate-300">{{ $news->title }}</span>
    </nav>

    <div class="mb-2 flex items-center gap-2 text-xs text-slate-500">
        <span>{{ $news->source ?? 'Crypto News' }}</span>
        <span>·</span>
        <span>{{ $news->published_at?->format('M j, Y') }}</span>
        @if ($news->sentiment !== 'neutral')
            <span class="ml-2 rounded px-1.5 py-0.5 font-semibold
                {{ $news->sentiment === 'positive' ? 'bg-emerald-900/50 text-emerald-400' : 'bg-red-900/50 text-red-400' }}">
                {{ $news->sentiment }}
            </span>
        @endif
    </div>

    <h1 class="mb-6 text-3xl font-bold text-white leading-tight">{{ e($news->title) }}</h1>

    @if ($news->image_url)
        <img src="{{ $news->image_url }}" alt="{{ e($news->title) }}"
             class="mb-6 w-full rounded-xl object-cover max-h-80">
    @endif

    {{-- AI Summary --}}
    @if ($news->ai_summary)
    <div class="mb-6 rounded-xl border border-blue-800/40 bg-blue-950/30 p-4">
        <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-blue-400">AI Summary</p>
        <p class="text-sm text-slate-300 leading-relaxed">{{ $news->ai_summary }}</p>
    </div>
    @endif

    <div class="mb-6 text-slate-300 leading-relaxed">
        {{ $news->summary ?? '' }}
    </div>

    <a href="{{ $news->url }}" target="_blank" rel="noopener"
       class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-500 transition">
        Read Full Article ↗
    </a>

    {{-- Related articles --}}
    @if ($relatedItems->isNotEmpty())
    <div class="mt-10">
        <h2 class="mb-4 text-lg font-semibold text-white">More News</h2>
        <div class="grid gap-3 sm:grid-cols-2">
            @foreach ($relatedItems as $rel)
            <a href="{{ route('news.show', $rel->slug) }}"
               class="rounded-lg border border-slate-800 bg-slate-900/60 p-4 hover:border-slate-600 transition">
                <p class="text-xs text-slate-500 mb-1">{{ $rel->published_at?->diffForHumans() }}</p>
                <p class="text-sm font-medium text-white line-clamp-2">{{ e($rel->title) }}</p>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
