{{--
    Phase 10 — Ad: horizontal leaderboard banner (728×90 or responsive).
    Replace the comment below with a real AdSense ad unit tag.
--}}
@if(config('ads.enabled'))
<div class="flex justify-center border-b border-slate-800 bg-slate-900/40 py-1.5">
    {{-- AdSense: <ins class="adsbygoogle" ...></ins><script>...</script> --}}
    <div class="h-[50px] w-[728px] bg-slate-800/60 rounded flex items-center justify-center text-xs text-slate-600">
        Ad · 728×90
    </div>
</div>
@endif
