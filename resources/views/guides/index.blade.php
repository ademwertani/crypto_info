@extends('layouts.app')
@section('content')

<nav class="text-xs text-slate-500 mb-6" aria-label="Breadcrumb">
    <ol class="flex items-center gap-1.5">
        <li><a href="{{ route('crypto.index') }}" class="hover:text-white transition">Home</a></li>
        <li aria-hidden="true">/</li>
        <li class="text-slate-300">{{ __('guides.index_title') }}</li>
    </ol>
</nav>

<div class="mb-6">
    <h1 class="text-3xl font-extrabold text-white mb-2">{{ __('guides.index_title') }}</h1>
    <p class="text-slate-400">{{ __('guides.index_subtitle') }}</p>
</div>

@if($clusters->count() > 1)
<div class="mb-6 flex flex-wrap gap-2">
    <a href="{{ route('guides.index') }}"
       class="rounded-lg border px-3 py-1.5 text-xs font-medium transition {{ $cluster === '' ? 'border-blue-600 bg-blue-600/20 text-blue-400' : 'border-slate-700 text-slate-400 hover:border-slate-500 hover:text-white' }}">
        {{ __('guides.all_clusters') }}
    </a>
    @foreach($clusters as $c)
        <a href="{{ route('guides.index', ['cluster' => $c]) }}"
           class="rounded-lg border px-3 py-1.5 text-xs font-medium transition {{ $cluster === $c ? 'border-blue-600 bg-blue-600/20 text-blue-400' : 'border-slate-700 text-slate-400 hover:border-slate-500 hover:text-white' }}">
            {{ ucwords(str_replace('_', ' ', $c)) }}
        </a>
    @endforeach
</div>
@endif

@if($pages->isEmpty())
    <div class="flex flex-col items-center justify-center gap-3 py-20 text-slate-500">
        <p class="font-medium">{{ __('guides.no_pages') }}</p>
    </div>
@else

<div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
    @foreach($pages as $page)
    <a href="{{ route('guides.show', $page->slug) }}" class="glass rounded-2xl p-5 group flex flex-col">
        <span class="mb-3 inline-block w-fit rounded-md bg-blue-950/50 border border-blue-800/30 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-blue-400">
            {{ ucwords(str_replace('_', ' ', $page->type)) }}
        </span>
        <h2 class="font-bold text-white leading-snug mb-2 group-hover:text-blue-400 transition-colors">{{ $page->h1 }}</h2>
        @if($page->intro_html)
            <p class="text-sm text-slate-400 leading-relaxed line-clamp-3 mb-3">{{ strip_tags($page->intro_html) }}</p>
        @endif
        <p class="mt-auto text-xs text-slate-600">{{ __('news.reading_time', ['count' => $page->reading_time_min]) }}</p>
    </a>
    @endforeach
</div>

<div class="mt-8 flex justify-center">
    {{ $pages->links() }}
</div>

@endif

@endsection
