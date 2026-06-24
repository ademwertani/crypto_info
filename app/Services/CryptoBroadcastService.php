<?php

namespace App\Services;

use App\Events\PriceUpdated;
use App\Models\Cryptocurrency;

class CryptoBroadcastService
{
    /**
     * Broadcast the latest prices for all tracked coins.
     * Called by FetchCryptoData after each successful DB upsert.
     */
    public function broadcastPrices(): void
    {
        $coins = Cryptocurrency::orderBy('market_cap_rank')
            ->get(['slug', 'name', 'symbol', 'image_url',
                   'current_price', 'market_cap', 'total_volume',
                   'price_change_percentage_1h_in_currency',
                   'price_change_percentage_24h_in_currency',
                   'price_change_percentage_7d_in_currency',
                   'market_cap_rank'])
            ->map(fn ($c) => [
                'slug'       => $c->slug,
                'name'       => $c->name,
                'symbol'     => $c->symbol,
                'image'      => $c->image_url,
                'price'      => (float) $c->current_price,
                'market_cap' => (float) $c->market_cap,
                'volume'     => (float) $c->total_volume,
                'change_1h'  => (float) $c->price_change_percentage_1h_in_currency,
                'change_24h' => (float) $c->price_change_percentage_24h_in_currency,
                'change_7d'  => (float) $c->price_change_percentage_7d_in_currency,
                'rank'       => $c->market_cap_rank,
            ])
            ->values()
            ->all();

        broadcast(new PriceUpdated($coins, now()->toISOString()));
    }
}
