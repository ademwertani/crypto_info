<?php

namespace App\Console\Commands;

use App\Services\CryptoApiService;
use App\Services\CryptoBroadcastService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

#[Signature('app:fetch-crypto-data')]
#[Description('Fetch top-250 cryptocurrency market data from CoinGecko and persist to DB')]
class FetchCryptoData extends Command
{
    public function handle(CryptoApiService $api, CryptoBroadcastService $broadcaster): int
    {
        $this->info('Fetching crypto market data from CoinGecko…');

        $coins = $api->fetchMarkets(page: 1, perPage: 250);

        if (empty($coins)) {
            $this->error('No data returned — check logs for API errors.');
            return self::FAILURE;
        }

        $this->info(sprintf('Received %d coins. Upserting into DB…', count($coins)));

        $now     = now();
        $records = [];

        foreach ($coins as $coin) {
            $records[] = [
                'name'                                    => $coin['name'] ?? '',
                'symbol'                                  => strtoupper($coin['symbol'] ?? ''),
                'slug'                                    => $coin['id'] ?? '',
                'image_url'                               => $coin['image'] ?? null,
                'current_price'                           => $coin['current_price'] ?? null,
                'market_cap'                              => $coin['market_cap'] ?? null,
                'market_cap_rank'                         => $coin['market_cap_rank'] ?? null,
                'fully_diluted_valuation'                 => $coin['fully_diluted_valuation'] ?? null,
                'total_volume'                            => $coin['total_volume'] ?? null,
                'high_24h'                                => $coin['high_24h'] ?? null,
                'low_24h'                                 => $coin['low_24h'] ?? null,
                'price_change_percentage_1h_in_currency'  => $coin['price_change_percentage_1h_in_currency'] ?? null,
                'price_change_percentage_24h_in_currency' => $coin['price_change_percentage_24h_in_currency'] ?? null,
                'price_change_percentage_7d_in_currency'  => $coin['price_change_percentage_7d_in_currency'] ?? null,
                'circulating_supply'                      => $coin['circulating_supply'] ?? null,
                'total_supply'                            => $coin['total_supply'] ?? null,
                'max_supply'                              => $coin['max_supply'] ?? null,
                'ath'                                     => $coin['ath'] ?? null,
                'ath_change_percentage'                   => $coin['ath_change_percentage'] ?? null,
                'ath_date'                                => isset($coin['ath_date'])
                                                              ? date('Y-m-d H:i:s', strtotime($coin['ath_date']))
                                                              : null,
                'atl'                                     => $coin['atl'] ?? null,
                'atl_change_percentage'                   => $coin['atl_change_percentage'] ?? null,
                'atl_date'                                => isset($coin['atl_date'])
                                                              ? date('Y-m-d H:i:s', strtotime($coin['atl_date']))
                                                              : null,
                'sparkline_7d'                            => isset($coin['sparkline_in_7d']['price'])
                                                              ? json_encode(
                                                                  array_values(
                                                                      array_filter(
                                                                          array_map('floatval', $coin['sparkline_in_7d']['price']),
                                                                          'is_finite'
                                                                      )
                                                                  )
                                                                )
                                                              : null,
                'created_at'                              => $now,
                'updated_at'                              => $now,
            ];
        }

        foreach (array_chunk($records, 50) as $chunk) {
            DB::table('cryptocurrencies')->upsert(
                $chunk,
                ['slug'],
                array_keys($chunk[0])
            );
        }

        // Bust page caches
        Cache::forget('crypto_total_count');
        for ($p = 1; $p <= 5; $p++) {
            Cache::forget("crypto_page_{$p}_items");
        }
        // Bust analytics caches
        Cache::forget('crypto_gainers');
        Cache::forget('crypto_losers');
        Cache::forget('crypto_trending');

        // Broadcast live prices via WebSocket
        try {
            $broadcaster->broadcastPrices();
            $this->info('Prices broadcast to WebSocket clients.');
        } catch (\Throwable $e) {
            $this->warn('Broadcast skipped: ' . $e->getMessage());
        }

        $this->info('Done! Database updated successfully.');

        return self::SUCCESS;
    }
}
