<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MoneyPage extends Model
{
    use HasUniqueSlug;

    protected $fillable = [
        'slug', 'locale', 'translation_group',
        'type', 'cluster',
        'h1', 'meta_title', 'meta_description', 'intro_html', 'body_html',
        'faq', 'cta_config', 'related_coin_ids', 'related_page_ids',
        'status', 'author', 'published_at', 'reading_time_min',
    ];

    protected $casts = [
        'faq'               => 'array',
        'cta_config'        => 'array',
        'related_coin_ids'  => 'array',
        'related_page_ids'  => 'array',
        'published_at'      => 'datetime',
        'views'             => 'integer',
        'reading_time_min'  => 'integer',
    ];

    /** Memoized result of parseBody() — avoids re-parsing body_html per call. */
    private ?array $parsedBody = null;

    protected static function booted(): void
    {
        static::saving(function (self $page) {
            if (empty($page->reading_time_min)) {
                $words = str_word_count(strip_tags((string) $page->body_html));
                $page->reading_time_min = max(1, (int) ceil($words / 200));
            }
        });
    }

    protected static function slugSourceField(): string
    {
        return 'h1';
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    /** Single batched query — never call in a loop over multiple pages. */
    public function relatedCoins(): Collection
    {
        if (empty($this->related_coin_ids)) {
            return collect();
        }

        return Cryptocurrency::query()
            ->whereIn('id', $this->related_coin_ids)
            ->get(['id', 'name', 'slug', 'symbol', 'image_url']);
    }

    /** Single batched query; only ever links to published sibling pages. */
    public function relatedPages(): Collection
    {
        if (empty($this->related_page_ids)) {
            return collect();
        }

        return static::query()
            ->whereIn('id', $this->related_page_ids)
            ->published()
            ->get(['id', 'h1', 'slug']);
    }

    /** Sibling pages sharing this page's translation_group, keyed by locale. */
    public function translationSiblings(): Collection
    {
        if (blank($this->translation_group)) {
            return collect();
        }

        return static::query()
            ->where('translation_group', $this->translation_group)
            ->where('id', '!=', $this->id)
            ->published()
            ->get(['id', 'slug', 'locale']);
    }

    /**
     * body_html with a slugified id="" injected on every <h2>, so the
     * table of contents can link straight to in-page anchors.
     */
    public function bodyWithHeadingAnchors(): string
    {
        return $this->parseBody()['html'];
    }

    /** @return array<int, array{id: string, label: string}> */
    public function tableOfContents(): array
    {
        return $this->parseBody()['toc'];
    }

    /** @return array{html: string, toc: array<int, array{id: string, label: string}>} */
    private function parseBody(): array
    {
        if ($this->parsedBody !== null) {
            return $this->parsedBody;
        }

        $html = trim((string) $this->body_html);

        if ($html === '') {
            return $this->parsedBody = ['html' => '', 'toc' => []];
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        // The XML prolog forces UTF-8 decoding without leaking a <meta> tag
        // into the saved fragment — needed for accented/Arabic headings.
        $dom->loadHTML(
            '<?xml encoding="utf-8" ?><div id="__root">'.$html.'</div>',
            LIBXML_NOERROR | LIBXML_NOWARNING
        );
        libxml_clear_errors();

        $toc  = [];
        $used = [];

        foreach ($dom->getElementsByTagName('h2') as $heading) {
            $text = trim($heading->textContent);
            if ($text === '') {
                continue;
            }

            $base = Str::slug($text) ?: 'section';
            $id   = $base;
            $n    = 2;
            while (in_array($id, $used, true)) {
                $id = "{$base}-{$n}";
                $n++;
            }
            $used[] = $id;

            $heading->setAttribute('id', $id);
            $toc[] = ['id' => $id, 'label' => $text];
        }

        $root  = $dom->getElementById('__root');
        $inner = '';
        foreach ($root->childNodes as $child) {
            $inner .= $dom->saveHTML($child);
        }

        return $this->parsedBody = ['html' => $inner, 'toc' => $toc];
    }
}
