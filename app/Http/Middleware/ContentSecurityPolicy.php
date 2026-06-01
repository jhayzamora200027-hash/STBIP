<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request and add a restrictive CSP header.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $managedEnvs = Config::get('security.app_managed_environments', ['local', 'testing']);

        if (!in_array(app()->environment(), $managedEnvs, true)) {
            return $response;
        }

        $policy = Config::get('security.content_security_policy');

        if ($policy) {
            $response->headers->set('Content-Security-Policy', $policy);
        }

        return $response;
    }
}
