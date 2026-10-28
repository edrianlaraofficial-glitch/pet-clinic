<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>@yield('title', 'Admin') · Wade-County</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="admin-body">
    <div class="admin-shell">
        <aside class="admin-rail" aria-label="Admin navigation">
            <a href="{{ route('admin.dashboard') }}" class="brand admin-brand">
                <span class="brand-mark" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="#fff"><ellipse cx="12" cy="16" rx="5" ry="4"/><circle cx="5.5" cy="10" r="2.2"/><circle cx="9.5" cy="5.5" r="2.2"/><circle cx="14.5" cy="5.5" r="2.2"/><circle cx="18.5" cy="10" r="2.2"/></svg>
                </span>
                <span class="brand-copy">Wade-County<small>Animal Clinic</small></span>
            </a>

            <nav class="admin-nav" aria-label="Primary">
                <p class="admin-nav-label">Workspace</p>
                <a href="{{ route('admin.dashboard') }}" @class(['active' => request()->routeIs('admin.dashboard')]) @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="5" rx="1.5"/><rect x="13" y="10" width="8" height="11" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/></svg>
                    <span>Appointments</span>
                </a>
                <a href="{{ route('admin.customers.index') }}" @class(['active' => request()->routeIs('admin.customers.*')]) @if (request()->routeIs('admin.customers.*')) aria-current="page" @endif>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M3 20v-1.5a6 6 0 0 1 12 0V20H3Z"/><path d="M16 5.2a3.5 3.5 0 0 1 0 6.7M18 14a5 5 0 0 1 3 4.5V20h-3"/></svg>
                    <span>Customers</span>
                </a>

                <p class="admin-nav-label">Clinic management</p>
                <a href="{{ route('admin.services.index') }}" @class(['active' => request()->routeIs('admin.services.*')]) @if (request()->routeIs('admin.services.*')) aria-current="page" @endif>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/><circle cx="8" cy="7" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="10" cy="17" r="1.5"/></svg>
                    <span>Services</span>
                </a>
                <a href="{{ route('admin.hours.index') }}" @class(['active' => request()->routeIs('admin.hours.*')]) @if (request()->routeIs('admin.hours.*')) aria-current="page" @endif>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/></svg>
                    <span>Opening hours</span>
                </a>
                <a href="{{ route('admin.account.index') }}" @class(['active' => request()->routeIs('admin.account.*')]) @if (request()->routeIs('admin.account.*')) aria-current="page" @endif>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M5 21v-2a7 7 0 0 1 14 0v2M18 8h4m-2-2v4"/></svg>
                    <span>Account</span>
                </a>
                <a href="{{ route('admin.gallery.index') }}" @class(['active' => request()->routeIs('admin.gallery.*')]) @if (request()->routeIs('admin.gallery.*')) aria-current="page" @endif>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8.5" cy="9" r="1.5"/><path d="m21 15-5-5L5 20"/></svg>
                    <span>Gallery</span>
                </a>
            </nav>

            <div class="admin-rail-footer">
                <div class="admin-identity">
                    <span class="admin-identity-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth('admin')->user()->name, 0, 1)) }}</span>
                    <span class="admin-identity-copy"><strong>{{ auth('admin')->user()->name }}</strong><small>Clinic administrator</small></span>
                    <span class="admin-badge">Admin</span>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="admin-signout" type="submit">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3"/><path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/></svg>
                        <span>Sign out</span>
                    </button>
                </form>
            </div>
        </aside>

        <div class="admin-workspace">
            <header class="admin-topbar">
                <div>
                    <span class="admin-topbar-label">Administration</span>
                    <strong>Wade-County Animal Clinic</strong>
                </div>
                <span class="admin-topbar-status"><span aria-hidden="true"></span> Secure admin workspace</span>
            </header>

            <main class="admin-main">
                <div class="admin-container">
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
        </div>
    </div>
    <script src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>
