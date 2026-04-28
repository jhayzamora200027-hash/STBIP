<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;


class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Basic headers applied everywhere
        $basic = Config::get('security.basic', []);
        if (!empty($basic['x_frame_options'])) {
            $response->headers->set('X-Frame-Options', $basic['x_frame_options']);
        }
        if (!empty($basic['x_content_type_options'])) {
            $response->headers->set('X-Content-Type-Options', $basic['x_content_type_options']);
        }
        if (!empty($basic['referrer_policy'])) {
            $response->headers->set('Referrer-Policy', $basic['referrer_policy']);
        }

        // Permissions policy (feature policy)
        $pp = Config::get('security.permissions_policy');
        if ($pp) {
            $response->headers->set('Permissions-Policy', $pp, true);
        }

        // Adobe cross-domain policy
        $xperm = Config::get('security.x_permitted_cross_domain_policies');
        if ($xperm) {
            $response->headers->set('X-Permitted-Cross-Domain-Policies', $xperm, true);
        }

        // Apply stricter cross-origin isolation only in configured strict environments
        $strictEnvs = Config::get('security.strict_environments', []);
        $isStrictEnv = in_array(app()->environment(), $strictEnvs, true);
        if ($isStrictEnv) {
            $cross = Config::get('security.cross_origin', []);
            if (!empty($cross['coop'])) {
                $response->headers->set('Cross-Origin-Opener-Policy', $cross['coop'], true);
            }
            if (!empty($cross['coep'])) {
                $response->headers->set('Cross-Origin-Embedder-Policy', $cross['coep'], true);
            }
            if (!empty($cross['corp'])) {
                $response->headers->set('Cross-Origin-Resource-Policy', $cross['corp'], true);
            }

            // HSTS: only set when request is secure and HSTS enabled in config
            $hstsCfg = Config::get('security.hsts', []);
            if ($request->secure() && !empty($hstsCfg['enabled'])) {
                $max = !empty($hstsCfg['max_age']) ? (int) $hstsCfg['max_age'] : 0;
                $inc = !empty($hstsCfg['include_subdomains']);
                $value = 'max-age=' . $max;
                if ($inc) $value .= '; includeSubDomains';
                $response->headers->set('Strict-Transport-Security', $value, true);
            }
        }

        // Legacy XSS protection header (opt-in via config)
        if (Config::get('security.x_xss_protection')) {
            $response->headers->set('X-XSS-Protection', '1; mode=block', true);
        }

        return $response;
    }
}
