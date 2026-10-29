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

    public function test_asset_urls_use_the_forwarded_https_scheme_without_trusting_forwarded_host(): void
    {
        $response = $this->withServerVariables([
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_HOST' => 'untrusted.example',
        ])->get('http://clinic.test/privacy-policy');

        $response
            ->assertOk()
            ->assertSee('href="https://clinic.test/build/assets/app-', false)
            ->assertSee('href="https://clinic.test/css/app.css"', false)
            ->assertSee('href="https://clinic.test/favicon.png"', false)
            ->assertSee('src="https://clinic.test/js/app.js"', false)
            ->assertDontSee('href="http://clinic.test/build/assets/app-', false)
            ->assertDontSee('http://clinic.test/css/app.css', false)
            ->assertDontSee('untrusted.example', false);
    }

    public function test_asset_urls_preserve_http_for_local_requests_without_forwarded_scheme(): void
    {
        $response = $this->get('http://clinic.test/privacy-policy');

        $response
            ->assertOk()
            ->assertSee('href="http://clinic.test/css/app.css"', false)
            ->assertSee('href="http://clinic.test/favicon.png"', false)
            ->assertSee('src="http://clinic.test/js/app.js"', false);
    }
}
