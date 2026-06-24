@extends('layouts.app')
@section('title', 'Top Crypto Losers Today')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-white">Top Losers Today</h1>
    <p class="mt-1 text-sm text-slate-400">Coins with the biggest 24h price drop</p>
</div>

@include('partials._coin-table', ['coins' => $coins, 'highlightCol' => '24h'])

@endsection
