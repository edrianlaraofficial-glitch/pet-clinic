<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('clinic:make-admin {email}')]
#[Description('Grant the admin role to an existing user')]
class MakeAdmin extends Command
{
    public function handle(): int
    {
        $user = User::query()->where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('No user was found with that email address.');

            return self::FAILURE;
        }

        $user->grantRole(UserRole::ADMIN);
        $this->info("Admin access granted to {$user->email}.");

        return self::SUCCESS;
    }
}
