<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_responses_include_security_headers_and_a_script_restrictive_csp(): void
    {
        $response = $this->get(route('policies.privacy'));

        $response
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertHeader('Permissions-Policy', 'camera=(), geolocation=(), microphone=()')
            ->assertHeader('Content-Security-Policy', "default-src 'self'; base-uri 'self'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; img-src 'self' data:; font-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'; connect-src 'self'");

        $configuredSecrets = array_filter([
            config('app.key'),
            config('app.admin_seed.password'),
        ]);

        foreach ($configuredSecrets as $secret) {
            self::assertFalse(
                str_contains($response->getContent(), $secret),
                'A private configuration value was included in a public response.',
            );
        }
    }

    public function test_production_hsts_is_only_sent_on_https_responses(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');

        $this->call('GET', 'http://clinic.test/privacy-policy')
            ->assertOk()
            ->assertHeaderMissing('Strict-Transport-Security');

        $this->call('GET', 'https://clinic.test/privacy-policy')
            ->assertOk()
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000');
    }
}
