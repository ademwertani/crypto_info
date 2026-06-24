<?php

namespace App\Http\Controllers;

use App\Models\Cryptocurrency;
use App\Services\AiSummaryService;
use App\Services\GlobalMarketService;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class CryptoController extends Controller
{
    private const int PER_PAGE  = 100;
    private const int CACHE_TTL = 300;

    public function index(Request $request, GlobalMarketService $gms): View
    {
        $page   = max(1, (int) $request->query('page', 1));
        $search = (string) $request->query('search', '');

        if ($search !== '') {
            $cryptos = $this->paginateQuery($search, $page);
            $seo     = SeoService::forHome();
            $stats   = [];

            return view('crypto.index', compact('cryptos', 'search', 'seo', 'stats'));
        }

        $total = Cache::remember('crypto_total_count', self::CACHE_TTL, function () {
            return Cryptocurrency::count();
        });

        $rows = Cache::remember("crypto_page_{$page}_items", self::CACHE_TTL, function () use ($page) {
            return Cryptocurrency::orderBy('market_cap_rank')
                ->forPage($page, self::PER_PAGE)
                ->get()
                ->toArray();
        });

        $items   = collect($rows)->map(function (array $row) {
            return (new Cryptocurrency())->forceFill($row);
        });

        $cryptos = new LengthAwarePaginator(
            $items, $total, self::PER_PAGE, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $seo   = SeoService::forHome();
        $stats = ($page === 1) ? $gms->getGlobalStats() : [];

        return view('crypto.index', compact('cryptos', 'search', 'seo', 'stats'));
    }

    public function show(string $slug, AiSummaryService $ai): View
    {
        $row = Cache::remember("crypto_detail_{$slug}", self::CACHE_TTL, function () use ($slug) {
            return Cryptocurrency::where('slug', $slug)->firstOrFail()->toArray();
        });

        $crypto = (new Cryptocurrency())->forceFill($row);

        Cryptocurrency::where('slug', $slug)->increment('views_count');

        $aiExplanation = Cache::remember("ai_why_{$slug}", 1800, function () use ($crypto, $ai) {
            $change = (float) $crypto->price_change_percentage_24h_in_currency;
            if (abs($change) < 2) { return null; }
            return $ai->whyPriceMoved($crypto->name, $change);
        });

        $seo = SeoService::forCoin($crypto);

        return view('crypto.show', compact('crypto', 'seo', 'aiExplanation'));
    }

    private function paginateQuery(string $search, int $page): LengthAwarePaginator
    {
        return Cryptocurrency::query()
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('symbol', 'like', "%{$search}%");
            })
            ->orderBy('market_cap_rank')
            ->paginate(self::PER_PAGE, ['*'], 'page', $page)
            ->withQueryString();
    }
}
