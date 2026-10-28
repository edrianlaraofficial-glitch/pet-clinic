@extends('layouts.admin')
@section('title', 'Account')

@section('content')
    <div class="admin-page-heading">
        <div>
            <p class="step-label">Administrator</p>
            <h1>Account</h1>
            <p class="muted">Manage your personal information and sign-in password.</p>
        </div>
    </div>

    <section class="admin-panel admin-account-panel" id="account-profile" aria-labelledby="personal-information-title">
        <div class="admin-panel-heading">
            <div>
                <p class="step-label">Your profile</p>
                <h2 id="personal-information-title">Personal information</h2>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.profile.update') }}" class="admin-account-form">
            @csrf
            @method('PUT')
            <div class="field">
                <label for="admin-name">Administrator name</label>
                <input id="admin-name" type="text" name="name" value="{{ old('name', auth('admin')->user()->name) }}" autocomplete="name" maxlength="255" required>
                @error('name') <span class="field-error">{{ $message }}</span> @enderror
            </div>
            <div class="field">
                <label for="admin-email">Email address</label>
                <input id="admin-email" type="email" name="email" value="{{ old('email', auth('admin')->user()->email) }}" autocomplete="email" maxlength="255" required>
                @error('email') <span class="field-error">{{ $message }}</span> @enderror
            </div>
            <div class="field">
                <label for="profile-current-password">Confirm current password</label>
                <input id="profile-current-password" type="password" name="current_password" autocomplete="current-password" required>
                @error('current_password') <span class="field-error">{{ $message }}</span> @enderror
                <span class="muted small">Required to save profile changes.</span>
            </div>
            <button class="btn btn-primary" type="submit">Save personal information</button>
        </form>
    </section>

    <section class="admin-panel admin-account-panel" id="account-password" aria-labelledby="change-password-title">
        <div class="admin-panel-heading">
            <div>
                <p class="step-label">Keep your account secure</p>
                <h2 id="change-password-title">Change password</h2>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.password.update') }}" class="admin-account-form">
            @csrf
            @method('PUT')
            <div class="field">
                <label for="password-current">Current password</label>
                <input id="password-current" type="password" name="current_password" autocomplete="current-password" required>
                @error('current_password') <span class="field-error">{{ $message }}</span> @enderror
            </div>
            <div class="field">
                <label for="password-new">New password</label>
                <input id="password-new" type="password" name="password" autocomplete="new-password" minlength="8" required>
                @error('password') <span class="field-error">{{ $message }}</span> @enderror
            </div>
            <div class="field">
                <label for="password-confirmation">Confirm new password</label>
                <input id="password-confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required>
            </div>
            <button class="btn btn-primary" type="submit">Save new password</button>
        </form>
    </section>
@endsection
