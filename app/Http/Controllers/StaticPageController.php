<?php

namespace App\Http\Controllers;

use App\Services\SeoService;
use Illuminate\View\View;

class StaticPageController extends Controller
{
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
}
