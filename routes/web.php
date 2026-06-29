<?php

use App\Http\Controllers\CompareController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\CryptoController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\StaticPageController;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Spatie\Sitemap\SitemapGenerator;

// ── Language switcher ───────────────────────────────────────────────────────
Route::get('/lang/{locale}', [LocaleController::class, 'switch'])
    ->where('locale', 'en|fr|ar|es|de|pt')
    ->name('locale.switch');

// ── Homepage ────────────────────────────────────────────────────────────────
Route::get('/', [CryptoController::class, 'index'])->name('crypto.index');
Route::get('/currencies/{slug}', [CryptoController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')->name('crypto.show');
Route::get('/crypto/{slug}-price', [CryptoController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')->name('crypto.show.seo');

// ── Compare Tool ────────────────────────────────────────────────────────────
Route::get('/compare', [CompareController::class, 'chooser'])->name('crypto.compare.chooser');
Route::get('/compare/{slugA}-vs-{slugB}', [CompareController::class, 'show'])
    ->where(['slugA' => '[a-z0-9\-]+', 'slugB' => '[a-z0-9\-]+'])
    ->name('crypto.compare');

// ── Market Analytics ────────────────────────────────────────────────────────
Route::get('/gainers',                [MarketController::class, 'gainers'])->name('market.gainers');
Route::get('/losers',                 [MarketController::class, 'losers'])->name('market.losers');
Route::get('/trending',               [MarketController::class, 'trending'])->name('market.trending');

// SEO aliases
Route::get('/best-performing-coins',  [MarketController::class, 'gainers'])->name('market.best-performing');
Route::get('/worst-performing-coins', [MarketController::class, 'losers'])->name('market.worst-performing');
Route::get('/trending-cryptocurrencies', [MarketController::class, 'trending'])->name('market.trending-seo');

// ── SEO pages (Phase 6) ─────────────────────────────────────────────────────
Route::get('/fear-and-greed-index',   [MarketController::class, 'fearGreed'])->name('market.fear-greed');
Route::get('/bitcoin-dominance',      [MarketController::class, 'bitcoinDominance'])->name('market.bitcoin-dominance');
Route::get('/crypto-market-cap',      [MarketController::class, 'globalMarketCap'])->name('market.global-cap');
Route::get('/global-crypto-volume',   [MarketController::class, 'globalMarketCap'])->name('market.global-volume');

// ── News ────────────────────────────────────────────────────────────────────
Route::get('/news',        [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{news}', [NewsController::class, 'show'])->name('news.show');

// ── Newsletter ──────────────────────────────────────────────────────────────
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');

// ── Static pages ────────────────────────────────────────────────────────────
Route::get('/about',                [StaticPageController::class, 'about'])->name('pages.about');
Route::get('/our-data-methodology', [StaticPageController::class, 'methodology'])->name('pages.methodology');
Route::get('/privacy-policy',       [StaticPageController::class, 'privacy'])->name('pages.privacy');
Route::get('/terms-of-service',     [StaticPageController::class, 'terms'])->name('pages.terms');
Route::get('/contact',              [StaticPageController::class, 'contact'])->name('pages.contact');
Route::post('/contact',             [StaticPageController::class, 'submitContact'])->name('pages.contact.submit');

// ── API docs ────────────────────────────────────────────────────────────────
Route::get('/api-docs', fn () => view('api.docs'))->name('api.docs');

// ── Sitemap ─────────────────────────────────────────────────────────────────
Route::get('/sitemap.xml', function () {
    $path = storage_path('app/public/sitemap.xml');
    if (! file_exists($path) || filemtime($path) < now()->subHours(12)->timestamp) {
        SitemapGenerator::create(config('app.url'))->writeToFile($path);
    }
    return Response::make(file_get_contents($path), 200, ['Content-Type' => 'application/xml']);
});

Route::get('/robots.txt', fn () => Response::make(
    "User-agent: *\nAllow: /\nSitemap: " . url('/sitemap.xml') . "\n",
    200, ['Content-Type' => 'text/plain']
));

