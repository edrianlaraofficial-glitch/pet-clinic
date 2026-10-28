<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function show(): View
    {
        return view('auth.index');
    }

    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        Password::sendResetLink($credentials);

        return back()->with('status', 'If an account exists for that email address, a password reset link will be sent.');
    }

    public function showResetPassword(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset($credentials, function (User $user, string $password): void {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        });

        if ($status === Password::PASSWORD_RESET) {
            return to_route('login')->with('status', 'Your password has been reset. You can now sign in.');
        }

        return back()->withErrors(['email' => 'The password reset link is invalid or expired. Request a new link and try again.']);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember')) || Auth::user()?->isAdmin()) {
            Auth::logout();

            return back()
                ->withErrors(['email' => 'These credentials do not match our records.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.unique' => 'We could not create an account with those details. Try signing in or resetting your password.',
        ]);

        $user = DB::transaction(function () use ($validated): User {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'email_verified_at' => config('app.require_email_verification') ? null : now(),
            ]);

            $user->profile()->create([
                'full_name' => $validated['name'],
                'phone' => $validated['phone'],
            ]);

            return $user;
        });

        if (config('app.require_email_verification')) {
            event(new Registered($user));
        }

        Auth::login($user);
        $request->session()->regenerate();

        if (! config('app.require_email_verification')) {
            return redirect()->route('home')
                ->with('status', 'Your account is ready. You can book an appointment now.');
        }

        return redirect()->route('verification.notice')
            ->with('status', 'Your account was created. Please confirm your email before booking.');
    }

    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect()->route('home')->with('status', 'Your email address has been verified.');
    }

    public function verifyNotice(): View|RedirectResponse
    {
        if (! config('app.require_email_verification')) {
            return redirect()->route('home');
        }

        return view('auth.verify');
    }

    public function resend(Request $request): RedirectResponse
    {
        if (! config('app.require_email_verification') || $request->user()->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'A new confirmation link has been sent to your email address.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
