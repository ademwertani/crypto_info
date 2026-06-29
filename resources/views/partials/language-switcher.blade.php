@php
$current = app()->getLocale();
$langs = [
    'en' => ['flag' => '🇬🇧', 'label' => 'English',   'short' => 'EN'],
    'fr' => ['flag' => '🇫🇷', 'label' => 'Français',  'short' => 'FR'],
    'ar' => ['flag' => '🇸🇦', 'label' => 'العربية',   'short' => 'AR'],
    'es' => ['flag' => '🇪🇸', 'label' => 'Español',   'short' => 'ES'],
    'de' => ['flag' => '🇩🇪', 'label' => 'Deutsch',   'short' => 'DE'],
    'pt' => ['flag' => '🇧🇷', 'label' => 'Português', 'short' => 'PT'],
];
@endphp

<div class="relative" x-data="{ open: false }" x-on:keydown.escape="open = false">
    {{-- Trigger button --}}
    <button
        @click="open = !open"
        @click.outside="open = false"
        class="flex items-center gap-2 rounded-lg border border-slate-700/60 bg-slate-800/60 px-2.5 py-1.5 text-xs font-semibold text-slate-200 shadow-sm shadow-black/20 hover:border-slate-500 hover:text-white transition-all"
        :aria-expanded="open"
        aria-haspopup="listbox"
        aria-label="{{ __('lang.label') }}">
        <span class="text-base leading-none">{{ $langs[$current]['flag'] }}</span>
        <span class="hidden sm:inline">{{ $langs[$current]['short'] }}</span>
        <svg class="h-3 w-3 text-slate-500 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Dropdown --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
        class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} top-full mt-1.5 z-[60] w-44 rounded-xl border border-slate-700/80 bg-slate-900 shadow-xl shadow-black/40 overflow-hidden"
        role="listbox"
        aria-label="{{ __('lang.label') }}">

        <div class="px-3 py-2 border-b border-slate-800">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500">{{ __('lang.label') }}</p>
        </div>

        <ul class="py-1">
            @foreach($langs as $code => $meta)
            <li role="option" aria-selected="{{ $current === $code ? 'true' : 'false' }}">
                <a href="{{ route('locale.switch', $code) }}"
                   class="flex items-center gap-2.5 px-3 py-2 text-sm transition-colors
                          {{ $current === $code
                             ? 'bg-blue-600/20 text-blue-400 font-semibold'
                             : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span class="text-base leading-none w-5 text-center">{{ $meta['flag'] }}</span>
                    <span>{{ $meta['label'] }}</span>
                    @if($current === $code)
                        <svg class="ml-auto h-3.5 w-3.5 text-blue-400 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                </a>
            </li>
            @endforeach
        </ul>
    </div>
</div>
