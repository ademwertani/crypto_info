<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Cryptocurrency;
use App\Services\SeoService;
use Illuminate\View\View;

class ArticleController extends Controller
{
    private const PER_PAGE = 9;

    public function index(): View
    {
        $categorySlug = (string) request()->query('category', '');
        $category     = null;

        $query = Article::query()->published()->with('category')->latest('published_at');

        if ($categorySlug !== '') {
            $category = ArticleCategory::where('slug', $categorySlug)->first();
            $query->when($category, fn ($q) => $q->where('article_category_id', $category->id));
        }

        $articles   = $query->paginate(self::PER_PAGE)->withQueryString();
        $categories = ArticleCategory::orderBy('name')->get();
        $seo        = SeoService::forBlogIndex();

        return view('blog.index', compact('articles', 'categories', 'category', 'seo'));
    }

    public function show(Article $article): View
    {
        abort_unless(
            $article->status === 'published' && $article->published_at?->lessThanOrEqualTo(now()),
            404
        );

        $article->increment('views_count');

        $related = Article::query()
            ->published()
            ->where('id', '!=', $article->id)
            ->when($article->article_category_id, fn ($q) => $q->where('article_category_id', $article->article_category_id))
            ->latest('published_at')
            ->limit(3)
            ->get();

        $relatedCoins = collect();
        if (! empty($article->related_coin_slugs)) {
            $relatedCoins = Cryptocurrency::query()
                ->whereIn('slug', $article->related_coin_slugs)
                ->get(['name', 'slug', 'symbol', 'image_url', 'current_price']);
        }

        $seo = SeoService::forArticle($article);

        return view('blog.show', compact('article', 'related', 'relatedCoins', 'seo'));
    }
}
