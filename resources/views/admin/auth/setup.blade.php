@extends('layouts.admin-auth')
@section('title', 'First admin setup')

@section('content')
    <section class="admin-login-card">
        <a href="{{ route('home') }}" class="brand admin-login-brand">
            <span class="brand-mark" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="#fff"><ellipse cx="12" cy="16" rx="5" ry="4"/><circle cx="5.5" cy="10" r="2.2"/><circle cx="9.5" cy="5.5" r="2.2"/><circle cx="14.5" cy="5.5" r="2.2"/><circle cx="18.5" cy="10" r="2.2"/></svg>
            </span>
            <span class="brand-copy">Wade-County<small>Animal Clinic</small></span>
        </a>

        <p class="step-label">One-time administrator setup</p>
        <h1>First administrator setup</h1>
        <p class="muted">Welcome, Admin! Create your first administrator account to get started.</p>

        @if ($errors->any())
            <div class="alert alert-error" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.setup.store') }}">
            @csrf
            <div class="field">
                <label for="admin-name">Full name</label>
                <input id="admin-name" type="text" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="255" required autofocus>
            </div>
            <div class="field">
                <label for="admin-email">Email address</label>
                <input id="admin-email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" maxlength="255" required>
            </div>
            <div class="field">
                <label for="admin-password">Password</label>
                <input id="admin-password" type="password" name="password" autocomplete="new-password" minlength="12" required>
            </div>
            <div class="field">
                <label for="admin-password-confirmation">Confirm password</label>
                <input id="admin-password-confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="12" required>
            </div>
            <div class="field">
                <label for="admin-setup-key">One-time setup key</label>
                <input id="admin-setup-key" type="password" name="setup_key" autocomplete="off" required>
            </div>
            <button class="btn btn-primary admin-login-submit" type="submit">Create administrator account</button>
        </form>
        <p class="admin-login-note"><span aria-hidden="true">🔒</span> Setup requires a private key and closes permanently after the first administrator is created.</p>
    </section>
@endsection
