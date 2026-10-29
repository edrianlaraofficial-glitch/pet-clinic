@extends('layouts.app')
@section('title', 'Confirm your email')

@section('content')
    <section class="card" style="max-width:620px;margin-inline:auto">
        <p class="step-label">One more step</p>
        <h1>Check your inbox</h1>
        <p>Enter the email address you used to register. If your account still needs verification, we’ll send a fresh confirmation link.</p>
        <p class="muted small">Can't find it? Check your spam folder, or ask us to send it again.</p>

        <form method="POST" action="{{ route('verification.send') }}" style="margin-top:1.25rem">
            @csrf
            <div class="field">
                <label for="verification-email">Email address</label>
                <input id="verification-email" type="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required>
            </div>
            <div class="row">
                <button class="btn btn-primary" type="submit">Resend confirmation email</button>
                <a class="btn btn-ghost" href="{{ route('login') }}">Back to sign in</a>
            </div>
        </form>
    </section>
@endsection
