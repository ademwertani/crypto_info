<?php

namespace App\Http\Controllers;

use App\Models\MoneyPage;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MoneyPageController extends Controller
{
    private const PER_PAGE = 12;

    // Deliberately simple regex, no package: catches the crawlers/tools
    // that would otherwise inflate the view counter (search engines, SEO
    // crawlers, social-share unfurlers, generic HTTP clients).
    private const BOT_UA_PATTERN = '/bot|crawl|spider|slurp|bingpreview|facebookexternalhit|curl|wget|python-requests|scrapy|headless/i';

    public function index(Request $request): View
    {
        $cluster = (string) $request->query('cluster', '');

        $query = MoneyPage::query()->published()->latest('published_at');

        if ($cluster !== '') {
            $query->where('cluster', $cluster);
        }

        $pages    = $query->paginate(self::PER_PAGE)->withQueryString();
        $clusters = MoneyPage::query()->published()->select('cluster')->distinct()->orderBy('cluster')->pluck('cluster');
        $seo      = SeoService::forGuidesIndex();

        return view('guides.index', compact('pages', 'clusters', 'cluster', 'seo'));
    }

    public function show(Request $request, MoneyPage $moneyPage): View
    {
        abort_unless(
            $moneyPage->status === 'published' && $moneyPage->published_at?->lessThanOrEqualTo(now()),
            404
        );

        if (! $this->looksLikeBot($request)) {
            $moneyPage->increment('views');
        }

        $relatedCoins = $moneyPage->relatedCoins();
        $relatedPages = $moneyPage->relatedPages();
        $toc          = $moneyPage->tableOfContents();
        $seo          = SeoService::forMoneyPage($moneyPage);

        return view('guides.show', compact('moneyPage', 'relatedCoins', 'relatedPages', 'toc', 'seo'));
    }

    /**
     * Renders any MoneyPage regardless of status/schedule — lets an editor
     * see exactly how a draft will look once published, before publishing
     * it. Gated inline (not via the `auth` middleware alias): this app has
     * no route named "login" — only Filament's "filament.admin.auth.login"
     * — so the generic `auth` middleware would 500 on redirect instead of
     * cleanly bouncing a guest. 404 matches the abort_unless() below.
     */
    public function preview(MoneyPage $moneyPage): View
    {
        abort_unless(auth()->check(), 404);

        $relatedCoins = $moneyPage->relatedCoins();
        $relatedPages = $moneyPage->relatedPages();
        $toc          = $moneyPage->tableOfContents();
        $seo          = SeoService::forMoneyPage($moneyPage);
        $isPreview    = true;

        return view('guides.show', compact('moneyPage', 'relatedCoins', 'relatedPages', 'toc', 'seo', 'isPreview'));
    }

    private function looksLikeBot(Request $request): bool
    {
        $ua = (string) $request->userAgent();

        return $ua === '' || (bool) preg_match(self::BOT_UA_PATTERN, $ua);
    }
}
