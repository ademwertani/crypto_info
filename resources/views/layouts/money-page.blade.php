{{--
    Thin layout for every "money page" (guides, exchange reviews, how-to,
    comparisons...). Wraps layouts.app and guarantees the "not financial
    advice" disclaimer always renders, regardless of what any given guide
    template does — so mass-produced content can never accidentally ship
    without it.
--}}
@extends('layouts.app')

@section('content')
    <div class="mb-6">
        @include('partials.content-disclaimer')
    </div>

    @yield('money-page-content')
@endsection
