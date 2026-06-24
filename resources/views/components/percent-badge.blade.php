@props(['value'])

@php
    $num   = (float) $value;
    $color = $num >= 0 ? 'text-emerald-400' : 'text-red-400';
    $arrow = $num >= 0 ? '▲' : '▼';
    $label = number_format(abs($num), 2) . '%';
@endphp

<span class="inline-flex items-center gap-0.5 font-medium tabular-nums {{ $color }}">
    <span class="text-[0.6rem] leading-none">{{ $arrow }}</span>
    {{ $label }}
</span>
