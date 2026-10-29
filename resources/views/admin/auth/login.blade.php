@extends('layouts.admin-auth')
@section('title', $setupAvailable ? 'First admin setup' : 'Admin sign in')

@section('content')
    <section class="admin-login-card">
        <a href="{{ route('home') }}" class="brand admin-login-brand">
            <span class="brand-mark" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="#fff"><ellipse cx="12" cy="16" rx="5" ry="4"/><circle cx="5.5" cy="10" r="2.2"/><circle cx="9.5" cy="5.5" r="2.2"/><circle cx="14.5" cy="5.5" r="2.2"/><circle cx="18.5" cy="10" r="2.2"/></svg>
            </span>
            <span class="brand-copy">Wade-County<small>Animal Clinic</small></span>
        </a>

        @if ($setupAvailable)
            <p class="step-label">One-time administrator setup</p>
            <h1>First administrator account</h1>
            <p class="muted">Welcome, Admin! Get started by creating your first administrator account.</p>
        @else
            <p class="step-label">Private staff access</p>
            <h1>Admin sign in</h1>
            <p class="muted">Use your administrator account to manage the clinic website.</p>
        @endif

        @if (session('status'))
            <div class="alert alert-info" role="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @if ($setupAvailable)
            <form method="POST" action="{{ route('admin.login.store') }}">
                @csrf
                <div class="field">
                    <label for="admin-name">Full Name</label>
                    <input id="admin-name" type="text" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="255" required autofocus>
                </div>
                <div class="field">
                    <label for="admin-email">Email</label>
                    <input id="admin-email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" maxlength="255" required>
                </div>
                <div class="field">
                    <label for="admin-password">Password</label>
                    <input id="admin-password" type="password" name="password" autocomplete="new-password" minlength="12" required>
                </div>
                <div class="field">
                    <label for="admin-password-confirmation">Confirm Password</label>
                    <input id="admin-password-confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="12" required>
                </div>
                <div class="field">
                    <label for="admin-setup-key">Private setup key</label>
                    <input id="admin-setup-key" type="password" name="setup_key" autocomplete="off" required>
                </div>
                <button class="btn btn-primary admin-login-submit" type="submit">Create Your First Account</button>
            </form>
            <p class="admin-login-note"><span aria-hidden="true">🔒</span> First-account setup requires the private key configured for this deployment.</p>
        @else
            <form method="POST" action="{{ route('admin.login.store') }}">
                @csrf
                <div class="field">
                    <label for="admin-email">Admin email</label>
                    <input id="admin-email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
                </div>
                <div class="field">
                    <label for="admin-password">Password</label>
                    <input id="admin-password" type="password" name="password" autocomplete="current-password" required>
                </div>
                <button class="btn btn-primary admin-login-submit" type="submit">Sign in securely</button>
            </form>
            <p class="admin-login-note"><span aria-hidden="true">🔒</span> This sign-in is separate from customer accounts.</p>
        @endif
    </section>
@endsection
