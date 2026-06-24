@extends('layouts.app')
@section('title', 'API Documentation')
@section('content')

<div class="mx-auto max-w-3xl">

<div class="mb-8">
    <h1 class="text-3xl font-bold text-white">REST API Documentation</h1>
    <p class="mt-2 text-slate-400">Free, rate-limited JSON API. No authentication required.</p>
    <p class="mt-1 text-sm text-slate-500">Base URL: <code class="rounded bg-slate-800 px-2 py-0.5 text-blue-300">{{ url('/api') }}</code></p>
    <p class="mt-1 text-sm text-slate-500">Rate limit: <code class="rounded bg-slate-800 px-2 py-0.5 text-slate-300">60 requests / minute per IP</code></p>
</div>

@php
$endpoints = [
    ['GET', '/api/coins', 'List all coins (paginated)', ['page' => 'Page number (default: 1)', 'per_page' => 'Results per page (10–100, default: 50)']],
    ['GET', '/api/coins/{slug}', 'Get a single coin by slug', []],
    ['GET', '/api/gainers', 'Top 50 gainers by 24h change', []],
    ['GET', '/api/losers', 'Top 50 losers by 24h change', []],
    ['GET', '/api/trending', 'Top 20 coins by 24h volume', []],
];
@endphp

<div class="space-y-6">
@foreach ($endpoints as [$method, $path, $desc, $params])
<div class="rounded-xl border border-slate-800 bg-slate-900/60 overflow-hidden">
    <div class="flex items-center gap-3 border-b border-slate-800 px-5 py-4">
        <span class="rounded px-2 py-0.5 text-xs font-bold bg-emerald-900/50 text-emerald-400">{{ $method }}</span>
        <code class="text-sm font-mono text-blue-300">{{ $path }}</code>
        <span class="ml-2 text-sm text-slate-400">{{ $desc }}</span>
        <a href="{{ url($path) }}" target="_blank" rel="noopener"
           class="ml-auto text-xs text-slate-500 hover:text-blue-400 transition">Try it ↗</a>
    </div>
    @if (!empty($params))
    <div class="px-5 py-3">
        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Query Parameters</p>
        <div class="space-y-1">
            @foreach ($params as $param => $pdesc)
            <div class="flex items-start gap-3 text-sm">
                <code class="text-blue-300 shrink-0">{{ $param }}</code>
                <span class="text-slate-400">{{ $pdesc }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endforeach
</div>

<div class="mt-8 rounded-xl border border-slate-800 bg-slate-900/60 p-5">
    <h2 class="mb-3 font-semibold text-white">Example Response</h2>
    <pre class="overflow-x-auto rounded-lg bg-slate-950 p-4 text-xs text-slate-300"><code>{
  "data": [
    {
      "id": 1,
      "name": "Bitcoin",
      "symbol": "BTC",
      "slug": "bitcoin",
      "current_price": "107349.00",
      "market_cap": "2129876543210.00",
      "market_cap_rank": 1,
      "price_change_percentage_24h_in_currency": 2.45,
      ...
    }
  ],
  "meta": {
    "page": 1,
    "per_page": 50,
    "total": 250,
    "last_page": 5
  }
}</code></pre>
</div>

</div>
@endsection
