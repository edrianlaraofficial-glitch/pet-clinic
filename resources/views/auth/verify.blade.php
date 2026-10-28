@extends('layouts.app')
@section('title', 'Confirm your email')

@section('content')
    <section class="card" style="max-width:620px;margin-inline:auto">
        <p class="step-label">One more step</p>
        <h1>Check your inbox</h1>
        <p>We've sent a confirmation link to <strong>{{ auth()->user()->email }}</strong>. Click it to start booking appointments.</p>
        <p class="muted small">Can't find it? Check your spam folder, or ask us to send it again.</p>

        <div class="row" style="margin-top:1.25rem">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button class="btn btn-primary" type="submit">Resend confirmation email</button>
            </form>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-ghost" type="submit">Sign out</button>
            </form>
        </div>
    </section>
@endsection
