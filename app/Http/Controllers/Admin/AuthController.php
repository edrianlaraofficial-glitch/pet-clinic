<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function show(FirstAdminSetupController $firstAdminSetup): View|RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        if ($admin?->isAdmin()) {
            return to_route('admin.dashboard');
        }

        return view('admin.auth.login', [
            'setupAvailable' => $firstAdminSetup->isSetupAvailable(),
        ]);
    }

    public function store(Request $request, FirstAdminSetupController $firstAdminSetup): RedirectResponse
    {
        if ($request->exists('name') || $request->exists('setup_key')) {
            return $firstAdminSetup->store($request);
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $guard = Auth::guard('admin');
        $authenticated = $guard->attempt($credentials);
        $admin = $guard->user();

        if (! $authenticated || ! $admin?->isAdmin()) {
            $guard->logout();

            return back()
                ->withErrors(['email' => 'The admin credentials could not be verified.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return to_route('admin.dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();
        $request->session()->regenerate();

        return to_route('admin.login');
    }
}
