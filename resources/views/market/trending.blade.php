@extends('layouts.app')
@section('title', 'Trending Cryptocurrencies')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-white">Trending by Volume</h1>
    <p class="mt-1 text-sm text-slate-400">Most traded coins in the last 24 hours</p>
</div>

@include('partials._coin-table', ['coins' => $coins, 'highlightCol' => 'volume'])

@endsection
