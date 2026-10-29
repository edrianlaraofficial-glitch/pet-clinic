<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminCredentialsTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_page_separates_profile_and_password_settings(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.account.index'))
            ->assertOk()
            ->assertSee('id="account-profile"', false)
            ->assertSee('Personal information')
            ->assertSee('Change password')
            ->assertSee('name="name"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="current_password"', false)
            ->assertSee('name="password_confirmation"', false);
    }

    public function test_admin_can_change_password_after_confirming_current_password(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.password.update'), [
                'current_password' => 'current-password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect(route('admin.account.index'))
            ->assertSessionHas('status', 'Your admin password was updated.');

        $this->assertTrue(Hash::check('new-password-123', $admin->fresh()->password));
        $this->assertNotSame('previous-remember-token', $admin->fresh()->remember_token);
    }

    public function test_admin_password_change_rejects_an_incorrect_current_password(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->from(route('admin.account.index'))
            ->put(route('admin.password.update'), [
                'current_password' => 'incorrect-password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect(route('admin.account.index'))
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('current-password', $admin->fresh()->password));
    }

    public function test_admin_password_change_requires_a_confirmed_minimum_length_password(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->from(route('admin.account.index'))
            ->put(route('admin.password.update'), [
                'current_password' => 'current-password',
                'password' => 'short',
                'password_confirmation' => 'different',
            ])
            ->assertRedirect(route('admin.account.index'))
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('current-password', $admin->fresh()->password));
    }

    public function test_admin_can_update_personal_information_after_confirming_current_password(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.profile.update'), [
                'name' => 'Wade Admin',
                'email' => $admin->email,
                'current_password' => 'current-password',
            ])
            ->assertRedirect(route('admin.account.index'))
            ->assertSessionHas('status', 'Your personal information was updated.');

        $this->assertSame('Wade Admin', $admin->fresh()->name);
        $this->assertSame('admin@example.test', $admin->fresh()->email);
    }

    public function test_admin_profile_update_rejects_wrong_current_password(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->from(route('admin.account.index'))
            ->put(route('admin.profile.update'), [
                'name' => 'Changed Name',
                'email' => 'new-admin@example.test',
                'current_password' => 'incorrect-password',
            ])
            ->assertRedirect(route('admin.account.index'))
            ->assertSessionHasErrors('current_password');

        $this->assertSame('Clinic Admin', $admin->fresh()->name);
        $this->assertSame('admin@example.test', $admin->fresh()->email);
        $this->assertTrue(Hash::check('current-password', $admin->fresh()->password));
    }

    public function test_admin_email_change_does_not_require_email_verification(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.profile.update'), [
                'name' => $admin->name,
                'email' => 'new-admin@example.test',
                'current_password' => 'current-password',
            ])
            ->assertRedirect(route('admin.account.index'))
            ->assertSessionHas('status', 'Your personal information was updated.');

        $updatedAdmin = $admin->fresh();
        $this->assertSame('new-admin@example.test', $updatedAdmin->email);
        $this->assertNull($updatedAdmin->email_verified_at);
        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_guest_cannot_update_admin_profile_or_password(): void
    {
        $this->put(route('admin.profile.update'), [
            'name' => 'Wade Admin',
            'email' => 'new-admin@example.test',
            'current_password' => 'current-password',
        ])->assertRedirect(route('admin.login'));

        $this->put(route('admin.password.update'), [
            'current_password' => 'current-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertRedirect(route('admin.login'));
    }

    public function test_admin_email_must_be_unique(): void
    {
        $admin = $this->admin();
        User::query()->create([
            'name' => 'Other User',
            'email' => 'other@example.test',
            'password' => 'password',
        ]);

        $this->actingAs($admin, 'admin')
            ->from(route('admin.account.index'))
            ->put(route('admin.profile.update'), [
                'name' => $admin->name,
                'email' => 'other@example.test',
                'current_password' => 'current-password',
            ])
            ->assertRedirect(route('admin.account.index'))
            ->assertSessionHasErrors('email');

        $this->assertSame('admin@example.test', $admin->fresh()->email);
    }

    private function admin(): User
    {
        $admin = User::query()->create([
            'name' => 'Clinic Admin',
            'email' => 'admin@example.test',
            'password' => 'current-password',
            'remember_token' => 'previous-remember-token',
        ]);
        $admin->grantRole(UserRole::ADMIN);

        return $admin;
    }
}
