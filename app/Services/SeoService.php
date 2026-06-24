<?php

namespace App\Services;

use App\Models\Cryptocurrency;
use App\Models\News;

class SeoService
{
    public string $title       = '';
    public string $description = '';
    public ?string $canonical  = null;
    public ?string $image      = null;
    public string $og_type     = 'website';
    public array  $jsonld      = [];

    public static function forHome(): self
    {
        $seo              = new self();
        $seo->title       = 'Crypto Info — Live Cryptocurrency Prices, Market Cap & Volume';
        $seo->description = 'Real-time prices for Bitcoin, Ethereum and 250+ cryptocurrencies. Track market cap, volume, and 24h changes with live WebSocket updates.';
        $seo->canonical   = url('/');
        $seo->jsonld      = [
            '@context'    => 'https://schema.org',
            '@type'       => 'WebSite',
            'name'        => 'CryptoInfo',
            'url'         => url('/'),
            'description' => $seo->description,
        ];

        return $seo;
    }

    public static function forCoin(Cryptocurrency $coin): self
    {
        $price  = number_format((float) $coin->current_price, 2);
        $change = number_format((float) $coin->price_change_percentage_24h_in_currency, 2);
        $dir    = $change >= 0 ? 'up' : 'down';

        $seo              = new self();
        $seo->title       = "{$coin->name} ({$coin->symbol}) Price: \${$price} | CryptoInfo";
        $seo->description = "{$coin->name} price is \${$price} today, {$dir} {$change}% in the last 24h. View market cap, volume, supply and historical data.";
        $seo->canonical   = route('crypto.show', $coin->slug);
        $seo->image       = $coin->image_url;
        $seo->og_type     = 'article';
        $seo->jsonld      = [
            '@context'    => 'https://schema.org',
            '@type'       => 'FinancialProduct',
            'name'        => $coin->name,
            'description' => $seo->description,
            'image'       => $coin->image_url,
            'url'         => $seo->canonical,
            'offers'      => [
                '@type'         => 'Offer',
                'price'         => (float) $coin->current_price,
                'priceCurrency' => 'USD',
            ],
        ];

        return $seo;
    }

    public static function forNews(News $article): self
    {
        $seo              = new self();
        $seo->title       = $article->title . ' | CryptoInfo News';
        $seo->description = $article->ai_summary ?? $article->summary ?? substr($article->title, 0, 160);
        $seo->canonical   = route('news.show', $article->slug);
        $seo->image       = $article->image_url;
        $seo->og_type     = 'article';
        $seo->jsonld      = [
            '@context'         => 'https://schema.org',
            '@type'            => 'NewsArticle',
            'headline'         => $article->title,
            'description'      => $seo->description,
            'image'            => $article->image_url,
            'url'              => $seo->canonical,
            'datePublished'    => $article->published_at?->toIso8601String(),
            'dateModified'     => $article->updated_at->toIso8601String(),
            'publisher'        => [
                '@type' => 'Organization',
                'name'  => 'CryptoInfo',
                'url'   => url('/'),
            ],
        ];

        return $seo;
    }

    public static function forMarket(string $type): self
    {
        $labels = [
            'gainers' => ['Top Crypto Gainers Today', 'Cryptocurrencies with the biggest price increase in the last 24 hours.'],
            'losers'  => ['Top Crypto Losers Today',  'Cryptocurrencies with the biggest price drop in the last 24 hours.'],
            'trending'=> ['Trending Cryptocurrencies', 'Most traded cryptocurrencies by volume in the last 24 hours.'],
        ];

        [$title, $desc]   = $labels[$type] ?? ['Market', ''];
        $seo              = new self();
        $seo->title       = $title . ' | CryptoInfo';
        $seo->description = $desc;
        $seo->canonical   = url("/{$type}");

        return $seo;
    }
}
