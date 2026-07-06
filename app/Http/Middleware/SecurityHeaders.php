<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Baseline security headers only. Deliberately no Content-Security-Policy
     * here yet: the site relies on inline event handlers, Alpine.js inline
     * expressions and third-party scripts (Google Fonts, lightweight-charts
     * via unpkg, CoinGecko fetch calls, Reverb websocket). A CSP has to be
     * authored once the final list of ad-network script/frame/connect
     * domains (e.g. Raptive) is known, otherwise it would need to be
     * reworked immediately after onboarding.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'interest-cohort=(), browsing-topics=()');

        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
