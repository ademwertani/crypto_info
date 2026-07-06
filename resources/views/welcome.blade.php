@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-2xl text-center py-16">
        <h1 class="text-4xl font-black text-white mb-4">CryptoInfo</h1>
        <p class="text-slate-400 mb-6">Live cryptocurrency prices, market analytics and comparison tools.</p>
        <a href="{{ route('crypto.index') }}" class="inline-flex rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500 transition">
            Open Market
        </a>
    </div>
@endsection
