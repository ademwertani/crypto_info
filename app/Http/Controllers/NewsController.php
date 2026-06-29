<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Services\NewsApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class NewsController extends Controller
{
    private const CACHE_TTL = 600;

    public function index(Request $request, NewsApiService $newsApi): View
    {
        $page = max(1, (int) $request->get('page', 1));

        if (! Schema::hasTable('news')) {
            return view('news.index', ['news' => $this->emptyPaginator($request, $page)]);
        }

        $hasNews = News::query()->exists();
        if (! $hasNews) {
            $fallbackArticles = $newsApi->fetchLatest(12);
            if (! empty($fallbackArticles)) {
                foreach ($fallbackArticles as $article) {
                    News::updateOrCreate(
                        ['slug' => $article['slug']],
                        $article
                    );
                }

                Cache::forget('news_page_1');
                Cache::forget('news_page_2');
                Cache::forget('news_page_3');
            }
        }

        $news = Cache::remember("news_page_{$page}", self::CACHE_TTL, function () use ($page) {
            $query = News::query()->orderByDesc('published_at')->orderByDesc('id');
            $paginator = $query->paginate(20, ['*'], 'page', $page)->withQueryString();

            return [
                'data' => $paginator->getCollection()->toArray(),
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
            ];
        });

        if (empty($news['data'])) {
            $fallbackArticles = $newsApi->fetchLatest(12);
            if (! empty($fallbackArticles)) {
                foreach ($fallbackArticles as $article) {
                    News::updateOrCreate(
                        ['slug' => $article['slug']],
                        $article
                    );
                }

                Cache::forget("news_page_{$page}");
                Cache::forget('news_page_1');
                Cache::forget('news_page_2');
                Cache::forget('news_page_3');

                return $this->index($request, $newsApi);
            }
        }

        $items = collect($news['data'])->map(fn (array $r) => (new News())->forceFill($r));
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items, $news['total'], $news['per_page'], $page,
            ['path' => route('news.index'), 'query' => $request->query()]
        );

        return view('news.index', ['news' => $paginator]);
    }

    public function show(News $news): View
    {
        if (Schema::hasColumn('news', 'views_count')) {
            $news->setAttribute('views_count', (int) ($news->views_count ?? 0) + 1);
            $news->save();
        }

        $related = Cache::remember("news_related_{$news->slug}", self::CACHE_TTL, fn () =>
            News::query()->where('id', '!=', $news->id)
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->limit(4)
                ->get()
                ->toArray()
        );

        $relatedItems = collect($related)->map(fn (array $r) => (new News())->forceFill($r));

        return view('news.show', compact('news', 'relatedItems'));
    }

    private function emptyPaginator(Request $request, int $page): \Illuminate\Pagination\LengthAwarePaginator
    {
        return new \Illuminate\Pagination\LengthAwarePaginator(
            collect(),
            0,
            20,
            $page,
            ['path' => route('news.index'), 'query' => $request->query()]
        );
    }
}
