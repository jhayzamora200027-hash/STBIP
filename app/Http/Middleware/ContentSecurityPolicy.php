<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request and add a restrictive CSP header.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Defense-in-depth CSP. Adjust allowed hosts for external CDNs if needed.
        $policy = "default-src 'self'; ";
        $policy .= "script-src 'self' https: 'unsafe-inline'; ";
        $policy .= "style-src 'self' 'unsafe-inline' https:; ";
        $policy .= "img-src 'self' data: https:; ";
        $policy .= "font-src 'self' https: data:; ";
        $policy .= "object-src 'none'; base-uri 'self'; frame-ancestors 'none';";

        $response->headers->set('Content-Security-Policy', $policy);

        return $response;
    }
}
