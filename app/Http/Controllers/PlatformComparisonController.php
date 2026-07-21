<?php

namespace App\Http\Controllers;

use App\Models\PlatformComparison;
use App\Services\SeoService;
use Illuminate\View\View;

class PlatformComparisonController extends Controller
{
    public function index(): View
    {
        $comparisons = PlatformComparison::query()
            ->published()
            ->with(['platformA', 'platformB'])
            ->orderBy('id')
            ->get();

        $seo = SeoService::forPlatformComparisonIndex();

        return view('platforms.compare-index', compact('comparisons', 'seo'));
    }
}
