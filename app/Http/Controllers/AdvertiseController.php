<?php

namespace App\Http\Controllers;

use App\Mail\AdvertiserInquiryAck;
use App\Mail\AdvertiserInquiryReceived;
use App\Models\AdFormat;
use App\Models\AdvertiserLead;
use App\Services\SeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdvertiseController extends Controller
{
    public function show(): View
    {
        $formats = AdFormat::query()->published()->orderBy('sort_order')->get();
        $seo = SeoService::forAdvertise();
        $budgetRanges = AdvertiserLead::BUDGET_RANGES;

        return view('advertise.show', compact('formats', 'seo', 'budgetRanges'));
    }

    public function store(Request $request): RedirectResponse
    {
        // Honeypot: real visitors never see or fill this field. A bot that
        // fills every input will fill it too — silently drop the
        // submission without hinting that anything was detected.
        if (filled($request->input('hp_website'))) {
            return redirect()->route('advertise.show')->with('advertise_sent', true);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'company' => ['nullable', 'string', 'max:150'],
            'budget_range' => ['nullable', 'string', Rule::in(AdvertiserLead::BUDGET_RANGES)],
            'message' => ['required', 'string', 'min:20', 'max:5000'],
        ]);

        $lead = AdvertiserLead::create([
            ...$validated,
            'ip' => $request->ip(),
        ]);

        Mail::to(config('services.advertise.contact_email'))->send(new AdvertiserInquiryReceived($lead));
        Mail::to($lead->email)->send(new AdvertiserInquiryAck($lead));

        return redirect()->route('advertise.show')->with('advertise_sent', true);
    }
}
