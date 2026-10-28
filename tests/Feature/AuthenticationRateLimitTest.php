<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationRateLimitTest extends TestCase
{
    use RefreshDatabase;

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
