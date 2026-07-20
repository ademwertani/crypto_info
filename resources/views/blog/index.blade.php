@extends('layouts.app')
@section('content')

<nav class="text-xs text-slate-500 mb-6" aria-label="Breadcrumb">
    <ol class="flex items-center gap-1.5">
        <li><a href="{{ route('crypto.index') }}" class="hover:text-white transition">Home</a></li>
        <li aria-hidden="true">/</li>
        <li class="text-slate-300">{{ __('blog.title') }}</li>
    </ol>
</nav>

<div class="mb-6">
    <h1 class="text-3xl font-extrabold text-white mb-2">{{ __('blog.title') }}</h1>
    <p class="text-slate-400">{{ __('blog.subtitle') }}</p>
</div>

{{-- Category filter --}}
<div class="mb-6 flex flex-wrap gap-2">
    <a href="{{ route('blog.index') }}"
       class="rounded-lg border px-3 py-1.5 text-xs font-medium transition {{ !$category ? 'border-blue-600 bg-blue-600/20 text-blue-400' : 'border-slate-700 text-slate-400 hover:border-slate-500 hover:text-white' }}">
        {{ __('blog.all_categories') }}
    </a>
    @foreach($categories as $cat)
        <a href="{{ route('blog.index', ['category' => $cat->slug]) }}"
           class="rounded-lg border px-3 py-1.5 text-xs font-medium transition {{ $category && $category->id === $cat->id ? 'border-blue-600 bg-blue-600/20 text-blue-400' : 'border-slate-700 text-slate-400 hover:border-slate-500 hover:text-white' }}">
            {{ $cat->name }}
        </a>
    @endforeach
</div>

@if($articles->isEmpty())
    <div class="flex flex-col items-center justify-center gap-3 py-20 text-slate-500">
        <p class="font-medium">{{ __('blog.no_articles') }}</p>
    </div>
@else

<div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
    @foreach($articles as $article)
    <a href="{{ route('blog.show', $article->slug) }}" class="glass rounded-2xl overflow-hidden group flex flex-col">
        <div class="h-40 w-full bg-slate-800/60 overflow-hidden shrink-0">
            @if($article->cover_image_url)
                <img src="{{ $article->cover_image_url }}" alt="{{ e($article->title) }}"
                     class="h-40 w-full object-cover group-hover:scale-105 transition-transform duration-300"
                     width="400" height="160" loading="lazy">
            @else
                <div class="h-40 w-full flex items-center justify-center bg-gradient-to-br from-slate-800 to-slate-900 text-slate-600 text-xs font-semibold uppercase tracking-wider">
                    CryptoInfo
                </div>
            @endif
        </div>
        <div class="p-5 flex flex-col flex-1">
            @if($article->category)
                <span class="mb-2 inline-block w-fit rounded-md bg-blue-950/50 border border-blue-800/30 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-blue-400">
                    {{ $article->category->name }}
                </span>
            @endif
            <h2 class="font-bold text-white leading-snug mb-2 group-hover:text-blue-400 transition-colors">{{ $article->title }}</h2>
            @if($article->excerpt)
                <p class="text-sm text-slate-400 leading-relaxed line-clamp-3 mb-3">{{ $article->excerpt }}</p>
            @endif
            <p class="mt-auto text-xs text-slate-600">{{ __('blog.published_on') }} {{ optional($article->published_at)->format('M d, Y') }}</p>
        </div>
    </a>
    @endforeach
</div>

<div class="mt-8 flex justify-center">
    {{ $articles->links() }}
</div>

@endif

@endsection
