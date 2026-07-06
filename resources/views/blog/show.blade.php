@extends('layouts.app')
@section('content')

<div class="max-w-3xl mx-auto">

    <nav class="text-xs text-slate-500 mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center gap-1.5">
            <li><a href="{{ route('crypto.index') }}" class="hover:text-white transition">Home</a></li>
            <li aria-hidden="true">/</li>
            <li><a href="{{ route('blog.index') }}" class="hover:text-white transition">{{ __('blog.title') }}</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-slate-300 truncate max-w-[16rem]">{{ $article->title }}</li>
        </ol>
    </nav>

    <div class="mb-5">
        @if($article->category)
            <a href="{{ route('blog.index', ['category' => $article->category->slug]) }}"
               class="mb-3 inline-block rounded-md bg-blue-950/50 border border-blue-800/30 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-blue-400 hover:bg-blue-900/40 transition">
                {{ $article->category->name }}
            </a>
        @endif
        <h1 class="text-3xl font-extrabold text-white mb-2 leading-tight">{{ $article->title }}</h1>
        <p class="text-xs text-slate-500">
            {{ __('blog.published_on') }} {{ optional($article->published_at)->format('F d, Y') }}
            &nbsp;·&nbsp; {{ $article->author_name }}
        </p>
    </div>

    @if($article->cover_image_url)
        <div class="mb-6 h-56 sm:h-72 w-full overflow-hidden rounded-2xl bg-slate-800/60">
            <img src="{{ $article->cover_image_url }}" alt="{{ e($article->title) }}"
                 class="h-full w-full object-cover" width="800" height="288" loading="lazy">
        </div>
    @endif

    <div class="mb-6">
        @include('partials.content-disclaimer')
    </div>

    <article class="glass rounded-2xl p-6 sm:p-8">
        @php
            $sections   = $article->sections ?? [];
            $lastIndex  = count($sections) - 1;
            $middleIndex = intdiv($lastIndex, 2);
        @endphp

        @foreach($sections as $i => $html)
            @if($i === $middleIndex && $middleIndex > 0 && $middleIndex < $lastIndex)
                @include('partials.ad-rectangle', ['position' => 'article-middle'])
            @endif
            @if($i === $lastIndex && $lastIndex > 1)
                @include('partials.ad-rectangle', ['position' => 'article-before-conclusion'])
            @endif

            <div class="article-section text-sm text-slate-300 leading-relaxed">
                {!! $html !!}
            </div>

            @if($i === 0 && $lastIndex >= 2)
                @include('partials.ad-rectangle', ['position' => 'article-after-intro'])
            @endif
        @endforeach
    </article>

    @if($relatedCoins->isNotEmpty())
    <div class="mt-6 glass rounded-2xl p-5">
        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('blog.related_coins') }}</p>
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

    @if($related->isNotEmpty())
    <div class="mt-8">
        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('blog.related_articles') }}</p>
        <div class="grid gap-4 sm:grid-cols-3">
            @foreach($related as $r)
                <a href="{{ route('blog.show', $r->slug) }}" class="glass rounded-xl p-4 hover:border-blue-700/50 transition">
                    <p class="font-semibold text-white text-sm leading-snug mb-1">{{ $r->title }}</p>
                    <p class="text-xs text-slate-500">{{ optional($r->published_at)->format('M d, Y') }}</p>
                </a>
            @endforeach
        </div>
    </div>
    @endif

    <div class="mt-8">
        <a href="{{ route('blog.index') }}" class="text-sm text-blue-400 hover:underline">{{ __('blog.back_to_blog') }}</a>
    </div>

</div>

@endsection
