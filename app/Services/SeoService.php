<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Cryptocurrency;
use App\Models\News;
use App\Models\NewsPost;

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
    public ?string $breadcrumbLabel = null;
    /** @var array{label: string, url: string}|null */
    public ?array $breadcrumbParent = null;

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
        $seo->breadcrumbLabel = $coin->name;
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
        // Canonical intentionally stays the homepage: this targets the
        // RSS-aggregated News model, which has no public route of its own
        // (see App\Models\News). Do not point this at news.show — that
        // route now serves the separate, independent NewsPost module.
        $seo              = new self();
        $seo->title       = $article->title . ' | CryptoInfo';
        $seo->description = $article->ai_summary ?? $article->summary ?? substr($article->title, 0, 160);
        $seo->canonical   = url('/');
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
        $seo->breadcrumbLabel = $title;
        $seo->image       = url('/images/og-default.svg');
        $seo->locale      = app()->getLocale();
        $seo->alternateLanguages = [
            'x-default' => $seo->canonical,
            'en'        => $seo->canonical,
        ];

        return $seo;
    }

    public static function forBlogIndex(): self
    {
        $seo              = new self();
        $seo->title       = 'Crypto Guides & Articles | CryptoInfo';
        $seo->description = 'Beginner-friendly guides on cryptocurrency: how markets work, wallet security, stablecoins and how to read market data. Informational only, not financial advice.';
        $seo->canonical   = route('blog.index');
        $seo->breadcrumbLabel = 'Blog';
        $seo->image       = url('/images/og-default.svg');
        $seo->locale      = app()->getLocale();
        $seo->alternateLanguages = [
            'x-default' => $seo->canonical,
            'en'        => $seo->canonical,
        ];
        $seo->jsonld      = [
            '@context'    => 'https://schema.org',
            '@type'       => 'CollectionPage',
            'name'        => $seo->title,
            'description' => $seo->description,
            'url'         => $seo->canonical,
        ];

        return $seo;
    }

    public static function forArticle(Article $article): self
    {
        $seo              = new self();
        $seo->title       = ($article->meta_title ?: $article->title) . ' | CryptoInfo';
        $seo->description = $article->meta_description ?: $article->excerpt ?: substr($article->title, 0, 160);
        $seo->canonical   = route('blog.show', $article->slug);
        $seo->image       = $article->cover_image_url ?: url('/images/og-default.svg');
        $seo->og_type     = 'article';
        $seo->breadcrumbLabel = $article->title;
        $seo->breadcrumbParent = ['label' => 'Blog', 'url' => route('blog.index')];
        $seo->locale      = app()->getLocale();
        $seo->alternateLanguages = [
            'x-default' => $seo->canonical,
            'en'        => $seo->canonical,
        ];
        $seo->jsonld      = [
            '@context'      => 'https://schema.org',
            '@type'         => 'Article',
            'headline'      => $article->title,
            'description'   => $seo->description,
            'image'         => $seo->image,
            'url'           => $seo->canonical,
            'datePublished' => $article->published_at?->toIso8601String(),
            'dateModified'  => optional($article->updated_at)->toIso8601String(),
            'author'        => [
                '@type' => 'Organization',
                'name'  => $article->author_name,
            ],
            'publisher'     => [
                '@type' => 'Organization',
                'name'  => 'CryptoInfo',
                'url'   => url('/'),
                'logo'  => [
                    '@type' => 'ImageObject',
                    'url'   => url('/images/og-default.svg'),
                ],
            ],
        ];

        return $seo;
    }

    public static function forNewsIndex(): self
    {
        $seo              = new self();
        $seo->title       = 'Crypto News | CryptoInfo';
        $seo->description = 'The latest cryptocurrency news: market moves, project updates and industry developments.';
        $seo->canonical   = route('news.index');
        $seo->breadcrumbLabel = 'News';
        $seo->image       = url('/images/og-default.svg');
        $seo->locale      = app()->getLocale();
        $seo->alternateLanguages = [
            'x-default' => $seo->canonical,
            'en'        => $seo->canonical,
        ];
        $seo->jsonld      = [
            '@context'    => 'https://schema.org',
            '@type'       => 'CollectionPage',
            'name'        => $seo->title,
            'description' => $seo->description,
            'url'         => $seo->canonical,
        ];

        return $seo;
    }

    public static function forNewsArticle(NewsPost $newsPost): self
    {
        $seo              = new self();
        $seo->title       = ($newsPost->meta_title ?: $newsPost->title) . ' | CryptoInfo';
        $seo->description = $newsPost->meta_description ?: $newsPost->excerpt ?: substr($newsPost->title, 0, 160);
        $seo->canonical   = route('news.show', $newsPost->slug);
        $seo->image       = $newsPost->featured_image_url ?: url('/images/og-default.svg');
        $seo->og_type     = 'article';
        $seo->breadcrumbLabel = $newsPost->title;
        $seo->breadcrumbParent = ['label' => 'News', 'url' => route('news.index')];
        $seo->locale      = app()->getLocale();
        $seo->alternateLanguages = [
            'x-default' => $seo->canonical,
            'en'        => $seo->canonical,
        ];
        $seo->jsonld      = [
            '@context'      => 'https://schema.org',
            '@type'         => 'Article',
            'headline'      => $newsPost->title,
            'description'   => $seo->description,
            'image'         => $seo->image,
            'url'           => $seo->canonical,
            'datePublished' => $newsPost->published_at?->toIso8601String(),
            'dateModified'  => optional($newsPost->updated_at)->toIso8601String(),
            'publisher'     => [
                '@type' => 'Organization',
                'name'  => 'CryptoInfo',
                'url'   => url('/'),
                'logo'  => [
                    '@type' => 'ImageObject',
                    'url'   => url('/images/og-default.svg'),
                ],
            ],
        ];

        return $seo;
    }

    /**
     * Schema.org BreadcrumbList for this page, or null on the homepage
     * (Google recommends omitting breadcrumbs on the root page).
     */
    public function breadcrumbListJsonLd(): ?array
    {
        $current = $this->canonical ?? url()->current();

        if ($current === url('/')) {
            return null;
        }

        $label = $this->breadcrumbLabel ?? trim(explode('|', $this->title)[0]);

        $items = [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
        ];

        if ($this->breadcrumbParent) {
            $items[] = [
                '@type' => 'ListItem', 'position' => 2,
                'name' => $this->breadcrumbParent['label'], 'item' => $this->breadcrumbParent['url'],
            ];
        }

        $items[] = [
            '@type' => 'ListItem', 'position' => count($items) + 1, 'name' => $label, 'item' => $current,
        ];

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }
}
