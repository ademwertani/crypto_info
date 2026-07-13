<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cryptocurrency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CoinController extends Controller
{
    private const CACHE_TTL = 60;

    public function index(Request $request): JsonResponse
    {
        $page    = max(1, (int) $request->get('page', 1));
        $perPage = min(100, max(10, (int) $request->get('per_page', 50)));

        $cacheKey = "api_coins_{$page}_{$perPage}";

        $result = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($perPage, $page) {
            $paginator = DB::table('cryptocurrencies')
                ->orderBy('market_cap_rank')
                ->paginate($perPage, ['*'], 'page', $page);

            return [
                'items' => $paginator->items(),
                'page'       => $paginator->currentPage(),
                'per_page'   => $paginator->perPage(),
                'total'      => $paginator->total(),
                'last_page'  => $paginator->lastPage(),
            ];
        });

        return response()->json([
            'data' => $result['items'],
            'meta' => [
                'page'       => $result['page'],
                'per_page'   => $result['per_page'],
                'total'      => $result['total'],
                'last_page'  => $result['last_page'],
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $coin = Cache::remember("api_coin_{$slug}", self::CACHE_TTL, fn () =>
            DB::table('cryptocurrencies')->where('slug', $slug)->first()
        );

        if (! $coin) {
            return response()->json(['error' => 'Coin not found'], 404);
        }

        return response()->json(['data' => $coin]);
    }

    public function gainers(): JsonResponse
    {
        $data = Cache::remember('api_gainers', self::CACHE_TTL, fn () =>
            DB::table('cryptocurrencies')
                ->whereNotNull('price_change_percentage_24h_in_currency')
                ->orderByDesc('price_change_percentage_24h_in_currency')
                ->limit(50)
                ->get()
        );

        return response()->json(['data' => $data]);
    }

    public function losers(): JsonResponse
    {
        $data = Cache::remember('api_losers', self::CACHE_TTL, fn () =>
            DB::table('cryptocurrencies')
                ->whereNotNull('price_change_percentage_24h_in_currency')
                ->orderBy('price_change_percentage_24h_in_currency')
                ->limit(50)
                ->get()
        );

        return response()->json(['data' => $data]);
    }

    public function trending(): JsonResponse
    {
        $data = Cache::remember('api_trending', self::CACHE_TTL, fn () =>
            DB::table('cryptocurrencies')
                ->whereNotNull('total_volume')
                ->orderByDesc('total_volume')
                ->limit(20)
                ->get()
        );

        return response()->json(['data' => $data]);
    }
}
