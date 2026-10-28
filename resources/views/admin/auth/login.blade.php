@extends('layouts.admin-auth')
@section('title', 'Admin sign in')

@section('content')
    <section class="admin-login-card">
        <a href="{{ route('home') }}" class="brand admin-login-brand">
            <span class="brand-mark" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="#fff"><ellipse cx="12" cy="16" rx="5" ry="4"/><circle cx="5.5" cy="10" r="2.2"/><circle cx="9.5" cy="5.5" r="2.2"/><circle cx="14.5" cy="5.5" r="2.2"/><circle cx="18.5" cy="10" r="2.2"/></svg>
            </span>
            <span class="brand-copy">Wade-County<small>Animal Clinic</small></span>
        </a>

        <p class="step-label">Private staff access</p>
        <h1>Admin sign in</h1>
        <p class="muted">Use your verified administrator account to manage the clinic website.</p>

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
    </section>
@endsection
