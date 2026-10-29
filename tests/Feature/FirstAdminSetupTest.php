<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FirstAdminSetupTest extends TestCase
{
    use RefreshDatabase;

    private const SETUP_KEY = 'test-first-admin-setup-key-0123456789abcdef';

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.admin_setup_key' => self::SETUP_KEY]);
    }

    public function test_setup_page_shows_welcome_only_when_enabled_and_no_admin_exists(): void
    {
        $this->get(route('admin.setup'))
            ->assertOk()
            ->assertSee('Welcome, Admin! Create your first administrator account to get started.')
            ->assertSee('name="setup_key"', false);

        $admin = $this->createUser('existing-admin@example.test');
        $admin->grantRole(UserRole::ADMIN);

        $this->get(route('admin.setup'))->assertNotFound();
    }

    public function test_setup_page_is_unavailable_without_a_configured_secret(): void
    {
        config(['app.admin_setup_key' => null]);

        $this->get(route('admin.setup'))->assertNotFound();
    }

    public function test_valid_setup_creates_and_signs_in_the_first_admin(): void
    {
        $response = $this->post(route('admin.setup.store'), [
            'name' => 'First Admin',
            'email' => 'first-admin@example.test',
            'password' => 'first-admin-password-123',
            'password_confirmation' => 'first-admin-password-123',
            'setup_key' => self::SETUP_KEY,
        ]);

        $admin = User::query()->where('email', 'first-admin@example.test')->firstOrFail();

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue(Hash::check('first-admin-password-123', $admin->password));
        $this->assertNotNull($admin->email_verified_at);
        $this->assertDatabaseHas('profiles', [
            'user_id' => $admin->getKey(),
            'full_name' => 'First Admin',
        ]);
        $this->assertNotNull(DB::table('admin_setup_statuses')->value('completed_at'));
    }

    public function test_setup_rejects_an_incorrect_secret_without_creating_an_admin(): void
    {
        $this->post(route('admin.setup.store'), [
            'name' => 'Attacker',
            'email' => 'attacker@example.test',
            'password' => 'attacker-password-123',
            'password_confirmation' => 'attacker-password-123',
            'setup_key' => 'incorrect-key-0123456789abcdef',
        ])->assertNotFound();

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('user_roles', 0);
    }

    public function test_setup_key_in_the_url_is_not_accepted(): void
    {
        $this->post(route('admin.setup.store', ['setup_key' => self::SETUP_KEY]), [
            'name' => 'Attacker',
            'email' => 'attacker@example.test',
            'password' => 'attacker-password-123',
            'password_confirmation' => 'attacker-password-123',
        ])->assertNotFound();

        $this->assertDatabaseCount('users', 0);
    }

    public function test_setup_does_not_promote_an_existing_customer_account(): void
    {
        $customer = $this->createUser('customer@example.test');

        $this->from(route('admin.setup'))
            ->post(route('admin.setup.store'), [
                'name' => 'Customer',
                'email' => $customer->email,
                'password' => 'customer-password-123',
                'password_confirmation' => 'customer-password-123',
                'setup_key' => self::SETUP_KEY,
            ])
            ->assertRedirect(route('admin.setup'))
            ->assertSessionHasErrors('email');

        $this->assertFalse($customer->fresh()->isAdmin());
        $this->assertDatabaseCount('user_roles', 0);
    }

    public function test_setup_validates_required_fields_and_password_confirmation(): void
    {
        $this->from(route('admin.setup'))
            ->post(route('admin.setup.store'), [
                'setup_key' => self::SETUP_KEY,
                'name' => 'First Admin',
                'email' => 'first-admin@example.test',
                'password' => 'short',
                'password_confirmation' => 'different',
            ])
            ->assertRedirect(route('admin.setup'))
            ->assertSessionHasErrors(['password']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_setup_requires_name_email_and_password(): void
    {
        $this->from(route('admin.setup'))
            ->post(route('admin.setup.store'), ['setup_key' => self::SETUP_KEY])
            ->assertRedirect(route('admin.setup'))
            ->assertSessionHasErrors(['name', 'email', 'password']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_setup_returns_an_error_while_another_request_holds_the_setup_lock(): void
    {
        $lock = Cache::store('database')->lock('first-admin-setup', 30);
        $this->assertTrue($lock->get());

        $this->from(route('admin.setup'))
            ->post(route('admin.setup.store'), [
                'name' => 'First Admin',
                'email' => 'first-admin@example.test',
                'password' => 'first-admin-password-123',
                'password_confirmation' => 'first-admin-password-123',
                'setup_key' => self::SETUP_KEY,
            ])
            ->assertRedirect(route('admin.setup'))
            ->assertSessionHasErrors('setup');

        $lock->release();

        $this->assertDatabaseCount('users', 0);
    }

    public function test_setup_remains_closed_after_the_first_admin_role_is_removed(): void
    {
        $this->post(route('admin.setup.store'), [
            'name' => 'First Admin',
            'email' => 'first-admin@example.test',
            'password' => 'first-admin-password-123',
            'password_confirmation' => 'first-admin-password-123',
            'setup_key' => self::SETUP_KEY,
        ])->assertRedirect(route('admin.dashboard'));

        $admin = User::query()->where('email', 'first-admin@example.test')->firstOrFail();
        $admin->roles()->delete();

        $this->get(route('admin.setup'))->assertNotFound();
        $this->post(route('admin.setup.store'), [
            'name' => 'Second Admin',
            'email' => 'second-admin@example.test',
            'password' => 'second-admin-password-123',
            'password_confirmation' => 'second-admin-password-123',
            'setup_key' => self::SETUP_KEY,
        ])->assertNotFound();

        $this->assertDatabaseCount('users', 1);
    }

    private function createUser(string $email): User
    {
        return User::query()->create([
            'name' => 'Clinic User',
            'email' => $email,
            'password' => 'existing-password',
        ]);
    }
}
