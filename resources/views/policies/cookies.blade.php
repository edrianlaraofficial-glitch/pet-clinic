@extends('layouts.app')
@section('title', 'Cookie Policy')

@section('content')
    <article class="policy-page">
        <header class="policy-header">
            <p class="step-label">Website preferences</p>
            <h1>Cookie Policy</h1>
            <p class="muted">A summary of the browser storage this service needs to operate.</p>
        </header>

        @include('policies._status')
        @include('policies._navigation')

        <section>
            <h2>Essential session cookies</h2>
            <p>The website uses an essential Laravel session cookie to keep a signed-in session and support security features such as CSRF protection. The cookie is configured as HttpOnly and SameSite=Lax. In production, the Secure attribute should be enabled and the site should be served only over HTTPS.</p>
            <p>These cookies are required for account, booking, and administrator functions. They are not used by this application for advertising or analytics.</p>
        </section>

        <section>
            <h2>Optional tracking</h2>
            <p>No analytics or advertising cookie integration is present in the current application code. Hosting, embedded content, or other third-party services added by the clinic may use their own storage; verify those services and update this policy before enabling them.</p>
            <p>There is no optional-cookie preference interface because no nonessential tracking is currently implemented. If optional cookies are introduced, disclose them and implement any legally required consent and preference controls before setting them.</p>
        </section>

        <section>
            <h2>Managing cookies</h2>
            <p>You can configure your browser to block or clear cookies. Blocking the essential session cookie may prevent sign-in, booking, and administrator functions from working. The clinic should confirm cookie names and lifetimes against the final production configuration.</p>
        </section>
    </article>
@endsection
