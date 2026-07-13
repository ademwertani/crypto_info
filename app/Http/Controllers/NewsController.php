<?php

namespace App\Http\Controllers;

use App\Models\NewsPost;
use App\Services\SeoService;
use Illuminate\View\View;

class NewsController extends Controller
{
    private const PER_PAGE = 9;

    public function index(): View
    {
        $search = trim((string) request()->query('search', ''));

        $query = NewsPost::query()->published()->latest('published_at');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $news = $query->paginate(self::PER_PAGE)->withQueryString();
        $seo  = SeoService::forNewsIndex();

        return view('news.index', compact('news', 'search', 'seo'));
    }

    public function show(NewsPost $news): View
    {
        abort_unless(
            $news->status === 'published' && $news->published_at?->lessThanOrEqualTo(now()),
            404
        );

        $recent = NewsPost::query()
            ->published()
            ->where('id', '!=', $news->id)
            ->latest('published_at')
            ->limit(3)
            ->get();

        $seo = SeoService::forNewsArticle($news);

        return view('news.show', compact('news', 'recent', 'seo'));
    }
}
