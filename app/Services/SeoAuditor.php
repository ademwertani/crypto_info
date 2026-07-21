<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Cryptocurrency;
use App\Models\MoneyPage;
use App\Models\NewsPost;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Audits every indexable content type (crypto pages, money pages, blog
 * articles, news posts) for basic on-page SEO problems, without needing an
 * HTTP round-trip per page. Used by both `php artisan seo:audit` and the
 * Filament dashboard widgets — see cached() for the shared, TTL'd result
 * the widgets use, and run() for a always-fresh pass (what the CLI wants).
 */
class SeoAuditor
{
    private const CHUNK_SIZE = 200;

    private const META_TITLE_MAX = 60;

    private const META_DESCRIPTION_MIN = 120;

    private const META_DESCRIPTION_MAX = 160;

    private const MIN_WORDS = 300;

    // Matches the slug constraint every dynamic content route in
    // routes/web.php actually enforces (crypto.show, guides.show, ...).
    private const SLUG_PATTERN = '/^[a-z0-9\-]+$/';

    // Recognizes the app's own internal content URLs inside body HTML, so
    // "broken internal links" can be checked without any HTTP request.
    private const INTERNAL_LINK_PATTERNS = [
        'crypto' => '#^/currencies/([a-z0-9\-]+)/?$#',
        'crypto_seo_alias' => '#^/crypto/([a-z0-9\-]+)-price/?$#',
        'guides' => '#^/guides/([a-z0-9\-]+)/?$#',
        'blog' => '#^/blog/([a-z0-9\-]+)/?$#',
        'news' => '#^/news/([a-z0-9\-]+)/?$#',
    ];

    /** @var array<string, bool> memoized "does this internal link resolve?" per run() call */
    private array $linkExistsCache = [];

    /**
     * Cached for the Filament dashboard widgets (10 min, same pattern as
     * GlobalMarketService::getGlobalStats()) so loading /admin doesn't
     * trigger a full audit on every page view.
     */
    public function cached(): array
    {
        return Cache::remember('seo_audit_result', 600, fn () => $this->run());
    }

    /**
     * @return array{pages: Collection, errors: Collection, stats: array}
     */
    public function run(): array
    {
        $this->linkExistsCache = [];

        $pages = collect();
        $errors = collect();

        foreach ($this->specs() as $type => $spec) {
            $spec['query']()->chunk(self::CHUNK_SIZE, function (Collection $rows) use ($type, $spec, $pages, $errors) {
                foreach ($rows as $row) {
                    try {
                        $pages->push($this->auditRow($type, $row, $spec));
                    } catch (Throwable $e) {
                        // Never let one bad row take down the whole audit.
                        $errors->push([
                            'type' => $type,
                            'id' => $row->getKey(),
                            'message' => $e->getMessage(),
                        ]);
                    }
                }
            });
        }

        return [
            'pages' => $pages,
            'errors' => $errors,
            'stats' => $this->summarize($pages, $errors),
        ];
    }

