<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_security_headers_are_present(): void
    {
        $this->get('/login')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_permissions_policy_is_present(): void
    {
        $this->get('/login')->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_hsts_only_over_https(): void
    {
        $this->get('https://localhost/login')->assertHeader('Strict-Transport-Security', 'max-age=31536000');
        $this->get('http://localhost/login')->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_csp_is_sent_in_report_only_mode(): void
    {
        $response = $this->get('/login');

        $policy = (string) $response->headers->get('Content-Security-Policy-Report-Only');
        $this->assertStringContainsString("default-src 'self'", $policy);
        $this->assertStringContainsString("script-src 'self';", $policy);
        $this->assertStringContainsString("object-src 'none'", $policy);
        $this->assertStringContainsString("frame-ancestors 'self'", $policy);
        $response->assertHeaderMissing('Content-Security-Policy'); // enforcing comes in SP2e
    }
}
