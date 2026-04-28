<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;

class ForceHttps
{
    /**
     * Redirect HTTP to HTTPS and add HSTS header.
     */
    public function handle($request, Closure $next)
    {
        if (!$request->secure()) {
            return redirect()->secure($request->getRequestUri());
        }

        $response = $next($request);

        if ($response instanceof RedirectResponse) {
            return $response;
        }

        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

        return $response;
    }
}
