<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CryptoApiService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.coingecko.url', 'https://api.coingecko.com/api/v3');
    }

    /**
     * Fetch the top $perPage coins for a given $page from CoinGecko.
     *
     * CoinGecko free-tier endpoint:
     *   GET /coins/markets?vs_currency=usd&order=market_cap_desc
     *                     &per_page=250&page=1&price_change_percentage=1h,24h,7d
     *
     * Sample item structure returned:
     * {
     *   "id": "bitcoin",
     *   "symbol": "btc",
     *   "name": "Bitcoin",
     *   "image": "https://…/bitcoin.png",
     *   "current_price": 67000,
     *   "market_cap": 1320000000000,
     *   "market_cap_rank": 1,
     *   "fully_diluted_valuation": 1400000000000,
     *   "total_volume": 30000000000,
     *   "high_24h": 68000,
     *   "low_24h": 65000,
     *   "price_change_percentage_1h_in_currency": 0.12,
     *   "price_change_percentage_24h_in_currency": -1.5,
     *   "price_change_percentage_7d_in_currency": 4.3,
     *   "circulating_supply": 19700000,
     *   "total_supply": 21000000,
     *   "max_supply": 21000000,
     *   "ath": 73750,
     *   "ath_change_percentage": -9.12,
     *   "ath_date": "2024-03-14T07:10:36.635Z",
     *   "atl": 67.81,
     *   "atl_change_percentage": 98700,
     *   "atl_date": "2013-07-06T00:00:00.000Z"
     * }
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchMarkets(int $page = 1, int $perPage = 250): array
    {
        try {
            $apiKey = config('services.coingecko.api_key', '');
            $headers = ['User-Agent' => 'CryptoInfo/1.0 (contact@cryptoinfo.dev)', 'Accept' => 'application/json'];
            if ($apiKey) {
                $headers['x-cg-demo-api-key'] = $apiKey;
            }
            $response = Http::timeout(15)
                ->retry(2, 1000)
                ->withHeaders($headers)
                ->get("{$this->baseUrl}/coins/markets", [
                    'vs_currency'            => 'usd',
                    'order'                  => 'market_cap_desc',
                    'per_page'               => $perPage,
                    'page'                   => $page,
                    'price_change_percentage'=> '1h,24h,7d',
                    'sparkline'              => 'true',
                ]);

            $response->throw();

            return $response->json() ?? [];
        } catch (ConnectionException $e) {
            Log::error('CoinGecko connection error', ['message' => $e->getMessage()]);
        } catch (RequestException $e) {
            Log::error('CoinGecko HTTP error', [
                'status'  => $e->response->status(),
                'message' => $e->getMessage(),
            ]);
        }

        return [];
    }

    /**
     * Fetch extended detail for a single coin (description, links, etc.).
     *
     * @return array<string, mixed>
     */
    public function fetchCoinDetail(string $id): array
    {
        try {
            $apiKey2  = config('services.coingecko.api_key', '');
            $headers2 = ['User-Agent' => 'CryptoInfo/1.0 (contact@cryptoinfo.dev)', 'Accept' => 'application/json'];
            if ($apiKey2) {
                $headers2['x-cg-demo-api-key'] = $apiKey2;
            }
            $response = Http::timeout(15)
                ->withHeaders($headers2)
                ->get("{$this->baseUrl}/coins/{$id}", [
                    'localization'   => 'false',
                    'tickers'        => 'false',
                    'market_data'    => 'false',
                    'community_data' => 'false',
                    'developer_data' => 'false',
                ]);

            $response->throw();

            return $response->json() ?? [];
        } catch (ConnectionException $e) {
            Log::error('CoinGecko connection error (detail)', ['message' => $e->getMessage()]);
        } catch (RequestException $e) {
            Log::error('CoinGecko HTTP error (detail)', [
                'status'  => $e->response->status(),
                'message' => $e->getMessage(),
            ]);
        }

        return [];
    }
}
