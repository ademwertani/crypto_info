{{--
    Reusable affiliate/sponsored link. Always carries rel="sponsored noopener"
    (required by Google — an affiliate link without rel="sponsored" is a risk
    for manual action / link-scheme penalties) and the data-affiliate-*
    attributes read by the click tracker in resources/js/app.js to fire the
    GA4 "affiliate_click" event.
--}}
@props([
    'href',
    'network',
    'placement',
    'coin' => null,
])

<a
    href="{{ $href }}"
    target="_blank"
    rel="sponsored noopener"
    data-affiliate-network="{{ $network }}"
    data-affiliate-placement="{{ $placement }}"
    @if($coin) data-affiliate-coin="{{ $coin }}" @endif
    {{ $attributes }}
>{{ $slot }}</a>
