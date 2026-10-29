<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Transport\ResendTransport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_resend_mailer_uses_laravels_resend_transport(): void
    {
        config(['services.resend.key' => 're_test_key']);

        $this->assertInstanceOf(
            ResendTransport::class,
            Mail::mailer('resend')->getSymfonyTransport(),
        );
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

    public function test_repeated_verification_email_requests_are_throttled(): void
    {
        Notification::fake();
        User::query()->create([
            'name' => 'Unverified Customer',
            'email' => 'unverified@example.test',
            'password' => 'password',
        ]);

        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->post(route('verification.send'), [
                'email' => 'unverified@example.test',
            ])->assertRedirect();
        }

        $this->post(route('verification.send'), [
            'email' => 'unverified@example.test',
        ])->assertTooManyRequests();

        Notification::assertSentToTimes(User::query()->where('email', 'unverified@example.test')->firstOrFail(), VerifyEmail::class, 6);
    }
}
