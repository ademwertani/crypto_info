@extends('layouts.app')
@section('content')

<div class="max-w-2xl mx-auto py-8">

    <div class="text-center mb-8 animate-fade-in">
        <h1 class="text-3xl font-extrabold text-white mb-2">⚖️ {{ __('compare.choose_title') }}</h1>
        <p class="text-slate-400 text-sm">{{ __('compare.choose_subtitle') }}</p>
    </div>

    <div class="glass rounded-2xl p-6 animate-fade-in-delay-1"
         x-data="coinChooser({{ json_encode($allCoins->map(fn($c) => ['name' => $c->name, 'slug' => $c->slug, 'symbol' => strtoupper($c->symbol ?? ''), 'image' => $c->image_url])->values()) }})">

        <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto_1fr] gap-4 items-start mb-5">

            {{-- Coin A --}}
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wide">{{ __('compare.coin_a') }}</label>
                <div class="relative">
                    <input type="text" x-model="searchA" @focus="openA = true" @click.outside="openA = false"
                           @keydown.escape="openA = false"
                           placeholder="{{ __('compare.search_placeholder') }}"
                           class="w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-3 text-sm text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/40 transition-all">
                    <div x-show="openA && filteredA.length > 0" x-cloak
                         class="absolute z-50 mt-1 w-full rounded-xl border border-slate-700 bg-slate-900 shadow-xl chooser-dropdown">
                        <template x-for="coin in filteredA" :key="coin.slug">
                            <button type="button" @click="selectA(coin)"
                                    class="flex items-center gap-2.5 w-full px-3 py-2.5 text-left text-sm hover:bg-slate-800 transition"
                                    :class="selectedA?.slug === coin.slug ? 'bg-blue-950/40 text-blue-400' : 'text-slate-300'">
                                <img :src="coin.image" :alt="coin.name" class="h-6 w-6 rounded-full shrink-0" loading="lazy"
                                     onerror="this.style.display='none'">
                                <span x-text="coin.name" class="font-medium"></span>
                                <span x-text="coin.symbol" class="text-[11px] text-slate-500 uppercase ml-1"></span>
                            </button>
                        </template>
                    </div>
                </div>
                <div x-show="selectedA" x-cloak class="mt-2 flex items-center gap-2 text-sm text-slate-300">
                    <img :src="selectedA?.image" class="h-5 w-5 rounded-full" onerror="this.style.display='none'">
                    <span x-text="selectedA?.name" class="font-medium text-white"></span>
                    <span x-text="selectedA?.symbol" class="text-xs text-slate-500 uppercase"></span>
                </div>
            </div>

            {{-- VS divider --}}
            <div class="flex items-center justify-center pt-7">
                <span class="text-2xl font-bold text-slate-600">vs</span>
            </div>

            {{-- Coin B --}}
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wide">{{ __('compare.coin_b') }}</label>
                <div class="relative">
                    <input type="text" x-model="searchB" @focus="openB = true" @click.outside="openB = false"
                           @keydown.escape="openB = false"
                           placeholder="{{ __('compare.search_placeholder') }}"
                           class="w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-3 text-sm text-slate-100 placeholder-slate-500 outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500/40 transition-all">
                    <div x-show="openB && filteredB.length > 0" x-cloak
                         class="absolute z-50 mt-1 w-full rounded-xl border border-slate-700 bg-slate-900 shadow-xl chooser-dropdown">
                        <template x-for="coin in filteredB" :key="coin.slug">
                            <button type="button" @click="selectB(coin)"
                                    class="flex items-center gap-2.5 w-full px-3 py-2.5 text-left text-sm hover:bg-slate-800 transition"
                                    :class="selectedB?.slug === coin.slug ? 'bg-purple-950/40 text-purple-400' : 'text-slate-300'">
                                <img :src="coin.image" :alt="coin.name" class="h-6 w-6 rounded-full shrink-0" loading="lazy"
                                     onerror="this.style.display='none'">
                                <span x-text="coin.name" class="font-medium"></span>
                                <span x-text="coin.symbol" class="text-[11px] text-slate-500 uppercase ml-1"></span>
                            </button>
                        </template>
                    </div>
                </div>
                <div x-show="selectedB" x-cloak class="mt-2 flex items-center gap-2 text-sm text-slate-300">
                    <img :src="selectedB?.image" class="h-5 w-5 rounded-full" onerror="this.style.display='none'">
                    <span x-text="selectedB?.name" class="font-medium text-white"></span>
                    <span x-text="selectedB?.symbol" class="text-xs text-slate-500 uppercase"></span>
                </div>
            </div>
        </div>

        <button @click="go()"
                :disabled="!selectedA || !selectedB || selectedA?.slug === selectedB?.slug"
                :class="(!selectedA || !selectedB || selectedA?.slug === selectedB?.slug) ? 'opacity-40 cursor-not-allowed' : 'hover:bg-blue-500 shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30'"
                class="w-full rounded-xl bg-blue-600 py-3.5 text-sm font-bold text-white transition-all">
            {{ __('compare.go') }} →
        </button>

        <p x-show="selectedA && selectedB && selectedA?.slug === selectedB?.slug" x-cloak
           class="text-center text-xs text-red-400 mt-2">
            {{ __('compare.same_coin_error') }}
        </p>
    </div>

    {{-- Quick suggestions --}}
    <div class="mt-8 animate-fade-in-delay-2">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">{{ __('compare.try_other') }}</p>
        <div class="flex flex-wrap gap-2">
            @foreach([['bitcoin','ethereum'],['bitcoin','solana'],['ethereum','solana'],['ripple','cardano'],['dogecoin','shiba-inu'],['bitcoin','bitcoin-cash']] as [$sA,$sB])
            <a href="{{ route('crypto.compare', ['slugA'=>$sA,'slugB'=>$sB]) }}"
               class="rounded-full border border-slate-700 px-3 py-1.5 text-xs text-slate-400 hover:border-blue-600/50 hover:text-blue-400 transition capitalize">
                {{ str_replace('-', ' ', $sA) }} vs {{ str_replace('-', ' ', $sB) }}
            </a>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
function coinChooser(allCoins) {
    return {
        allCoins,
        selectedA: null, searchA: '', openA: false,
        selectedB: null, searchB: '', openB: false,

        get filteredA() {
            const q = this.searchA.toLowerCase();
            if (!q) return this.allCoins.slice(0, 30);
            return this.allCoins.filter(c =>
                c.name.toLowerCase().includes(q) || c.symbol.toLowerCase().includes(q)
            ).slice(0, 20);
        },
        get filteredB() {
            const q = this.searchB.toLowerCase();
            if (!q) return this.allCoins.slice(0, 30);
            return this.allCoins.filter(c =>
                c.name.toLowerCase().includes(q) || c.symbol.toLowerCase().includes(q)
            ).slice(0, 20);
        },

        selectA(coin) { this.selectedA = coin; this.searchA = coin.name; this.openA = false; },
        selectB(coin) { this.selectedB = coin; this.searchB = coin.name; this.openB = false; },

        go() {
            if (!this.selectedA || !this.selectedB) return;
            if (this.selectedA.slug === this.selectedB.slug) return;
            window.location = `/compare/${this.selectedA.slug}-vs-${this.selectedB.slug}`;
        },
    };
}
</script>
@endpush
@endsection
