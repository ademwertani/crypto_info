<?php

/**
 * Content calendar for `php artisan pages:generate`. Each entry becomes at
 * most one draft MoneyPage (skipped if its slug already exists). Editable
 * here without touching command/service code — add a title, pick a type
 * and cluster, and it's picked up on the next run.
 *
 * `related_coin_slugs` must match real `cryptocurrencies.slug` values —
 * any that don't resolve are silently dropped (see
 * MoneyPageGeneratorService::resolveCoinIds()), never a fatal error.
 */
return [
    'pages' => [
        [
            'title' => 'How to Buy Bitcoin: A Complete Beginner\'s Guide',
            'type' => 'buy_asset',
            'cluster' => 'exchanges',
            'related_coin_slugs' => ['bitcoin'],
        ],
        [
            'title' => 'How to Buy Ethereum: A Complete Beginner\'s Guide',
            'type' => 'buy_asset',
            'cluster' => 'exchanges',
            'related_coin_slugs' => ['ethereum'],
        ],
        [
            'title' => 'How to Buy USDT (Tether): Step-by-Step',
            'type' => 'buy_asset',
            'cluster' => 'exchanges',
            'related_coin_slugs' => ['tether'],
        ],
        [
            'title' => 'How to Buy Solana: A Complete Beginner\'s Guide',
            'type' => 'buy_asset',
            'cluster' => 'exchanges',
            'related_coin_slugs' => ['solana'],
        ],
        [
            'title' => 'How to Buy XRP: A Complete Beginner\'s Guide',
            'type' => 'buy_asset',
            'cluster' => 'exchanges',
            'related_coin_slugs' => ['ripple'],
        ],
        [
            'title' => 'Binance Review: Features, Fees and Security',
            'type' => 'exchange_review',
            'cluster' => 'exchanges',
            'related_coin_slugs' => [],
        ],
        [
            'title' => 'Best Crypto Exchanges for Beginners',
            'type' => 'best_list',
            'cluster' => 'exchanges',
            'related_coin_slugs' => [],
        ],
        [
            'title' => 'Coinbase vs Binance: Which Exchange Should You Use?',
            'type' => 'comparison',
            'cluster' => 'exchanges',
            'related_coin_slugs' => [],
        ],
        [
            'title' => 'Ledger Nano Review: Is It Worth It?',
            'type' => 'wallet_review',
            'cluster' => 'wallets',
            'related_coin_slugs' => [],
        ],
        [
            'title' => 'How to Set Up a MetaMask Wallet',
            'type' => 'how_to',
            'cluster' => 'wallets',
            'related_coin_slugs' => ['ethereum'],
        ],
        [
            'title' => 'Best Crypto Wallets for Security',
            'type' => 'best_list',
            'cluster' => 'wallets',
            'related_coin_slugs' => [],
        ],
        [
            'title' => 'Hot Wallet vs Cold Wallet: What\'s the Difference?',
            'type' => 'comparison',
            'cluster' => 'wallets',
            'related_coin_slugs' => [],
        ],
    ],
];
