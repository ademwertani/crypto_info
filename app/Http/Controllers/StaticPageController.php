<?php

namespace App\Http\Controllers;

use App\Services\SeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class StaticPageController extends Controller
{
    public function methodology(): View
    {
        $seo = new SeoService();
        $seo->title       = 'Our Data Methodology — How We Verify Crypto Prices | CryptoInfo';
        $seo->description = 'Learn how CryptoInfo sources, validates and updates cryptocurrency prices every 10 minutes from CoinGecko and real-time WebSocket feeds.';
        $seo->canonical   = route('pages.methodology');

        return view('pages.methodology', compact('seo'));
    }

    public function about(): View
    {
        $seo = new SeoService();
        $seo->title       = 'About CryptoInfo — Real-Time Cryptocurrency Intelligence';
        $seo->description = 'CryptoInfo provides real-time cryptocurrency market data, prices, and analytics for 250+ coins. Our mission: transparent, accurate market intelligence.';
        $seo->canonical   = route('pages.about');

        return view('pages.about', compact('seo'));
    }

    public function privacy(): View
    {
        $seo = new SeoService();
        $seo->title       = 'Privacy Policy | CryptoInfo';
        $seo->description = 'CryptoInfo privacy policy: how we collect, use and protect your data.';
        $seo->canonical   = route('pages.privacy');

        return view('pages.privacy', compact('seo'));
    }

    public function terms(): View
    {
        $seo = new SeoService();
        $seo->title       = 'Terms of Service | CryptoInfo';
        $seo->description = 'Terms and conditions for using CryptoInfo cryptocurrency market data platform.';
        $seo->canonical   = route('pages.terms');

        return view('pages.terms', compact('seo'));
    }

    public function cookiePolicy(): View
    {
        $seo = new SeoService();
        $seo->title       = 'Cookie Policy | CryptoInfo';
        $seo->description = 'CryptoInfo cookie policy: which cookies we use today and how future advertising cookies will be handled.';
        $seo->canonical   = route('pages.cookie-policy');

        return view('pages.cookie-policy', compact('seo'));
    }

    public function contact(): View
    {
        $seo = new SeoService();
        $seo->title       = 'Contact Us | CryptoInfo';
        $seo->description = 'Get in touch with the CryptoInfo team for data inquiries, partnerships, or support.';
        $seo->canonical   = route('pages.contact');

        return view('pages.contact', compact('seo'));
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:120'],
            'email'   => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:100'],
            'message' => ['required', 'string', 'min:20', 'max:2000'],
        ]);

        // Log the message (replace with Mail::to() when SMTP is configured)
        \Illuminate\Support\Facades\Log::info('Contact form submission', $data);

        return redirect()->route('pages.contact')->with('contact_sent', true);
    }
}
