<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_link_request_does_not_disclose_whether_an_account_exists(): void
    {
        Notification::fake();
        $user = User::query()->create([
            'name' => 'Test customer',
            'email' => 'reset-customer@example.test',
            'password' => 'old-password',
        ]);
        $genericStatus = 'If an account exists for that email address, a password reset link will be sent.';

        $existingAccountResponse = $this->post(route('password.email'), ['email' => $user->email]);
        $unknownAccountResponse = $this->post(route('password.email'), ['email' => 'unknown@example.test']);

        $existingAccountResponse->assertRedirect()->assertSessionHas('status', $genericStatus);
        $unknownAccountResponse->assertRedirect()->assertSessionHas('status', $genericStatus);
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_valid_reset_token_changes_password_and_rotates_remember_token(): void
    {
        $user = User::query()->create([
            'name' => 'Test customer',
            'email' => 'reset-customer@example.test',
            'password' => 'old-password',
            'remember_token' => 'previous-remember-token',
        ]);
        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Your password has been reset. You can now sign in.');

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
        $this->assertNotSame('previous-remember-token', $user->fresh()->remember_token);
        $this->assertGuest();
    }

    public function test_invalid_reset_token_does_not_change_password(): void
    {
        $user = User::query()->create([
            'name' => 'Test customer',
            'email' => 'reset-customer@example.test',
            'password' => 'old-password',
        ]);

        $this->from(route('password.reset', ['token' => 'invalid-token']))
            ->post(route('password.update'), [
                'token' => 'invalid-token',
                'email' => $user->email,
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect(route('password.reset', ['token' => 'invalid-token']))
            ->assertSessionHasErrors([
                'email' => 'The password reset link is invalid or expired. Request a new link and try again.',
            ]);

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }
}
