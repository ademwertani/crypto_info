<?php

use App\Http\Controllers\AdvertiseController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\CryptoController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\MoneyPageController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PlatformComparisonController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StaticPageController;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

Route::get('/lang/{locale}', [LocaleController::class, 'switch'])
    ->where('locale', 'en|fr|ar|es|de|pt')
    ->name('locale.switch');

Route::get('/', [CryptoController::class, 'index'])->name('crypto.index');

Route::get('/currencies/{slug}', [CryptoController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('crypto.show');
Route::get('/crypto/{slug}-price', [CryptoController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('crypto.show.seo');

Route::get('/compare', [CompareController::class, 'chooser'])->name('crypto.compare.chooser');
Route::get('/compare/{slugA}-vs-{slugB}', [CompareController::class, 'show'])
    ->where(['slugA' => '[a-z0-9\-]+', 'slugB' => '[a-z0-9\-]+'])
    ->name('crypto.compare');

Route::get('/gainers', [MarketController::class, 'gainers'])->name('market.gainers');
Route::get('/losers', [MarketController::class, 'losers'])->name('market.losers');
Route::get('/trending', [MarketController::class, 'trending'])->name('market.trending');
Route::get('/best-performing-coins', [MarketController::class, 'gainers'])->name('market.best-performing');
Route::get('/worst-performing-coins', [MarketController::class, 'losers'])->name('market.worst-performing');
Route::get('/trending-cryptocurrencies', [MarketController::class, 'trending'])->name('market.trending-seo');
Route::get('/fear-and-greed-index', [MarketController::class, 'fearGreed'])->name('market.fear-greed');
Route::get('/bitcoin-dominance', [MarketController::class, 'bitcoinDominance'])->name('market.bitcoin-dominance');
Route::get('/crypto-market-cap', [MarketController::class, 'globalMarketCap'])->name('market.global-cap');
Route::get('/global-crypto-volume', [MarketController::class, 'globalMarketCap'])->name('market.global-volume');

Route::get('/compare-platforms', [PlatformComparisonController::class, 'index'])->name('platforms.compare');

Route::get('/blog', [ArticleController::class, 'index'])->name('blog.index');
Route::get('/blog/{article:slug}', [ArticleController::class, 'show'])
    ->where('article', '[a-z0-9\-]+')
    ->name('blog.show');

Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{news:slug}', [NewsController::class, 'show'])
    ->where('news', '[a-z0-9\-]+')
    ->name('news.show');

Route::get('/guides', [MoneyPageController::class, 'index'])->name('guides.index');
Route::get('/guides/{moneyPage:slug}', [MoneyPageController::class, 'show'])
    ->where('moneyPage', '[a-z0-9\-]+')
    ->name('guides.show');
Route::get('/guides/{moneyPage:slug}/preview', [MoneyPageController::class, 'preview'])
    ->where('moneyPage', '[a-z0-9\-]+')
    ->name('guides.preview');

Route::get('/about', [StaticPageController::class, 'about'])->name('pages.about');
Route::get('/privacy-policy', [StaticPageController::class, 'privacy'])->name('pages.privacy');
Route::get('/terms-of-service', [StaticPageController::class, 'terms'])->name('pages.terms');

Route::get('/advertise', [AdvertiseController::class, 'show'])->name('advertise.show');
Route::post('/advertise', [AdvertiseController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('advertise.store');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/robots.txt', fn () => Response::make(
    "User-agent: *\nDisallow: /lang/\nSitemap: ".url('/sitemap.xml')."\n",
    200,
    ['Content-Type' => 'text/plain']
));
