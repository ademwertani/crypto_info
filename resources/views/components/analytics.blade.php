{{--
    Analytics bootstrap. Never loads GA4/Clarity itself — it only exposes the
    configured IDs on window.CryptoInfoConfig. resources/js/app.js decides
    whether to actually inject the GA4/Clarity <script> tags, based on the
    visitor's stored cookie consent (see Alpine.store('consent')).

    Renders nothing at all (not even this config object) when:
      - both IDs are unset, or
      - the app is running in the local environment.
    That second guard is what keeps local dev from ever hitting GA4/Clarity,
    independently of the consent banner.
--}}
@php
    $ga4Id    = config('services.ga4.id');
    $clarityId = config('services.clarity.id');
    $enabled  = ! app()->environment('local') && ($ga4Id || $clarityId);
@endphp
@if ($enabled)
<script>
    window.CryptoInfoConfig = {
        ga4Id: @json($ga4Id),
        clarityId: @json($clarityId),
    };
</script>
@endif
