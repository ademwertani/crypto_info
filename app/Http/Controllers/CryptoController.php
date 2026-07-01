<?php

namespace App\Http\Controllers;

use App\Models\Cryptocurrency;
use App\Services\AiSummaryService;
use App\Services\GlobalMarketService;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CryptoController extends Controller
{
    private const PER_PAGE  = 100;
    private const CACHE_TTL = 300;

    public function index(Request $request, GlobalMarketService $gms): View
    {
        $page   = max(1, (int) $request->query('page', 1));
        $search = (string) $request->query('search', '');

        if (! Schema::hasTable('cryptocurrencies')) {
            $cryptos = new LengthAwarePaginator(
                collect(),
                0,
                self::PER_PAGE,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            return view('crypto.index', [
                'cryptos' => $cryptos,
                'search' => $search,
                'seo' => SeoService::forHome(),
                'stats' => [],
            ]);
        }

        if ($search !== '') {
            $cryptos = $this->paginateQuery($search, $page);
            $seo     = SeoService::forHome();
            $stats   = [];

            return view('crypto.index', compact('cryptos', 'search', 'seo', 'stats'));
        }

        $total = Cache::remember('crypto_total_count', self::CACHE_TTL, function () {
            return (int) DB::table('cryptocurrencies')->count();
        });

        $rows = Cache::remember("crypto_page_{$page}_items", self::CACHE_TTL, function () use ($page) {
            return DB::table('cryptocurrencies')
                ->select([
                    'id',
                    'name',
                    'symbol',
                    'slug',
                    'image_url',
                    'current_price',
                    'market_cap',
                    'market_cap_rank',
                    'total_volume',
                    'price_change_percentage_1h_in_currency',
                    'price_change_percentage_24h_in_currency',
                    'price_change_percentage_7d_in_currency',
                    'sparkline_7d',
                ])
                ->orderBy('market_cap_rank')
                ->orderBy('id')
                ->forPage($page, self::PER_PAGE)
                ->get()
                ->map(fn ($item) => (array) $item)
                ->all();
        });

        $items   = collect($rows)->map(function (array|object $row) {
            return (new Cryptocurrency())->forceFill((array) $row);
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
        if (! Schema::hasTable('cryptocurrencies')) {
            abort(404);
        }

        $row = Cache::remember("crypto_detail_{$slug}", self::CACHE_TTL, function () use ($slug) {
            $coin = DB::table('cryptocurrencies')
                ->select([
                    'id',
                    'name',
                    'symbol',
                    'slug',
                    'image_url',
                    'current_price',
                    'market_cap',
                    'market_cap_rank',
                    'fully_diluted_valuation',
                    'total_volume',
                    'high_24h',
                    'low_24h',
                    'price_change_percentage_1h_in_currency',
                    'price_change_percentage_24h_in_currency',
                    'price_change_percentage_7d_in_currency',
                    'circulating_supply',
                    'total_supply',
                    'max_supply',
                    'ath',
                    'ath_change_percentage',
                    'ath_date',
                    'atl',
                    'atl_change_percentage',
                    'atl_date',
                    'description',
                    'sparkline_7d',
                    'views_count',
                ])
                ->where('slug', $slug)
                ->first();

            if (! $coin) {
                abort(404);
            }

            return (array) $coin;
        });

        $crypto = (new Cryptocurrency())->forceFill($row);

        DB::table('cryptocurrencies')->where('slug', $slug)->increment('views_count');

        $aiExplanation = Cache::remember("ai_why_{$slug}", 1800, function () use ($crypto, $ai) {
            $change = (float) ($crypto->price_change_percentage_24h_in_currency ?? 0);
            if (abs($change) < 2) { return null; }
            return $ai->whyPriceMoved($crypto->name, $change);
        });

        $seo = SeoService::forCoin($crypto);

        return view('crypto.show', compact('crypto', 'seo', 'aiExplanation'));
    }

    private function paginateQuery(string $search, int $page): LengthAwarePaginator
    {
        return Cryptocurrency::query()
            ->select([
                'id',
                'name',
                'symbol',
                'slug',
                'image_url',
                'current_price',
                'market_cap',
                'market_cap_rank',
                'total_volume',
                'price_change_percentage_1h_in_currency',
                'price_change_percentage_24h_in_currency',
                'price_change_percentage_7d_in_currency',
                'sparkline_7d',
            ])
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('symbol', 'like', "%{$search}%");
            })
            ->orderBy('market_cap_rank')
            ->orderBy('id')
            ->paginate(self::PER_PAGE, ['*'], 'page', $page)
            ->withQueryString();
    }
}
