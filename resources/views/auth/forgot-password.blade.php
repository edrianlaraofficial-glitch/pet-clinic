@extends('layouts.app')
@section('title', 'Reset your password')

@section('content')
    <section class="auth-form-card password-reset-card">
        <p class="step-label">Account access</p>
        <h1>Reset your password</h1>
        <p class="muted">Enter the email address on your account. If an account exists, we’ll send a reset link.</p>
        @if ($errors->any())
            <div class="alert alert-error" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="field">
                <label for="email">Email address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
            </div>
            <button class="btn btn-primary" type="submit">Send reset link</button>
        </form>
        <p class="small"><a href="{{ route('login') }}">Back to sign in</a></p>
    </section>
@endsection
