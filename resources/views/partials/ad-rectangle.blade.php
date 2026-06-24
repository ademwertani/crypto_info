{{--
    Phase 10 — Ad: medium rectangle (300×250) or inline responsive unit.
    @param string $position  Identifier for logging/targeting (e.g. 'market-top')
    Replace the placeholder div with a real AdSense <ins> tag.
--}}
@if(config('ads.enabled'))
<div class="mb-4 flex justify-center">
    {{-- AdSense: <ins class="adsbygoogle" data-ad-slot="..." ...></ins> --}}
    <div class="h-[90px] w-full max-w-3xl bg-slate-800/40 rounded flex items-center justify-center text-xs text-slate-600 border border-slate-800">
        Ad · {{ $position ?? 'inline' }}
    </div>
</div>
@endif
