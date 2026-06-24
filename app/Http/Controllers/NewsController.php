<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class NewsController extends Controller
{
    private const int CACHE_TTL = 600;

    public function index(Request $request): View
    {
        $page = max(1, (int) $request->get('page', 1));

        $news = Cache::remember("news_page_{$page}", self::CACHE_TTL, fn () =>
            News::orderByDesc('published_at')
                ->paginate(20, ['*'], 'page', $page)
                ->toArray()
        );

        // Re-build paginator from cached array
        $items    = collect($news['data'])->map(fn (array $r) => (new News())->forceFill($r));
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items, $news['total'], $news['per_page'], $page,
            ['path' => route('news.index'), 'query' => $request->query()]
        );

        return view('news.index', ['news' => $paginator]);
    }

    public function show(News $news): View
    {
        $news->increment('views_count');

        $related = Cache::remember("news_related_{$news->slug}", self::CACHE_TTL, fn () =>
            News::where('id', '!=', $news->id)
                ->orderByDesc('published_at')
                ->limit(4)
                ->get()
                ->toArray()
        );

        $relatedItems = collect($related)->map(fn (array $r) => (new News())->forceFill($r));

        return view('news.show', compact('news', 'relatedItems'));
    }
}
