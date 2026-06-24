@extends('layouts.app')
@section('title', 'My Watchlist')
@section('content')

<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-white">My Watchlist</h1>
</div>

@if (session('status'))
    <div class="mb-4 rounded-lg border border-emerald-700 bg-emerald-900/30 px-4 py-3 text-sm text-emerald-400">
        {{ session('status') }}
    </div>
@endif

@if ($coins->isEmpty())
    <div class="flex flex-col items-center justify-center py-20 text-slate-500 gap-3">
        <p>Your watchlist is empty.</p>
        <a href="{{ route('crypto.index') }}" class="text-blue-400 hover:underline text-sm">Browse coins →</a>
    </div>
@else
    <div class="mb-8 overflow-x-auto rounded-xl border border-slate-800">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 bg-slate-900/60 text-xs uppercase tracking-wider text-slate-500">
                    <th class="px-4 py-3 text-left">Coin</th>
                    <th class="px-4 py-3 text-right">Price</th>
                    <th class="px-4 py-3 text-right">24h %</th>
                    <th class="px-4 py-3 text-right">Market Cap</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @foreach ($coins as $coin)
                <tr class="hover:bg-slate-800/40 transition-colors">
                    <td class="px-4 py-3.5">
                        <a href="{{ route('crypto.show', $coin->slug) }}" class="flex items-center gap-3">
                            @if ($coin->image_url)
                                <img src="{{ $coin->image_url }}" alt="{{ e($coin->name) }}" class="h-7 w-7 rounded-full" loading="lazy">
                            @endif
                            <div>
                                <p class="font-semibold text-white">{{ e($coin->name) }}</p>
                                <p class="text-xs text-slate-500 uppercase">{{ e($coin->symbol) }}</p>
                            </div>
                        </a>
                    </td>
                    <td class="px-4 py-3.5 text-right font-medium text-white tabular-nums">{{ $coin->formattedPrice() }}</td>
                    <td class="px-4 py-3.5 text-right tabular-nums">
                        @if ($coin->price_change_percentage_24h_in_currency !== null)
                            <x-percent-badge :value="$coin->price_change_percentage_24h_in_currency" />
                        @else <span class="text-slate-600">—</span> @endif
                    </td>
                    <td class="px-4 py-3.5 text-right tabular-nums text-slate-300">
                        @if ($coin->market_cap) ${{ number_format((float)$coin->market_cap/1e9,2) }}B
                        @else <span class="text-slate-600">—</span> @endif
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <form method="POST" action="{{ route('watchlist.toggle') }}" class="inline">
                            @csrf
                            <input type="hidden" name="slug" value="{{ $coin->slug }}">
                            <button class="text-xs text-red-400 hover:text-red-300 transition">Remove</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

{{-- Price Alerts --}}
@if ($alerts->isNotEmpty())
<div>
    <h2 class="mb-4 text-lg font-semibold text-white">Active Alerts</h2>
    <div class="space-y-2">
        @foreach ($alerts as $alert)
        <div class="flex items-center justify-between rounded-lg border border-slate-800 bg-slate-900/60 px-4 py-3">
            <div class="flex items-center gap-3">
                @if ($alert->cryptocurrency->image_url)
                    <img src="{{ $alert->cryptocurrency->image_url }}" class="h-6 w-6 rounded-full" alt="{{ $alert->cryptocurrency->name }}" loading="lazy">
                @endif
                <div>
                    <p class="text-sm font-medium text-white">{{ $alert->cryptocurrency->name }}</p>
                    <p class="text-xs text-slate-500">
                        Price {{ $alert->direction }} ${{ number_format((float)$alert->target_price, 2) }}
                    </p>
                </div>
            </div>
            <form method="POST" action="{{ route('watchlist.alert.destroy', $alert) }}">
                @csrf @method('DELETE')
                <button class="text-xs text-red-400 hover:text-red-300 transition">Delete</button>
            </form>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection
