{{-- Reusable coin table for gainers / losers / trending --}}
<div class="overflow-x-auto rounded-xl border border-slate-800">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-800 bg-slate-900/60 text-xs uppercase tracking-wider text-slate-500">
                <th class="px-4 py-3 text-right w-12">#</th>
                <th class="px-4 py-3 text-left">Name</th>
                <th class="px-4 py-3 text-right">Price</th>
                <th class="px-4 py-3 text-right {{ ($highlightCol ?? '') === '24h' ? 'text-emerald-400' : '' }}">24h %</th>
                <th class="px-4 py-3 text-right">7d %</th>
                <th class="px-4 py-3 text-right {{ ($highlightCol ?? '') === 'volume' ? 'text-emerald-400' : '' }}">Volume (24h)</th>
                <th class="px-4 py-3 text-right">Market Cap</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-800/60">
            @forelse ($coins as $coin)
            <tr class="hover:bg-slate-800/40 transition-colors cursor-pointer"
                onclick="window.location='{{ route('crypto.show', $coin->slug) }}'">

                <td class="px-4 py-3.5 text-right text-slate-500 font-mono text-xs">{{ $coin->market_cap_rank ?? '—' }}</td>

                <td class="px-4 py-3.5">
                    <a href="{{ route('crypto.show', $coin->slug) }}" class="flex items-center gap-3" onclick="event.stopPropagation()">
                        @if ($coin->image_url)
                            <img src="{{ $coin->image_url }}" alt="{{ e($coin->name) }}" class="h-7 w-7 rounded-full shrink-0" loading="lazy">
                        @endif
                        <div>
                            <p class="font-semibold text-white">{{ e($coin->name) }}</p>
                            <p class="text-xs text-slate-500 uppercase">{{ e($coin->symbol) }}</p>
                        </div>
                    </a>
                </td>

                <td class="px-4 py-3.5 text-right font-medium tabular-nums text-white">
                    {{ $coin->formattedPrice() }}
                </td>

                <td class="px-4 py-3.5 text-right tabular-nums">
                    @if ($coin->price_change_percentage_24h_in_currency !== null)
                        <x-percent-badge :value="$coin->price_change_percentage_24h_in_currency" />
                    @else <span class="text-slate-600">—</span> @endif
                </td>

                <td class="px-4 py-3.5 text-right tabular-nums">
                    @if ($coin->price_change_percentage_7d_in_currency !== null)
                        <x-percent-badge :value="$coin->price_change_percentage_7d_in_currency" />
                    @else <span class="text-slate-600">—</span> @endif
                </td>

                <td class="px-4 py-3.5 text-right tabular-nums text-slate-300">
                    @if ($coin->total_volume) ${{ number_format((float)$coin->total_volume/1e9,2) }}B
                    @else <span class="text-slate-600">—</span> @endif
                </td>

                <td class="px-4 py-3.5 text-right tabular-nums text-slate-300">
                    @if ($coin->market_cap) ${{ number_format((float)$coin->market_cap/1e9,2) }}B
                    @else <span class="text-slate-600">—</span> @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="py-16 text-center text-slate-500">No data available.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
