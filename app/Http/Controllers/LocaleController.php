<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    private const SUPPORTED = ['en', 'fr', 'ar', 'es', 'de', 'pt'];

    public function switch(Request $request, string $locale): RedirectResponse
    {
        if (in_array($locale, self::SUPPORTED, true)) {
            Session::put('locale', $locale);
        }

        $back = $request->headers->get('referer', route('crypto.index'));

        return redirect($back);
    }
}
