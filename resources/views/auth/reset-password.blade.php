@extends('layouts.app')
@section('title', 'Choose a new password')

@section('content')
    <section class="auth-form-card password-reset-card">
        <p class="step-label">Account access</p>
        <h1>Choose a new password</h1>
        <p class="muted">Reset links expire after 60 minutes. Request a new link if this one has expired.</p>
        @if ($errors->any())
            <div class="alert alert-error" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="field">
                <label for="email">Email address</label>
                <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email">
            </div>
            <div class="field">
                <label for="password">New password</label>
                <input id="password" type="password" name="password" required autocomplete="new-password">
            </div>
            <div class="field">
                <label for="password_confirmation">Confirm new password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
            </div>
            <button class="btn btn-primary" type="submit">Reset password</button>
        </form>
    </section>
@endsection
