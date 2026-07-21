@extends('layouts.app')
@section('content')

<div class="max-w-4xl mx-auto">

    <nav class="text-xs text-slate-500 mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center gap-1.5">
            <li><a href="{{ route('crypto.index') }}" class="hover:text-white transition">Home</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-slate-300">{{ __('advertise.title') }}</li>
        </ol>
    </nav>

    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-white mb-2">{{ __('advertise.title') }}</h1>
        <p class="text-slate-400">{{ __('advertise.subtitle') }}</p>
    </div>

    @if (session('advertise_sent'))
        <div class="mb-8 rounded-lg border border-emerald-500/30 bg-emerald-950/20 px-4 py-3 text-sm text-emerald-300" role="status">
            {{ __('advertise.success_message') }}
        </div>
    @endif

    {{-- Ad formats --}}
    <div class="mb-10">
        <h2 class="text-lg font-bold text-white mb-4">{{ __('advertise.formats_title') }}</h2>
        <div class="grid sm:grid-cols-3 gap-4">
            @forelse ($formats as $format)
                <div class="glass rounded-2xl p-6 flex flex-col">
                    <h3 class="font-bold text-white mb-2">{{ $format->name }}</h3>
                    <p class="text-sm text-slate-400 leading-relaxed mb-4 flex-1">{{ $format->description }}</p>

                    @if (! empty($format->specs))
                        <ul class="space-y-1.5 text-xs text-slate-500 mb-4">
                            @foreach ($format->specs as $spec)
                                <li class="flex gap-1.5"><span class="text-blue-400">•</span> {{ $spec }}</li>
                            @endforeach
                        </ul>
                    @endif

                    <div class="mt-auto pt-3 border-t border-slate-800">
                        <span class="inline-block rounded-full bg-blue-500/10 border border-blue-500/20 px-3 py-1 text-xs font-bold text-blue-400">
                            {{ $format->price_range }}
                        </span>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500 col-span-3">{{ __('advertise.no_formats') }}</p>
            @endforelse
        </div>
    </div>

    {{-- Contact form --}}
    <div class="glass rounded-2xl p-6 sm:p-8">
        <h2 class="text-lg font-bold text-white mb-5">{{ __('advertise.form_title') }}</h2>

        <form method="POST" action="{{ route('advertise.store') }}" class="space-y-4" novalidate>
            @csrf

            {{-- Honeypot: invisible to real visitors, never focusable, never announced by screen readers. --}}
            <div class="absolute -left-[9999px] top-0 opacity-0 pointer-events-none" aria-hidden="true">
                <label for="hp_website">Leave this field empty</label>
                <input type="text" id="hp_website" name="hp_website" tabindex="-1" autocomplete="off">
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="advertise-name" class="block text-xs font-medium text-slate-400 mb-1.5">{{ __('advertise.name_label') }}</label>
                    <input type="text" id="advertise-name" name="name" required maxlength="150" value="{{ old('name') }}"
                           class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-2 text-sm text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500">
                    @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="advertise-email" class="block text-xs font-medium text-slate-400 mb-1.5">{{ __('advertise.email_label') }}</label>
                    <input type="email" id="advertise-email" name="email" required maxlength="255" value="{{ old('email') }}"
                           class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-2 text-sm text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500">
                    @error('email')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="advertise-company" class="block text-xs font-medium text-slate-400 mb-1.5">{{ __('advertise.company_label') }}</label>
                    <input type="text" id="advertise-company" name="company" maxlength="150" value="{{ old('company') }}"
                           class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-2 text-sm text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500">
                    @error('company')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="advertise-budget" class="block text-xs font-medium text-slate-400 mb-1.5">{{ __('advertise.budget_label') }}</label>
                    <select id="advertise-budget" name="budget_range"
                            class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-2 text-sm text-slate-100 outline-none focus:border-blue-500">
                        <option value="">{{ __('advertise.budget_placeholder') }}</option>
                        @foreach ($budgetRanges as $range)
                            <option value="{{ $range }}" @selected(old('budget_range') === $range)>{{ $range }}</option>
                        @endforeach
                    </select>
                    @error('budget_range')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="advertise-message" class="block text-xs font-medium text-slate-400 mb-1.5">{{ __('advertise.message_label') }}</label>
                <textarea id="advertise-message" name="message" required minlength="20" maxlength="5000" rows="5"
                          class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-2 text-sm text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500">{{ old('message') }}</textarea>
                @error('message')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <p class="text-[11px] text-slate-500 leading-relaxed">
                {{ __('advertise.gdpr_notice') }}
                <a href="{{ route('pages.privacy') }}" class="text-blue-400 hover:underline">{{ __('advertise.gdpr_privacy_link') }}</a>.
            </p>

            <button type="submit"
                    class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-blue-500 transition">
                {{ __('advertise.submit_btn') }}
            </button>
        </form>
    </div>
</div>

@endsection
