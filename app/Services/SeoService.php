<?php

namespace App\Services;

use App\Models\Cryptocurrency;
use App\Models\News;
use Illuminate\Support\Facades\Route;

class SeoService
{
    public string $title       = '';
    public string $description = '';
    public ?string $canonical  = null;
    public ?string $image      = null;
    public string $og_type     = 'website';
    public string $robots      = 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1';
    public ?string $locale     = null;
    public array $alternateLanguages = [];
    public array $jsonld      = [];

    public static function forHome(): self
    {
        $seo              = new self();
        $seo->title       = 'Live Cryptocurrency Prices, Market Cap & Volume | CryptoInfo';
        $seo->description = 'Track real-time Bitcoin, Ethereum and 250+ crypto prices, market cap, volume and 24h performance with live market data.';
        $seo->canonical   = url('/');
        $seo->image       = url('/images/og-default.svg');
        $seo->locale      = app()->getLocale();
        $seo->alternateLanguages = [
            'x-default' => url('/'),
            'en'        => url('/'),
            'fr'        => url('/lang/fr'),
            'ar'        => url('/lang/ar'),
            'es'        => url('/lang/es'),
            'de'        => url('/lang/de'),
            'pt'        => url('/lang/pt'),
        ];
        $seo->jsonld      = [
            '@context'    => 'https://schema.org',
            '@type'       => 'WebSite',
            'name'        => 'CryptoInfo',
            'url'         => url('/'),
            'description' => $seo->description,
            'publisher'   => [
                '@type' => 'Organization',
                'name'  => 'CryptoInfo',
                'url'   => url('/'),
                'logo'  => [
                    '@type' => 'ImageObject',
                    'url'   => url('/images/og-default.svg'),
                ],
            ],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => url('/?search={search_term_string}'),
                'query-input' => 'required name=search_term_string',
            ],
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
        $seo->image       = $coin->image_url ?: url('/images/og-default.svg');
        $seo->og_type     = 'article';
        $seo->locale      = app()->getLocale();
        $seo->alternateLanguages = [
            'x-default' => $seo->canonical,
            'en'        => $seo->canonical,
        ];
        $seo->jsonld      = [
            '@context'    => 'https://schema.org',
            '@type'       => 'FinancialProduct',
            'name'        => $coin->name,
            'description' => $seo->description,
            'image'       => $seo->image,
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
        $seo->title       = $article->title . ' | CryptoInfo';
        $seo->description = $article->ai_summary ?? $article->summary ?? substr($article->title, 0, 160);
        $seo->canonical   = url('/');
        if (Route::has('news.show')) {
            $generated = route('news.show', ['news' => $article->slug], false);
            if (! empty($generated)) {
                $seo->canonical = $generated;
            }
        }
        $seo->image       = $article->image_url ?: url('/images/og-default.svg');
        $seo->og_type     = 'article';
        $seo->locale      = app()->getLocale();
        $seo->alternateLanguages = [
            'x-default' => $seo->canonical,
            'en'        => $seo->canonical,
        ];
        $seo->jsonld      = [
            '@context'         => 'https://schema.org',
            '@type'            => 'NewsArticle',
            'headline'         => $article->title,
            'description'      => $seo->description,
            'image'            => $seo->image,
            'url'              => $seo->canonical,
            'datePublished'    => $article->published_at?->toIso8601String(),
            'dateModified'     => optional($article->updated_at)->toIso8601String(),
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
        $seo->image       = url('/images/og-default.svg');
        $seo->locale      = app()->getLocale();
        $seo->alternateLanguages = [
            'x-default' => $seo->canonical,
            'en'        => $seo->canonical,
        ];

        return $seo;
    }
}
