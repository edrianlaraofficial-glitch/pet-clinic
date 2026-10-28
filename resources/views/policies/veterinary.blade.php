@extends('layouts.app')
@section('title', 'Veterinary and Emergency Disclaimer')

@section('content')
    <article class="policy-page">
        <header class="policy-header">
            <p class="step-label">When your pet needs care</p>
            <h1>Veterinary and Emergency Disclaimer</h1>
            <p class="muted">Important limits of online information and appointment requests.</p>
        </header>

        @include('policies._status')
        @include('policies._navigation')

        <section class="policy-emergency">
            <h2>For an urgent or life-threatening problem</h2>
            <p>Do not wait for a website response or an unconfirmed appointment. Contact a veterinarian or emergency animal-care provider immediately. <strong>[Clinic: add a verified emergency phone number, current availability, and locally appropriate emergency instructions before publication.]</strong></p>
        </section>

        <section>
            <h2>Online requests are not confirmation</h2>
            <p>Submitting an appointment request does not mean the clinic has accepted or confirmed the appointment. Wait for direct confirmation from the clinic. If your pet's condition is worsening, seek veterinary assessment rather than waiting for an online booking.</p>
        </section>

        <section>
            <h2>Website information is not a diagnosis</h2>
            <p>Website content and general communications are informational and cannot diagnose or treat an animal. They do not replace an examination, professional veterinary judgment, or advice from a veterinarian who has assessed your pet.</p>
        </section>

        <section>
            <h2>Clinic location and contact</h2>
            <address>Wade-County Animal Clinic<br>Garcia St., P-6, Poblacion,<br>Trento, 8505 Agusan del Sur, Philippines</address>
            <p>See the homepage for current clinic opening hours. <strong>[Clinic: confirm the address and add a monitored phone number and emergency contact instructions.]</strong></p>
        </section>
    </article>
@endsection
