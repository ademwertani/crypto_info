@extends('layouts.app')
@section('title', 'Crypto News')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-white">Crypto News</h1>
    <p class="mt-1 text-sm text-slate-400">Latest cryptocurrency news and market updates</p>
</div>

@if ($news->isEmpty())
    <div class="flex flex-col items-center justify-center py-20 text-slate-500 gap-3">
        <p>No news articles yet.</p>
        <p class="text-sm">Run <code class="rounded bg-slate-800 px-1">php artisan app:fetch-news</code> to fetch articles.</p>
    </div>
@else

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach ($news as $article)
    <a href="{{ route('news.show', $article->slug) }}"
       class="group flex flex-col rounded-xl border border-slate-800 bg-slate-900/60 hover:border-slate-600 transition overflow-hidden">
        @if ($article->image_url)
            <img src="{{ $article->image_url }}" alt="{{ e($article->title) }}"
                 class="h-40 w-full object-cover" loading="lazy">
        @endif
        <div class="flex-1 p-4 flex flex-col gap-2">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <span>{{ $article->source ?? 'News' }}</span>
                <span>·</span>
                <span>{{ $article->published_at?->diffForHumans() ?? '' }}</span>
                @if ($article->sentiment !== 'neutral')
                    <span class="ml-auto rounded px-1.5 py-0.5 text-[10px] font-semibold
                        {{ $article->sentiment === 'positive' ? 'bg-emerald-900/50 text-emerald-400' : 'bg-red-900/50 text-red-400' }}">
                        {{ $article->sentiment }}
                    </span>
                @endif
            </div>
            <h2 class="font-semibold text-white group-hover:text-blue-400 transition leading-snug line-clamp-2">
                {{ e($article->title) }}
            </h2>
            @if ($article->ai_summary)
                <p class="text-xs text-slate-400 leading-relaxed line-clamp-2">{{ $article->ai_summary }}</p>
            @elseif ($article->summary)
                <p class="text-xs text-slate-400 leading-relaxed line-clamp-2">{{ $article->summary }}</p>
            @endif
        </div>
    </a>
    @endforeach
</div>

@if ($news->hasPages())
    <div class="mt-6 flex justify-center">
        {{ $news->links() }}
    </div>
@endif

@endif
@endsection