    /**
     * Per-type field mapping. Keeping this as data (not 4 duplicated
     * methods) is what lets run() stay a single loop.
     */
    private function specs(): array
    {
        return [
            'crypto' => [
                'label' => 'Crypto page',
                'query' => fn () => Cryptocurrency::query(),
                'h1' => fn (Model $m) => (string) $m->name,
                // Mirrors SeoService::forCoin() exactly — see that method
                // if this ever needs to change. Not calling it directly:
                // it's cheap here, but doing this for MoneyPage/Article
                // pulls in DB queries / array building the audit doesn't need.
                'title' => function (Model $m) {
                    $price = number_format((float) $m->current_price, 2);

                    return "{$m->name} ({$m->symbol}) Price: \${$price} | CryptoInfo";
                },
                'description' => function (Model $m) {
                    $price = number_format((float) $m->current_price, 2);
                    $change = number_format((float) $m->price_change_percentage_24h_in_currency, 2);
                    $dir = $change >= 0 ? 'up' : 'down';

                    return "{$m->name} price is \${$price} today, {$dir} {$change}% in the last 24h. View market cap, volume, supply and historical data.";
                },
                // description is plain text (escaped + nl2br in the view),
                // never HTML — so the H1-in-body and image-alt checks don't
                // apply to this type at all.
                'bodyHtml' => null,
                'bodyText' => fn (Model $m) => (string) $m->description,
                'slug' => fn (Model $m) => (string) $m->slug,
                'url' => fn (Model $m) => route('crypto.show', $m->slug),
            ],

            'money_page' => [
                'label' => 'Money page',
                'query' => fn () => MoneyPage::query()->published(),
                'h1' => fn (Model $m) => (string) $m->h1,
                // Mirrors SeoService::forMoneyPage()'s title/description
                // fallback — deliberately not calling forMoneyPage() itself,
                // which also queries translationSiblings() per page.
                'title' => fn (Model $m) => ($m->meta_title ?: $m->h1).' | CryptoInfo',
                'description' => fn (Model $m) => $m->meta_description
                    ?: substr(trim(strip_tags((string) $m->intro_html)), 0, 160),
                'bodyHtml' => fn (Model $m) => (string) $m->intro_html.(string) $m->body_html,
                'bodyText' => fn (Model $m) => (string) $m->body_html,
                'slug' => fn (Model $m) => (string) $m->slug,
                'url' => fn (Model $m) => route('guides.show', $m->slug),
            ],

            'article' => [
                'label' => 'Blog article',
                'query' => fn () => Article::query()->published(),
                'h1' => fn (Model $m) => (string) $m->title,
                // Mirrors SeoService::forArticle().
                'title' => fn (Model $m) => ($m->meta_title ?: $m->title).' | CryptoInfo',
                'description' => fn (Model $m) => $m->meta_description
                    ?: ($m->excerpt ?: substr((string) $m->title, 0, 160)),
                // sections is a JSON array of HTML blocks, not one field.
                'bodyHtml' => fn (Model $m) => implode('', (array) ($m->sections ?? [])),
                'bodyText' => fn (Model $m) => implode(' ', (array) ($m->sections ?? [])),
                'slug' => fn (Model $m) => (string) $m->slug,
                'url' => fn (Model $m) => route('blog.show', $m->slug),
            ],

            'news_post' => [
                'label' => 'News post',
                'query' => fn () => NewsPost::query()->published(),
                'h1' => fn (Model $m) => (string) $m->title,
                // Mirrors SeoService::forNewsArticle().
                'title' => fn (Model $m) => ($m->meta_title ?: $m->title).' | CryptoInfo',
                'description' => fn (Model $m) => $m->meta_description
                    ?: ($m->excerpt ?: substr((string) $m->title, 0, 160)),
                'bodyHtml' => fn (Model $m) => (string) $m->content,
                'bodyText' => fn (Model $m) => (string) $m->content,
                'slug' => fn (Model $m) => (string) $m->slug,
                'url' => fn (Model $m) => route('news.show', $m->slug),
            ],
        ];
    }

    private function auditRow(string $type, Model $model, array $spec): array
    {
        $issues = [];
        $applicable = 0;
        $passed = 0;

        $title = (string) $spec['title']($model);
        $applicable++;
        if ($title === '' || mb_strlen($title) > self::META_TITLE_MAX) {
            $issues[] = 'meta_title';
        } else {
            $passed++;
        }

        $description = (string) $spec['description']($model);
        $descriptionLength = mb_strlen($description);
        $applicable++;
        if ($description === ''
            || $descriptionLength < self::META_DESCRIPTION_MIN
            || $descriptionLength > self::META_DESCRIPTION_MAX
        ) {
            $issues[] = 'meta_description';
        } else {
            $passed++;
        }

        $h1 = (string) $spec['h1']($model);
        $bodyHtml = $spec['bodyHtml'] ? (string) $spec['bodyHtml']($model) : '';
        $applicable++;
        if (trim($h1) === '') {
            $issues[] = 'h1_missing';
        } elseif ($bodyHtml !== '' && $this->countH1s($bodyHtml) > 0) {
            $issues[] = 'h1_multiple';
        } else {
            $passed++;
        }

        $bodyText = (string) $spec['bodyText']($model);
        $wordCount = str_word_count(strip_tags($bodyText));
        $applicable++;
        if ($wordCount < self::MIN_WORDS) {
            $issues[] = 'thin_content';
        } else {
            $passed++;
        }

        if ($spec['bodyHtml']) {
            $applicable++;
            if ($this->imagesWithoutAlt($bodyHtml) > 0) {
                $issues[] = 'images_missing_alt';
            } else {
                $passed++;
            }
        }

        $slug = (string) $spec['slug']($model);
        $applicable++;
        if ($slug === '' || ! preg_match(self::SLUG_PATTERN, $slug)) {
            $issues[] = 'canonical_invalid';
        } else {
            $passed++;
        }

        $applicable++;
        if ($bodyHtml !== '' && $this->countBrokenInternalLinks($bodyHtml) > 0) {
            $issues[] = 'broken_internal_links';
        } else {
            $passed++;
        }

        return [
            'type' => $type,
            'label' => $spec['label'],
            'id' => $model->getKey(),
            'slug' => $slug,
            'url' => $spec['url']($model),
            'h1_or_title' => $h1,
            'meta_title_length' => mb_strlen($title),
            'meta_description_length' => $descriptionLength,
            'word_count' => $wordCount,
            'issues' => $issues,
            'issue_count' => count($issues),
            'score' => $applicable > 0 ? (int) round($passed / $applicable * 100) : 100,
        ];
    }

