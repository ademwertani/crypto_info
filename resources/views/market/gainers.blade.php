@extends('layouts.app')
@section('title', 'Top Crypto Gainers Today')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-white">Top Gainers Today</h1>
    <p class="mt-1 text-sm text-slate-400">Coins with the highest 24h price increase</p>
</div>

@include('partials._coin-table', ['coins' => $coins, 'highlightCol' => '24h'])

@endsection

@push('scripts')
<script>window.CryptoInfoAnalytics?.trackMoneyPageView({ page_type: 'gainers' });</script>
@endpush
