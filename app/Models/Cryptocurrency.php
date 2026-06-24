<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Cryptocurrency extends Model
{
    protected $fillable = [
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
    ];

    protected $casts = [
        'current_price'                          => 'decimal:10',
        'market_cap'                             => 'decimal:2',
        'fully_diluted_valuation'                => 'decimal:2',
        'total_volume'                           => 'decimal:2',
        'high_24h'                               => 'decimal:10',
        'low_24h'                                => 'decimal:10',
        'price_change_percentage_1h_in_currency' => 'decimal:4',
        'price_change_percentage_24h_in_currency'=> 'decimal:4',
        'price_change_percentage_7d_in_currency' => 'decimal:4',
        'circulating_supply'                     => 'decimal:2',
        'total_supply'                           => 'decimal:2',
        'max_supply'                             => 'decimal:2',
        'ath'                                    => 'decimal:10',
        'ath_change_percentage'                  => 'decimal:4',
        'ath_date'                               => 'datetime',
        'atl'                                    => 'decimal:10',
        'atl_change_percentage'                  => 'decimal:4',
        'atl_date'                               => 'datetime',
    ];

    /** Format price for display — avoids scientific notation on tiny values. */
    public function formattedPrice(): string
    {
        $price = (float) $this->current_price;

        $decimals = match (true) {
            $price === 0.0   => 2,
            $price < 0.00001 => 10,
            $price < 0.01    => 6,
            $price < 1       => 4,
            default          => 2,
        };

        return '$' . number_format($price, $decimals);
    }

    /** Returns a Tailwind CSS text-color class based on sign of a percentage. */
    public static function percentColor(?float $value): string
    {
        if ($value === null) {
            return 'text-gray-400';
        }

        return $value >= 0 ? 'text-emerald-400' : 'text-red-400';
    }

    /** Returns ▲ or ▼ arrow for a percentage value. */
    public static function percentArrow(?float $value): string
    {
        if ($value === null) {
            return '';
        }

        return $value >= 0 ? '▲' : '▼';
    }
}
