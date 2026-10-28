<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserRole;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class AdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seed_creates_a_new_admin_with_a_hashed_configured_password(): void
    {
        config([
            'app.admin_seed.email' => 'seed-admin@example.test',
            'app.admin_seed.password' => 'local-test-password',
        ]);

        $this->seed(AdminSeeder::class);

        $admin = User::query()->where('email', 'seed-admin@example.test')->firstOrFail();

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue(Hash::check('local-test-password', $admin->password));
        $this->assertNotNull($admin->email_verified_at);
    }

    public function test_seed_refuses_to_promote_an_existing_customer_account(): void
    {
        config([
            'app.admin_seed.email' => 'registered-customer@example.test',
            'app.admin_seed.password' => 'configured-seed-password',
        ]);
        $customer = User::query()->create([
            'name' => 'Registered customer',
            'email' => 'registered-customer@example.test',
            'password' => Hash::make('customer-password'),
        ]);

        try {
            $this->seed(AdminSeeder::class);
            self::fail('The seeder should refuse to promote an existing customer.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('already belongs to a non-admin account', $exception->getMessage());
        }

        $this->assertFalse($customer->fresh()->isAdmin());
        $this->assertTrue(Hash::check('customer-password', $customer->fresh()->password));
        $this->assertDatabaseMissing('user_roles', [
            'user_id' => $customer->getKey(),
            'role' => UserRole::ADMIN,
        ]);
    }

    public function test_seed_is_idempotent_for_an_already_provisioned_admin(): void
    {
        config([
            'app.admin_seed.email' => 'existing-admin@example.test',
            'app.admin_seed.password' => 'must-not-replace-password',
        ]);
        $admin = User::query()->create([
            'name' => 'Existing administrator',
            'email' => 'existing-admin@example.test',
            'password' => Hash::make('existing-password'),
        ]);
        $admin->grantRole(UserRole::ADMIN);

        $this->seed(AdminSeeder::class);

        $this->assertTrue($admin->fresh()->isAdmin());
        $this->assertTrue(Hash::check('existing-password', $admin->fresh()->password));
        $this->assertDatabaseCount('user_roles', 1);
    }
}
