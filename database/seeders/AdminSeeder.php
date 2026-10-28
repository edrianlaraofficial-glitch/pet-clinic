<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * Creates the first admin only when ADMIN_EMAIL and ADMIN_PASSWORD are set in .env.
 * Otherwise, sign up on the site and run:
 *   php artisan clinic:make-admin you@example.com
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('app.admin_seed.email');
        $password = config('app.admin_seed.password');

        if (! $email || ! $password) {
            $this->command?->warn('Skipping admin seed: set ADMIN_EMAIL and ADMIN_PASSWORD, or run clinic:make-admin.');

            return;
        }

        $user = User::query()->where('email', $email)->first();

        if ($user) {
            if (! $user->isAdmin()) {
                throw new RuntimeException('Admin seed email already belongs to a non-admin account. Use the explicit clinic:make-admin command after verifying the account owner.');
            }

            $this->command?->info('Configured admin account is already provisioned.');

            return;
        }

        $user = User::query()->create([
            'name' => 'Clinic Admin',
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);
        $user->grantRole(UserRole::ADMIN);

        $this->command?->info('Configured admin account was created.');
    }
}
