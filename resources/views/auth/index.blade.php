@extends('layouts.app')
@section('title', 'Your pup’s care starts here')

@section('content')
    <div class="auth-shell">
        <aside class="auth-intro">
            <a href="{{ route('home') }}" class="auth-back-link"><span aria-hidden="true">←</span> Back to home</a>
            <div class="auth-illustration" aria-hidden="true">
                <span class="auth-sun"></span>
                <svg viewBox="0 0 180 180" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M42 84c-16-16-34-13-38 2-5 17 6 41 21 47 11 5 23-4 29-22L42 84Zm96 0c16-16 34-13 38 2 5 17-6 41-21 47-11 5-23-4-29-22l12-27Z" fill="#D88F54"/>
                    <path d="M53 57c10-13 23-20 37-20s27 7 37 20c11 14 16 31 16 49 0 36-23 62-53 62s-53-26-53-62c0-18 5-35 16-49Z" fill="#F3BD55"/>
                    <ellipse cx="72" cy="91" rx="5" ry="7" fill="#23483E"/>
                    <ellipse cx="108" cy="91" rx="5" ry="7" fill="#23483E"/>
                    <path d="M66 116c7-13 15-19 24-19s17 6 24 19c-5 15-14 23-24 23s-19-8-24-23Z" fill="#FFF5DF"/>
                    <ellipse cx="90" cy="111" rx="9" ry="7" fill="#23483E"/>
                    <path d="M90 118v8m0 0c-5 6-10 6-15 2m15-2c5 6 10 6 15 2" stroke="#23483E" stroke-width="3" stroke-linecap="round"/>
                    <path d="M57 154c10 7 21 10 33 10s23-3 33-10" stroke="#E87859" stroke-width="9" stroke-linecap="round"/>
                    <path d="m91 160 9 10-9 10-9-10 9-10Z" fill="#FFF5DF" stroke="#E87859" stroke-width="3" stroke-linejoin="round"/>
                </svg>
                <span class="auth-spark auth-spark-one">✦</span>
                <span class="auth-spark auth-spark-two">✳</span>
            </div>
            <p class="step-label">Wade-County Animal Clinic</p>
            <h1>More tail wags start here.</h1>
            <p class="auth-intro-copy">Book visits, keep your dog’s details handy, and get kind care from people who love what they do.</p>
            <div class="auth-rating">
                <span class="reviews-stars" aria-label="5 out of 5 stars">★★★★★</span>
                <span><strong>5.0 on Google</strong><small>From local pet parents</small></span>
            </div>
        </aside>

        <div class="auth-forms">
            <fieldset class="auth-tabs">
                <legend class="visually-hidden">Choose an account action</legend>
                <input
                    id="auth-tab-login"
                    type="radio"
                    name="auth_view"
                    value="login"
                    @checked(old('_form') !== 'register')
                >
                <label for="auth-tab-login">Sign in</label>
                <input
                    id="auth-tab-register"
                    type="radio"
                    name="auth_view"
                    value="register"
                    @checked(old('_form') === 'register')
                >
                <label for="auth-tab-register">Create account</label>
            </fieldset>

            <section class="auth-form-card auth-login-card">
                <div class="auth-form-heading">
                    <span class="auth-form-icon" aria-hidden="true">↗</span>
                    <div>
                        <p class="step-label">Welcome back</p>
                        <h2>Sign in</h2>
                    </div>
                </div>
                <p class="auth-form-intro">Your next visit is just a few paw-steps away.</p>
                <div class="social-login-placeholder" aria-label="Other sign-in options, coming soon">
                    <button class="social-login-placeholder-button" type="button" disabled>
                        <svg viewBox="0 0 48 48" aria-hidden="true">
                            <path fill="#4285F4" d="M43.6 24.5c0-1.4-.1-2.8-.4-4.1H24v7.8h11a9.4 9.4 0 0 1-4.1 6.2v5.1h6.7c3.9-3.6 6-8.9 6-15Z"/>
                            <path fill="#34A853" d="M24 44c5.5 0 10.1-1.8 13.5-4.9l-6.7-5.1c-1.8 1.2-4 1.9-6.8 1.9-5.2 0-9.6-3.5-11.2-8.2H5.9v5.2A20 20 0 0 0 24 44Z"/>
                            <path fill="#FBBC05" d="M12.8 27.7a12 12 0 0 1 0-7.4v-5.2H5.9a20 20 0 0 0 0 17.8l6.9-5.2Z"/>
                            <path fill="#EA4335" d="M24 12.1c3 0 5.7 1 7.8 3.1l5.8-5.8A19.4 19.4 0 0 0 24 4 20 20 0 0 0 5.9 15.1l6.9 5.2c1.6-4.7 6-8.2 11.2-8.2Z"/>
                        </svg>
                        <span>Google <small>Coming soon</small></span>
                    </button>
                    <button class="social-login-placeholder-button" type="button" disabled>
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="currentColor" d="M16.37 12.2c.02 2.15 1.88 2.87 1.9 2.88-.02.05-.3 1.03-.98 2.04-.59.87-1.2 1.73-2.16 1.75-.94.02-1.25-.57-2.33-.57-1.08 0-1.42.55-2.3.59-.92.03-1.62-.94-2.22-1.8-1.22-1.76-2.15-4.96-.9-7.12a3.42 3.42 0 0 1 2.89-1.76c.9-.02 1.74.61 2.3.61.56 0 1.6-.75 2.7-.64.46.02 1.76.19 2.59 1.42-.07.04-1.55.9-1.53 2.6ZM14.6 5.96c.49-.59.82-1.4.73-2.21-.7.03-1.55.47-2.05 1.06-.45.52-.85 1.34-.74 2.13.78.06 1.58-.4 2.06-.98Z"/>
                        </svg>
                        <span>Apple ID <small>Coming soon</small></span>
                    </button>
                </div>
                <p class="social-login-divider"><span>or sign in with email</span></p>
                <form method="POST" action="{{ route('auth.login') }}" novalidate>
                    @csrf
                    <input type="hidden" name="_form" value="login">
                    <div class="field">
                        <label for="login-email">Email address</label>
                        <input id="login-email" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autocomplete="email">
                        @error('email') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field">
                        <label for="login-password">Password</label>
                        <input id="login-password" type="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
                    </div>
                    <label class="checkbox auth-remember">
                        <input type="checkbox" name="remember" value="1"> Keep me signed in
                    </label>
                    <button class="btn btn-primary auth-submit" type="submit">Let’s go <span aria-hidden="true">↗</span></button>
                    <p class="small auth-email-note"><a href="{{ route('password.request') }}">Forgot your password?</a></p>
                </form>
            </section>

            <section class="auth-form-card auth-register-card" id="create-account">
                <div class="auth-form-heading">
                    <span class="auth-form-icon auth-form-icon-coral" aria-hidden="true">♡</span>
                    <div>
                        <p class="step-label">First time here?</p>
                        <h2>Create an account</h2>
                    </div>
                </div>
                <p class="auth-form-intro">Join the pack. It only takes a minute.</p>
                <div class="social-login-placeholder" aria-label="Other account creation options, coming soon">
                    <button class="social-login-placeholder-button" type="button" disabled>
                        <svg viewBox="0 0 48 48" aria-hidden="true">
                            <path fill="#4285F4" d="M43.6 24.5c0-1.4-.1-2.8-.4-4.1H24v7.8h11a9.4 9.4 0 0 1-4.1 6.2v5.1h6.7c3.9-3.6 6-8.9 6-15Z"/>
                            <path fill="#34A853" d="M24 44c5.5 0 10.1-1.8 13.5-4.9l-6.7-5.1c-1.8 1.2-4 1.9-6.8 1.9-5.2 0-9.6-3.5-11.2-8.2H5.9v5.2A20 20 0 0 0 24 44Z"/>
                            <path fill="#FBBC05" d="M12.8 27.7a12 12 0 0 1 0-7.4v-5.2H5.9a20 20 0 0 0 0 17.8l6.9-5.2Z"/>
                            <path fill="#EA4335" d="M24 12.1c3 0 5.7 1 7.8 3.1l5.8-5.8A19.4 19.4 0 0 0 24 4 20 20 0 0 0 5.9 15.1l6.9 5.2c1.6-4.7 6-8.2 11.2-8.2Z"/>
                        </svg>
                        <span>Google <small>Coming soon</small></span>
                    </button>
                    <button class="social-login-placeholder-button" type="button" disabled>
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="currentColor" d="M16.37 12.2c.02 2.15 1.88 2.87 1.9 2.88-.02.05-.3 1.03-.98 2.04-.59.87-1.2 1.73-2.16 1.75-.94.02-1.25-.57-2.33-.57-1.08 0-1.42.55-2.3.59-.92.03-1.62-.94-2.22-1.8-1.22-1.76-2.15-4.96-.9-7.12a3.42 3.42 0 0 1 2.89-1.76c.9-.02 1.74.61 2.3.61.56 0 1.6-.75 2.7-.64.46.02 1.76.19 2.59 1.42-.07.04-1.55.9-1.53 2.6ZM14.6 5.96c.49-.59.82-1.4.73-2.21-.7.03-1.55.47-2.05 1.06-.45.52-.85 1.34-.74 2.13.78.06 1.58-.4 2.06-.98Z"/>
                        </svg>
                        <span>Apple ID <small>Coming soon</small></span>
                    </button>
                </div>
                <p class="social-login-divider"><span>or create account with email</span></p>
                <form method="POST" action="{{ route('auth.register') }}" novalidate>
                    @csrf
                    <input type="hidden" name="_form" value="register">
                    <div class="auth-register-grid">
                        <div class="field">
                            <label for="reg-name">Full name</label>
                            <input id="reg-name" type="text" name="name" value="{{ old('name') }}" placeholder="Your name" required autocomplete="name">
                            @error('name') <span class="field-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="field">
                            <label for="reg-email">Email address</label>
                            <input id="reg-email" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autocomplete="email">
                            @error('email') <span class="field-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="field">
                            <label for="reg-phone">Phone number</label>
                            <input id="reg-phone" type="tel" name="phone" value="{{ old('phone') }}" placeholder="Your phone number" required autocomplete="tel">
                            @error('phone') <span class="field-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="field">
                            <label for="reg-password">Password <span class="muted small">(8+ characters)</span></label>
                            <input id="reg-password" type="password" name="password" placeholder="Create a password" required autocomplete="new-password">
                            @error('password') <span class="field-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="field auth-confirm-field">
                            <label for="reg-password-confirm">Confirm password</label>
                            <input id="reg-password-confirm" type="password" name="password_confirmation" placeholder="Enter it again" required autocomplete="new-password">
                        </div>
                    </div>
                    <button class="btn btn-dark auth-submit" type="submit">Join the pack <span aria-hidden="true">↗</span></button>
                    <p class="small muted auth-email-note">We’ll email you a link to confirm your address.</p>
                    <p class="small muted auth-email-note">Before joining, read our <a href="{{ route('policies.privacy') }}">Privacy Policy</a> and <a href="{{ route('policies.terms') }}">Terms and Conditions</a>.</p>
                </form>
            </section>
        </div>
    </div>
@endsection