    private function summarize(Collection $pages, Collection $errors): array
    {
        $byType = $pages->groupBy('type')->map(fn (Collection $group, string $type) => [
            'type' => $type,
            'label' => $group->first()['label'],
            'count' => $group->count(),
            'average_score' => round($group->avg('score'), 1),
            'pages_with_issues' => $group->filter(fn (array $p) => $p['issue_count'] > 0)->count(),
        ])->values();

        return [
            'total' => $pages->count(),
            'average_score' => $pages->isNotEmpty() ? round($pages->avg('score'), 1) : 100.0,
            'pages_with_issues' => $pages->filter(fn (array $p) => $p['issue_count'] > 0)->count(),
            'broken_internal_links' => $pages->filter(fn (array $p) => in_array('broken_internal_links', $p['issues'], true))->count(),
            'errors' => $errors->count(),
            'by_type' => $byType->all(),
        ];
    }

    // ── HTML body analysis ──────────────────────────────────────────────

    private function parseHtml(string $html): \DOMDocument
    {
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        // XML prolog forces UTF-8 decoding without leaking a <meta> tag —
        // same technique as App\Models\MoneyPage::parseBody().
        $dom->loadHTML('<?xml encoding="utf-8" ?><div id="__root">'.$html.'</div>', LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();

        return $dom;
    }

    private function countH1s(string $html): int
    {
        return $this->parseHtml($html)->getElementsByTagName('h1')->length;
    }

    private function imagesWithoutAlt(string $html): int
    {
        $count = 0;

        foreach ($this->parseHtml($html)->getElementsByTagName('img') as $img) {
            if (trim($img->getAttribute('alt')) === '') {
                $count++;
            }
        }

        return $count;
    }

    private function countBrokenInternalLinks(string $html): int
    {
        $broken = 0;

        foreach ($this->parseHtml($html)->getElementsByTagName('a') as $a) {
            $href = trim($a->getAttribute('href'));
            if ($href === '') {
                continue;
            }

            $target = $this->resolveInternalTarget($href);
            if ($target === null) {
                continue; // external or unrecognized — best effort, skip
            }

            [$targetType, $slug] = $target;
            if (! $this->internalLinkExists($targetType, $slug)) {
                $broken++;
            }
        }

        return $broken;
    }

    /** @return array{0: string, 1: string}|null */
    private function resolveInternalTarget(string $href): ?array
    {
        $path = $href;

        if (str_starts_with($href, 'http://') || str_starts_with($href, 'https://')) {
            $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
            $linkHost = parse_url($href, PHP_URL_HOST);

            if ($linkHost === null || $linkHost !== $appHost) {
                return null;
            }

            $path = parse_url($href, PHP_URL_PATH) ?? '';
        } elseif (! str_starts_with($path, '/')) {
            return null; // mailto:, tel:, #anchor, javascript:, etc.
        }

        foreach (self::INTERNAL_LINK_PATTERNS as $key => $pattern) {
            if (preg_match($pattern, $path, $matches)) {
                $type = $key === 'crypto_seo_alias' ? 'crypto' : $key;

                return [$type, $matches[1]];
            }
        }

        return null;
    }

    private function internalLinkExists(string $type, string $slug): bool
    {
        $key = "{$type}:{$slug}";

        if (array_key_exists($key, $this->linkExistsCache)) {
            return $this->linkExistsCache[$key];
        }

        $exists = match ($type) {
            'crypto' => Cryptocurrency::where('slug', $slug)->exists(),
            'guides' => MoneyPage::where('slug', $slug)->published()->exists(),
            'blog' => Article::where('slug', $slug)->published()->exists(),
            'news' => NewsPost::where('slug', $slug)->published()->exists(),
            default => true,
        };

        return $this->linkExistsCache[$key] = $exists;
    }
}
