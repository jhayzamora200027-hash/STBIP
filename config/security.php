<?php

return [
    // Basic headers applied to all environments
    'basic' => [
        'x_frame_options' => 'SAMEORIGIN',
        'x_content_type_options' => 'nosniff',
        'referrer_policy' => 'strict-origin-when-cross-origin',
    ],

    // Shared CSP used by both server snippets and application middleware.
    'content_security_policy' => implode(' ', [
        "default-src 'self';",
        "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://code.jquery.com https://www.google.com https://www.gstatic.com;",
        "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.bunny.net https://fonts.googleapis.com;",
        "connect-src 'self' https: https://www.google.com https://www.gstatic.com;",
        "img-src 'self' data: https:;",
        "font-src 'self' data: https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.bunny.net https://fonts.gstatic.com;",
        "object-src 'self';",
        "frame-src 'self' https://www.google.com https://www.gstatic.com;",
        "base-uri 'self';",
        "frame-ancestors 'self';",
    ]),

    // Permissions/feature policy default
    'permissions_policy' => "geolocation=(), microphone=(), camera=(), payment=(), usb=()",

    // Cross-origin and resource policies
    'cross_origin' => [
        'coop' => 'same-origin',
        'coep' => 'require-corp',
        'corp' => 'same-origin',
    ],

    // HSTS (only effective when request is secure). Toggle via env for production/staging.
    'hsts' => [
        'enabled' => env('SECURITY_HSTS', true),
        'max_age' => env('SECURITY_HSTS_MAX_AGE', 63072000),
        'include_subdomains' => env('SECURITY_HSTS_INCLUDE_SUBDOMAINS', true),
    ],

    // Adobe cross-domain policy header
    'x_permitted_cross_domain_policies' => 'none',

    // Legacy XSS protection header (optional)
    'x_xss_protection' => env('SECURITY_LEGACY_XSS_PROTECTION', false),

    // Which environments should receive the strict, production-grade headers
    'strict_environments' => ['production', 'staging'],
];
