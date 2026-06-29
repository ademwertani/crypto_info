<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fetches global crypto market data:
 * - CoinGecko /global : total market cap, BTC/ETH dominance, volume
 * - Alternative.me Fear & Greed Index (free, no auth)
 * - Last sync timestamp for Market Status widget
 */
class GlobalMarketService
{
    private const CACHE_TTL = 600; // 10 minutes

    public function getGlobalStats(): array
    {
        $stats = Cache::remember('global_market_stats', self::CACHE_TTL, function () {
            $gecko = $this->fetchGeckoGlobal();
            $fng   = $this->fetchFearGreed();

            return array_merge($gecko, $fng, ['fetched_at' => now()->toISOString()]);
        });

        if (! isset($stats['fng_updated_at']) && isset($stats['fng_timestamp'])) {
            $stats['fng_updated_at'] = date('Y-m-d H:i:s', (int) $stats['fng_timestamp']);
            Cache::put('global_market_stats', $stats, self::CACHE_TTL);
        }

        return $stats;
    }

    public function getFearGreed(): array
    {
        $stats = Cache::remember('fear_greed_index', self::CACHE_TTL, fn () => $this->fetchFearGreed());

        if (! isset($stats['fng_updated_at']) && isset($stats['fng_timestamp'])) {
            $stats['fng_updated_at'] = date('Y-m-d H:i:s', (int) $stats['fng_timestamp']);
            Cache::put('fear_greed_index', $stats, self::CACHE_TTL);
        }

        return $stats;
    }

    private function fetchGeckoGlobal(): array
    {
        try {
            $r = Http::timeout(10)->get('https://api.coingecko.com/api/v3/global');
            if (! $r->ok()) { return $this->emptyGecko(); }
            $d = $r->json('data', []);

            $totalCap = array_sum($d['market_cap_percentage'] ?? []) > 0
                ? ($d['total_market_cap']['usd'] ?? 0)
                : 0;

            return [
                'total_market_cap'      => (float) ($d['total_market_cap']['usd'] ?? 0),
                'total_volume_24h'      => (float) ($d['total_volume']['usd'] ?? 0),
                'btc_dominance'         => round((float) ($d['market_cap_percentage']['btc'] ?? 0), 1),
                'eth_dominance'         => round((float) ($d['market_cap_percentage']['eth'] ?? 0), 1),
                'active_cryptocurrencies' => (int) ($d['active_cryptocurrencies'] ?? 250),
                'markets'               => (int) ($d['markets'] ?? 0),
                'market_cap_change_24h' => round((float) ($d['market_cap_change_percentage_24h_usd'] ?? 0), 2),
            ];
        } catch (\Throwable $e) {
            Log::warning('GlobalMarketService CoinGecko: ' . $e->getMessage());
            return $this->emptyGecko();
        }
    }

    private function fetchFearGreed(): array
    {
        try {
            $r = Http::timeout(8)->get('https://api.alternative.me/fng/?limit=1');
            if (! $r->ok()) { return $this->emptyFng(); }
            $item = $r->json('data.0', []);

            return [
                'fng_value'              => (int) ($item['value'] ?? 50),
                'fng_classification'     => $item['value_classification'] ?? 'Neutral',
                'fng_timestamp'          => $item['timestamp'] ?? null,
                'fng_updated_at'         => isset($item['timestamp']) ? date('Y-m-d H:i:s', (int) $item['timestamp']) : null,
            ];
        } catch (\Throwable $e) {
            Log::warning('GlobalMarketService F&G: ' . $e->getMessage());
            return $this->emptyFng();
        }
    }

    private function emptyGecko(): array
    {
        return [
            'total_market_cap'       => 0,
            'total_volume_24h'       => 0,
            'btc_dominance'          => 0,
            'eth_dominance'          => 0,
            'active_cryptocurrencies'=> 250,
            'markets'                => 0,
            'market_cap_change_24h'  => 0,
        ];
    }

    private function emptyFng(): array
    {
        return ['fng_value' => 50, 'fng_classification' => 'Neutral', 'fng_timestamp' => null];
    }

    /** Determine label color for F&G index */
    public static function fngColor(int $value): string
    {
        return match(true) {
            $value <= 24  => 'text-red-500',
            $value <= 44  => 'text-orange-400',
            $value <= 55  => 'text-yellow-400',
            $value <= 74  => 'text-emerald-400',
            default       => 'text-emerald-500',
        };
    }

    public static function fngLabel(int $value): string
    {
        return match(true) {
            $value <= 24  => 'Extreme Fear',
            $value <= 44  => 'Fear',
            $value <= 55  => 'Neutral',
            $value <= 74  => 'Greed',
            default       => 'Extreme Greed',
        };
    }
}
