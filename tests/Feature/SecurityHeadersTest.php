<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_it_adds_the_required_security_headers_to_standard_responses(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader(
            'Content-Security-Policy',
            "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://code.jquery.com https://www.google.com https://www.gstatic.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.bunny.net https://fonts.googleapis.com; connect-src 'self' https: https://www.google.com https://www.gstatic.com; img-src 'self' data: https:; font-src 'self' data: https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.bunny.net https://fonts.gstatic.com; object-src 'self'; frame-src 'self' https://www.google.com https://www.gstatic.com; base-uri 'self'; frame-ancestors 'self';"
        );
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_it_adds_hsts_for_secure_requests(): void
    {
        config()->set('security.strict_environments', ['testing']);
        config()->set('security.hsts.enabled', true);
        config()->set('security.hsts.max_age', 63072000);
        config()->set('security.hsts.include_subdomains', true);

        $response = $this->get('https://localhost/');

        $response->assertHeader('Strict-Transport-Security', 'max-age=63072000; includeSubDomains');
    }
}