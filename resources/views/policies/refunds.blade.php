@extends('layouts.app')
@section('title', 'Refund and Cancellation Policy')

@section('content')
    <article class="policy-page">
        <header class="policy-header">
            <p class="step-label">Appointments and payments</p>
            <h1>Refund and Cancellation Policy</h1>
            <p class="muted">A management-review draft. No payment or refund terms are assumed by this website.</p>
        </header>

        @include('policies._status')
        @include('policies._navigation')

        <div class="policy-draft-notice policy-draft-warning" role="alert">
            Complete and approve every bracketed item before publishing this as an operative policy. The current application does not implement online payments or deposits.
        </div>

        <section>
            <h2>Appointment cancellation</h2>
            <p>Customers can request cancellation of eligible future appointments through their account. <strong>[Clinic: specify any advance notice requested, how to contact the clinic if online cancellation is unavailable, and how late cancellations or missed visits are handled.]</strong></p>
        </section>

        <section>
            <h2>Rescheduling</h2>
            <p>Customers can request an available replacement time for eligible appointments. A rescheduled request returns to pending status until the clinic confirms it. <strong>[Clinic: specify any rescheduling limits or notice requirements, if any.]</strong></p>
        </section>

        <section>
            <h2>Deposits and payments</h2>
            <p>The website currently has no payment gateway and does not collect deposits online. <strong>[Clinic: confirm whether deposits or advance payments are collected outside this website. If so, state when they apply, accepted payment methods, how they are applied, and the treatment of cancellations.]</strong></p>
        </section>

        <section>
            <h2>Services, products, and refund requests</h2>
            <p><strong>[Clinic: specify the process and timeframe to request review of a charge for consultations, procedures, medication, or products; who handles the request; and any relevant exceptions.]</strong> Do not promise a refund timeframe or make a charge non-refundable unless management has approved the term and it is permitted by applicable law.</p>
        </section>

        <section>
            <h2>Consumer rights and contact</h2>
            <p>This draft does not limit rights that cannot lawfully be waived. Have clinic management and Philippine legal counsel review the final policy. Contact <strong>[Clinic: verified phone/email]</strong> for cancellation, rescheduling, or refund questions.</p>
        </section>
    </article>
@endsection
