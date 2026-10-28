@extends('layouts.app')
@section('title', 'Terms and Conditions')

@section('content')
    <article class="policy-page">
        <header class="policy-header">
            <p class="step-label">Using this service</p>
            <h1>Terms and Conditions</h1>
            <p class="muted">Draft terms for the Wade-County Animal Clinic website and online appointment requests.</p>
        </header>

        @include('policies._status')
        @include('policies._navigation')

        <section>
            <h2>1. Scope and review</h2>
            <p>These draft terms describe use of this website and its online appointment-request feature. They require clinic management and Philippine legal review before acceptance or publication as binding terms. <strong>[Clinic: confirm the operating legal entity, contact details, and effective date.]</strong></p>
        </section>

        <section>
            <h2>2. Accounts and information</h2>
            <p>Provide accurate account, contact, pet, and appointment information and keep account credentials confidential. Notify the clinic if you believe your account was accessed without permission. The clinic may need to verify identity before discussing account or appointment details.</p>
        </section>

        <section>
            <h2>3. Appointment requests</h2>
            <p>An online request is not a confirmed appointment. The clinic must separately confirm availability. The clinic may contact you to clarify details or arrange a different time. Do not rely on a pending online request as confirmation of care.</p>
        </section>

        <section>
            <h2>4. Services and prices</h2>
            <p>Service descriptions, durations, and prices shown online are provided for planning and may require confirmation by the clinic. The clinic should explain any estimate, additional work, and charges before providing a service. <strong>[Clinic: confirm pricing, estimates, and any applicable payment arrangements.]</strong></p>
        </section>

        <section>
            <h2>5. Website use and availability</h2>
            <p>Use the website lawfully and do not attempt to interfere with its security or availability. The clinic may maintain or change website features. The website may be unavailable during maintenance or due to circumstances outside the clinic's control.</p>
        </section>

        <section>
            <h2>6. Veterinary advice and legal terms</h2>
            <p>Website content and online communications are not a substitute for a veterinary examination or urgent assessment. Nothing in these draft terms excludes rights or responsibilities that cannot lawfully be excluded. <strong>[Clinic/legal counsel: review consumer, professional, privacy, liability, dispute, and governing-law language for applicable Philippine law.]</strong></p>
        </section>

        <section>
            <h2>7. Contact</h2>
            <address>Wade-County Animal Clinic<br>Garcia St., P-6, Poblacion,<br>Trento, 8505 Agusan del Sur, Philippines<br><strong>[Clinic: add verified phone and email]</strong></address>
        </section>
    </article>
@endsection
