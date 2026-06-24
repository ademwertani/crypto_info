<?php

use App\Http\Controllers\Api\CoinController;
use Illuminate\Support\Facades\Route;

// Phase 9 — Internal REST API
// Rate-limited: 60 req/min per IP
Route::middleware(['throttle:60,1'])->prefix('coins')->group(function () {
    Route::get('/',           [CoinController::class, 'index'])->name('api.coins.index');
    Route::get('/{slug}',     [CoinController::class, 'show'])->name('api.coins.show');
});

Route::middleware(['throttle:60,1'])->group(function () {
    Route::get('/gainers',  [CoinController::class, 'gainers'])->name('api.gainers');
    Route::get('/losers',   [CoinController::class, 'losers'])->name('api.losers');
    Route::get('/trending', [CoinController::class, 'trending'])->name('api.trending');
});
