<?php

namespace App\Http\Controllers;

use App\Models\Cryptocurrency;
use App\Services\SeoService;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class CompareController extends Controller
{
    public function show(string $slugA, string $slugB): View
    {
        $rowA = Cache::remember("crypto_detail_{$slugA}", 300, function () use ($slugA) {
            return Cryptocurrency::where('slug', $slugA)->firstOrFail()->toArray();
        });

        $rowB = Cache::remember("crypto_detail_{$slugB}", 300, function () use ($slugB) {
            return Cryptocurrency::where('slug', $slugB)->firstOrFail()->toArray();
        });

        $coinA = (new Cryptocurrency())->forceFill($rowA);
        $coinB = (new Cryptocurrency())->forceFill($rowB);

        $seo = new SeoService();
        $seo->title       = "{$coinA->name} vs {$coinB->name} — Price & Market Comparison | CryptoInfo";
        $seo->description = "Compare {$coinA->name} and {$coinB->name}: price, market cap, volume, ATH, supply and 24h performance side-by-side.";
        $seo->canonical   = route('crypto.compare', ['slugA' => $slugA, 'slugB' => $slugB]);

        return view('market.compare', compact('coinA', 'coinB', 'seo'));
    }
}
