<?php

/**
 * Content calendar for `php artisan blog:generate`. Each entry becomes at
 * most one draft Article (skipped if its slug already exists). Editable
 * here without touching command/service code — add a title, pick a
 * category slug and it's picked up on the next run.
 *
 * `category` must match a real `article_categories.slug` value (see
 * ArticleCategorySeeder) — if it doesn't resolve, the article is still
 * created with no category rather than a fatal error (see
 * ArticleGeneratorService::resolveCategoryId()).
 *
 * `related_coin_slugs` must match real `cryptocurrencies.slug` values — any
 * that don't resolve are silently dropped, never a fatal error.
 */
return [
    'articles' => [
        [
            'title' => 'What Is DeFi? A Beginner\'s Guide to Decentralized Finance',
            'category' => 'beginner-guides',
            'related_coin_slugs' => ['ethereum'],
        ],
        [
            'title' => 'What Is Bitcoin Halving and Why Does It Matter?',
            'category' => 'beginner-guides',
            'related_coin_slugs' => ['bitcoin'],
        ],
        [
            'title' => 'Proof of Work vs Proof of Stake: What\'s the Difference?',
            'category' => 'beginner-guides',
            'related_coin_slugs' => ['bitcoin', 'ethereum'],
        ],
        [
            'title' => 'What Is a Stablecoin and How Does It Stay Stable?',
            'category' => 'beginner-guides',
            'related_coin_slugs' => ['tether'],
        ],
        [
            'title' => 'NFTs Explained: What They Are and How They Work',
            'category' => 'beginner-guides',
            'related_coin_slugs' => [],
        ],
        [
            'title' => 'How to Recognize and Avoid Common Crypto Scams',
            'category' => 'security',
            'related_coin_slugs' => [],
        ],
        [
            'title' => 'Seed Phrases 101: How to Store Yours Safely',
            'category' => 'security',
            'related_coin_slugs' => [],
        ],
        [
            'title' => 'Two-Factor Authentication: Why Every Crypto Account Needs It',
            'category' => 'security',
            'related_coin_slugs' => [],
        ],
        [
            'title' => 'What Is Market Capitalization and Why It Can Be Misleading',
            'category' => 'market-analysis',
            'related_coin_slugs' => [],
        ],
        [
            'title' => 'Understanding Crypto Market Cycles: Bull and Bear Basics',
            'category' => 'market-analysis',
            'related_coin_slugs' => ['bitcoin'],
        ],
        [
            'title' => 'What Is the Fear & Greed Index and How to Read It',
            'category' => 'market-analysis',
            'related_coin_slugs' => [],
        ],
        [
            'title' => 'Trading Volume Explained: What High and Low Volume Mean',
            'category' => 'market-analysis',
            'related_coin_slugs' => [],
        ],
    ],
];
