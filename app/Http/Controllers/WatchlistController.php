<?php

namespace App\Http\Controllers;

use App\Models\Cryptocurrency;
use App\Models\PriceAlert;
use App\Models\Watchlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WatchlistController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        $watchlistIds = Watchlist::where('user_id', auth()->id())
            ->pluck('cryptocurrency_id');

        $coins = Cryptocurrency::whereIn('id', $watchlistIds)
            ->orderBy('market_cap_rank')
            ->get();

        $alerts = PriceAlert::where('user_id', auth()->id())
            ->where('triggered', false)
            ->with('cryptocurrency')
            ->get();

        return view('watchlist.index', compact('coins', 'alerts'));
    }

    public function toggle(Request $request): RedirectResponse
    {
        $request->validate(['slug' => 'required|string|exists:cryptocurrencies,slug']);

        $coin = Cryptocurrency::where('slug', $request->slug)->firstOrFail();
        $userId = auth()->id();

        $existing = Watchlist::where('user_id', $userId)
            ->where('cryptocurrency_id', $coin->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $message = "{$coin->name} removed from watchlist.";
        } else {
            Watchlist::create(['user_id' => $userId, 'cryptocurrency_id' => $coin->id]);
            $message = "{$coin->name} added to watchlist.";
        }

        return back()->with('status', $message);
    }

    public function storeAlert(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'cryptocurrency_id' => 'required|exists:cryptocurrencies,id',
            'direction'         => 'required|in:above,below',
            'target_price'      => 'required|numeric|min:0',
        ]);

        PriceAlert::create(array_merge($data, ['user_id' => auth()->id()]));

        return back()->with('status', 'Price alert created.');
    }

    public function destroyAlert(PriceAlert $alert): RedirectResponse
    {
        $this->authorize('delete', $alert);
        $alert->delete();
        return back()->with('status', 'Alert deleted.');
    }
}
