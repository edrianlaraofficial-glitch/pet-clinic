<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class FirstAdminSetupController extends Controller
{
    private const LOCK_KEY = 'first-admin-setup';

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->isSetupAvailable(), 404);

        $configuredKey = config('app.admin_setup_key');
        $providedKey = $request->request->get('setup_key');
        $request->request->remove('setup_key');
        $request->query->remove('setup_key');

        abort_unless(
            is_string($configuredKey)
                && is_string($providedKey)
                && hash_equals($configuredKey, $providedKey),
            404,
        );

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        $lock = Cache::store('database')->lock(self::LOCK_KEY, 30);

        if (! $lock->get()) {
            return back()->withErrors([
                'setup' => 'Administrator setup is already in progress. Please try again.',
            ]);
        }

        try {
            $admin = DB::transaction(function () use ($validated): User {
                $setupStatus = DB::table('admin_setup_statuses')
                    ->where('id', 1)
                    ->lockForUpdate()
                    ->first();

                abort_unless(
                    $setupStatus && $setupStatus->completed_at === null && ! $this->adminExists(),
                    404,
                );

                if (User::query()->where('email', $validated['email'])->exists()) {
                    throw ValidationException::withMessages([
                        'email' => 'An account already uses that email address. Choose another email.',
                    ]);
                }

                $admin = User::query()->create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'email_verified_at' => now(),
                ]);
                $admin->profile()->create(['full_name' => $validated['name']]);
                $admin->grantRole(UserRole::ADMIN);

                $completed = DB::table('admin_setup_statuses')
                    ->where('id', 1)
                    ->whereNull('completed_at')
                    ->update(['completed_at' => now()]);

                if ($completed !== 1) {
                    throw new RuntimeException('The first-admin setup status could not be completed.');
                }

                return $admin;
            });
        } finally {
            $lock->release();
        }

        Auth::guard('web')->logout();
        Auth::guard('admin')->login($admin);
        $request->session()->regenerate();

        return to_route('admin.dashboard')
            ->with('status', 'Your administrator account is ready.');
    }

    public function isSetupAvailable(): bool
    {
        $setupKey = config('app.admin_setup_key');

        if (! is_string($setupKey) || strlen($setupKey) < 32) {
            return false;
        }

        return DB::table('admin_setup_statuses')
            ->where('id', 1)
            ->whereNull('completed_at')
            ->exists()
            && ! $this->adminExists();
    }

    private function adminExists(): bool
    {
        return UserRole::query()->where('role', UserRole::ADMIN)->exists();
    }
}
