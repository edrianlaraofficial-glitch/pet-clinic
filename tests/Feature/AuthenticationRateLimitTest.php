<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoApiTransport;
use Tests\TestCase;

class AuthenticationRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_brevo_mailer_builds_the_http_api_transport(): void
    {
        config(['mail.mailers.brevo.url' => 'brevo+api://test-key@default']);

        $this->assertInstanceOf(BrevoApiTransport::class, Mail::mailer('brevo')->getSymfonyTransport());
        $this->assertSame('edrianlara.official@gmail.com', config('mail.from.address'));
        $this->assertSame('Wade-County Animal Clinic', config('mail.from.name'));
    }

    public function test_repeated_customer_login_attempts_are_throttled(): void
    {
        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->post(route('auth.login'), [
                'email' => 'unknown@example.test',
                'password' => 'incorrect-password',
            ])->assertRedirect();
        }

        $this->post(route('auth.login'), [
            'email' => 'unknown@example.test',
            'password' => 'incorrect-password',
        ])->assertTooManyRequests();
    }
}
