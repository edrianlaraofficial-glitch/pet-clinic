<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessHour;
use App\Models\BusinessHourException;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function redirectLegacySettings(Request $request): RedirectResponse
    {
        return match ($request->query('section', 'services')) {
            'hours' => to_route('admin.hours.index'),
            'account' => to_route('admin.account.index'),
            default => to_route('admin.services.index'),
        };
    }

    public function services(): View
    {
        return view('admin.services', [
            'services' => Service::query()->orderBy('name')->get(),
        ]);
    }

    public function hours(): View
    {
        $savedHours = BusinessHour::query()->get()->keyBy('weekday');
        $hours = collect(range(0, 6))->map(function (int $weekday) use ($savedHours): BusinessHour {
            $hour = $savedHours->get($weekday);

            if ($hour instanceof BusinessHour) {
                return $hour;
            }

            $isClosed = $weekday === 0;

            return new BusinessHour([
                'weekday' => $weekday,
                'is_closed' => $isClosed,
                'opens_at' => $isClosed ? null : '09:00:00',
                'closes_at' => $isClosed ? null : '18:00:00',
            ]);
        });

        return view('admin.hours', [
            'hours' => $hours,
            'hasIncompleteSchedule' => collect(range(0, 6))
                ->contains(fn (int $weekday): bool => ! $savedHours->has($weekday)),
            'exceptions' => BusinessHourException::query()->orderBy('date')->get(),
        ]);
    }

    public function storeHours(): RedirectResponse
    {
        $timestamp = now();
        $hours = collect(range(0, 6))
            ->map(function (int $weekday) use ($timestamp): array {
                $isClosed = $weekday === 0;

                return [
                    'weekday' => $weekday,
                    'is_closed' => $isClosed,
                    'opens_at' => $isClosed ? null : '09:00:00',
                    'closes_at' => $isClosed ? null : '18:00:00',
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            })
            ->all();

        $createdDays = BusinessHour::query()->insertOrIgnore($hours);

        return to_route('admin.hours.index')->with(
            'status',
            $createdDays > 0
                ? 'The weekly schedule was created. Existing days were kept unchanged.'
                : 'The weekly schedule is already complete.',
        );
    }

    public function storeException(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date', 'date_format:Y-m-d'],
            'is_closed' => ['sometimes', 'boolean'],
            'opens_at' => ['nullable', 'date_format:H:i'],
            'closes_at' => ['nullable', 'date_format:H:i'],
            'reason' => ['nullable', 'string', 'max:120'],
        ]);

        $closed = (bool) ($validated['is_closed'] ?? false);

        if (! $closed) {
            if (empty($validated['opens_at']) || empty($validated['closes_at'])) {
                return back()->withErrors([
                    'opens_at' => 'Enter both opening and closing times for open days.',
                    'closes_at' => 'Enter both opening and closing times for open days.',
                ]);
            }

            $opensAt = CarbonImmutable::createFromFormat('!H:i', $validated['opens_at']);
            $closesAt = CarbonImmutable::createFromFormat('!H:i', $validated['closes_at']);

            if (! $closesAt->greaterThan($opensAt)) {
                return back()->withErrors([
                    'closes_at' => 'Closing time must be later than opening time.',
                ]);
            }

            $validated['opens_at'] = $opensAt->format('H:i:s');
            $validated['closes_at'] = $closesAt->format('H:i:s');
        } else {
            $validated['opens_at'] = null;
            $validated['closes_at'] = null;
        }

        BusinessHourException::updateOrCreate(
            ['date' => $validated['date']],
            $validated
        );

        return to_route('admin.hours.index')->with('status', 'Exception date was added.');
    }

    public function destroyException(BusinessHourException $exception): RedirectResponse
    {
        $exception->delete();

        return to_route('admin.hours.index')->with('status', 'Exception date was removed.');
    }

    public function account(): View
    {
        return view('admin.account');
    }

    public function storeService(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->serviceRules());

        Service::create($validated + ['is_active' => $request->boolean('is_active')]);

        return to_route('admin.services.index')->with('status', 'The service was added.');
    }

    public function updateService(Request $request, Service $service): RedirectResponse
    {
        $service->update($request->validate($this->serviceRules($service)));

        return to_route('admin.services.index')->with('status', 'The service was updated.');
    }

    public function toggleService(Service $service): RedirectResponse
    {
        $service->is_active = ! $service->is_active;
        $service->save();

        return to_route('admin.services.index')->with('status', $service->is_active
            ? 'The service is available for booking.'
            : 'The service is no longer available for booking.');
    }

    public function updateHours(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hours' => ['required', 'array', 'size:7'],
            'hours.*' => ['required', 'array'],
            'hours.*.is_closed' => ['sometimes', 'boolean'],
            'hours.*.opens_at' => ['nullable', 'date_format:H:i'],
            'hours.*.closes_at' => ['nullable', 'date_format:H:i'],
        ]);

        $hoursByWeekday = $validated['hours'];

        foreach (range(0, 6) as $weekday) {
            if (! array_key_exists($weekday, $hoursByWeekday)) {
                throw ValidationException::withMessages([
                    'hours' => 'Opening hours must include every day of the week.',
                ]);
            }

            $hours = $hoursByWeekday[$weekday];
            $closed = (bool) ($hours['is_closed'] ?? false);

            if ($closed) {
                continue;
            }

            if (empty($hours['opens_at']) || empty($hours['closes_at'])) {
                throw ValidationException::withMessages([
                    'hours' => 'Enter both opening and closing times for every open day.',
                ]);
            }

            $opensAt = CarbonImmutable::createFromFormat('!H:i', $hours['opens_at']);
            $closesAt = CarbonImmutable::createFromFormat('!H:i', $hours['closes_at']);

            if (! $closesAt->greaterThan($opensAt)) {
                throw ValidationException::withMessages([
                    'hours' => 'Closing time must be later than opening time.',
                ]);
            }

            $hoursByWeekday[$weekday]['opens_at'] = $opensAt->format('H:i:s');
            $hoursByWeekday[$weekday]['closes_at'] = $closesAt->format('H:i:s');
        }

        DB::transaction(function () use ($hoursByWeekday): void {
            foreach (range(0, 6) as $weekday) {
                $hours = $hoursByWeekday[$weekday];
                $closed = (bool) ($hours['is_closed'] ?? false);

                BusinessHour::updateOrCreate(
                    ['weekday' => $weekday],
                    [
                        'is_closed' => $closed,
                        'opens_at' => $closed ? null : $hours['opens_at'],
                        'closes_at' => $closed ? null : $hours['closes_at'],
                    ],
                );
            }
        });

        return to_route('admin.hours.index')->with('status', 'Opening hours were updated.');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $admin = $this->authenticatedAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($admin->getKey())],
            'current_password' => ['required', 'current_password:admin'],
        ]);
        $emailChanged = $validated['email'] !== $admin->email;

        DB::transaction(function () use ($admin, $validated, $emailChanged): void {
            $admin->name = $validated['name'];

            if ($emailChanged) {
                $admin->email = $validated['email'];

                if (config('app.require_admin_email_verification')) {
                    $admin->email_verified_at = null;
                }
            }

            $admin->save();

            if ($emailChanged && config('app.require_admin_email_verification')) {
                $admin->sendEmailVerificationNotification();
            }
        });

        $request->session()->regenerate();

        if ($emailChanged && config('app.require_admin_email_verification')) {
            return to_route('admin.login')
                ->with('status', 'Your email was updated. Verify the new address using the link we sent before signing in again.');
        }

        return to_route('admin.account.index')->with('status', 'Your personal information was updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $admin = $this->authenticatedAdmin();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password:admin'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $admin->password = $validated['password'];
        $admin->setRememberToken(Str::random(60));
        $admin->save();
        $request->session()->regenerate();

        return to_route('admin.account.index')->with('status', 'Your admin password was updated.');
    }

    public function verifyEmail(string $id, string $hash): RedirectResponse
    {
        $admin = User::query()->findOrFail($id);
        abort_unless(
            $admin->isAdmin()
                && hash_equals(sha1($admin->getEmailForVerification()), $hash),
            403,
        );

        if (! $admin->hasVerifiedEmail()) {
            $admin->markEmailAsVerified();
            event(new Verified($admin));
        }

        $guard = Auth::guard('admin');
        $authenticatedAdmin = $guard->user();

        if ($authenticatedAdmin instanceof User && $authenticatedAdmin->isAdmin() && $authenticatedAdmin->is($admin)) {
            $guard->setUser($admin);

            return to_route('admin.dashboard')->with('status', 'Your admin email has been verified.');
        }

        return to_route('admin.login')->with('status', 'Your admin email has been verified. You can now sign in.');
    }

    private function serviceRules(?Service $service = null): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('services', 'name')->ignore($service?->getKey()),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'duration_minutes' => ['required', 'integer', 'min:10', 'max:480', 'multiple_of:5'],
            'price' => ['required', 'numeric', 'min:0'],
        ];
    }

    private function authenticatedAdmin(): User
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin instanceof User && $admin->isAdmin(), 404);

        return $admin;
    }
}
