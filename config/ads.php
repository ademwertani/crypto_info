<?php

return [
    'enabled' => env('ADS_ENABLED', false),

    'adsense' => [
        'publisher_id' => env('ADSENSE_PUBLISHER_ID', ''),
        'leaderboard_slot'  => env('ADSENSE_LEADERBOARD_SLOT', ''),
        'rectangle_slot'    => env('ADSENSE_RECTANGLE_SLOT', ''),
    ],

    'affiliates' => [
        'binance_ref'  => env('AFFILIATE_BINANCE_REF', 'CRYPTOINFO'),
        'bybit_id'     => env('AFFILIATE_BYBIT_ID', 'CRYPTOINFO'),
        'okx_ref'      => env('AFFILIATE_OKX_REF', 'CRYPTOINFO'),
    ],
];
