<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Wade-County Animal Clinic') · Wade-County</title>
    @fonts
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
</head>
<body class="@yield('body-class')">
    <header class="site-header">
        <div class="container">
            <a href="{{ route('home') }}" class="brand">
                <span class="brand-mark" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="#fff"><ellipse cx="12" cy="16" rx="5" ry="4"/><circle cx="5.5" cy="10" r="2.2"/><circle cx="9.5" cy="5.5" r="2.2"/><circle cx="14.5" cy="5.5" r="2.2"/><circle cx="18.5" cy="10" r="2.2"/></svg>
                </span>
                <span class="brand-copy">Wade-County<small>Animal Clinic</small></span>
            </a>

            <nav class="nav" aria-label="Main">
                <a href="{{ route('home') }}" data-public-nav="home" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('home') }}#about" data-public-nav="about">About</a>
                <a href="{{ route('home') }}#gallery" data-public-nav="gallery">Gallery</a>
                <a href="{{ route('home') }}#ratings" data-public-nav="ratings">Ratings</a>
                @auth
                    <a href="{{ route('my-bookings') }}" class="{{ request()->routeIs('my-bookings', 'appointments.*') ? 'active' : '' }}">My bookings</a>
                    <a href="{{ route('pets.index') }}" class="{{ request()->routeIs('pets.*') ? 'active' : '' }}">My dogs</a>
                    <span class="small muted">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-ghost btn-sm" type="submit">Sign out</button>
                    </form>
                    <div class="nav-actions">
                        <a href="{{ route('booking.create') }}" class="btn btn-primary nav-book-visit">Book a visit</a>
                    </div>
                @else
                    <div class="nav-actions">
                        <a href="{{ route('login') }}" class="btn btn-primary nav-book-visit">Book a visit</a>
                        <a href="{{ route('login') }}" class="nav-sign-in">Sign in</a>
                    </div>
                @endauth
            </nav>
        </div>
    </header>

    <main>
        <div class="container stack">
            @if (session('status'))
                <div class="toast" role="status">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-error" role="alert">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="site-footer" id="contact">
        <div class="container footer-grid">
            <div class="footer-brand">
                <a href="{{ route('home') }}" class="brand">
                    <span class="brand-mark" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="#fff"><ellipse cx="12" cy="16" rx="5" ry="4"/><circle cx="5.5" cy="10" r="2.2"/><circle cx="9.5" cy="5.5" r="2.2"/><circle cx="14.5" cy="5.5" r="2.2"/><circle cx="18.5" cy="10" r="2.2"/></svg>
                    </span>
                    <span class="brand-copy">Wade-County<small>Animal Clinic</small></span>
                </a>
                <p>Good care, kind people, and a softer landing for every visit.</p>
                <span class="footer-copyright">© {{ date('Y') }} Wade-County Animal Clinic</span>
            </div>
            <div class="footer-detail">
                <h2>Come say hello</h2>
                <address>Garcia St., P-6, Poblacion,<br>Trento, 8505 Agusan del Sur,<br>Philippines</address>
                <a href="https://www.google.com/maps/search/?api=1&query=Wade-County+Animal+Clinic%2C+Garcia+St.%2C+P-6%2C+Poblacion%2C+Trento%2C+8505+Agusan+del+Sur%2C+Philippines" target="_blank" rel="noopener noreferrer">
                    Find us on Google Maps <span aria-hidden="true">↗</span>
                </a>
            </div>
            <div class="footer-detail">
                <h2>Clinic hours</h2>
                <ul class="footer-hours" aria-label="Weekly clinic hours">
                    @forelse ($footerHours as $hour)
                        <li>
                            <span>{{ $hour->dayName() }}</span>
                            <strong>
                                @if ($hour->is_closed || ! $hour->opens_at || ! $hour->closes_at)
                                    Closed for snoozes
                                @else
                                    {{ \Carbon\Carbon::parse($hour->opens_at)->format('g:i A') }} – {{ \Carbon\Carbon::parse($hour->closes_at)->format('g:i A') }}
                                @endif
                            </strong>
                        </li>
                    @empty
                        <li class="footer-hours-empty">Hours not posted</li>
                    @endforelse
                </ul>
                <span class="footer-love">Made with <span class="footer-heart" aria-label="love">♥</span> for good dogs.</span>
            </div>
            <nav class="footer-detail footer-policies" aria-label="Policies">
                <h2>Policies</h2>
                <a href="{{ route('policies.privacy') }}">Privacy policy</a>
                <a href="{{ route('policies.cookies') }}">Cookie policy</a>
                <a href="{{ route('policies.terms') }}">Terms and conditions</a>
                <a href="{{ route('policies.refunds') }}">Refund and cancellation</a>
                <a href="{{ route('policies.veterinary') }}">Veterinary disclaimer</a>
            </nav>
        </div>
    </footer>

    <script src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>
