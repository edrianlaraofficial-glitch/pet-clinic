@extends('layouts.app')
@section('title', 'Privacy Policy')

@section('content')
    <article class="policy-page">
        <header class="policy-header">
            <p class="step-label">Your information</p>
            <h1>Privacy Policy</h1>
            <p class="muted">How Wade-County Animal Clinic handles information through this website and appointment service.</p>
        </header>

        @include('policies._status')
        @include('policies._navigation')

        <section>
            <h2>1. Who is responsible</h2>
            <p>Wade-County Animal Clinic, at Garcia St., P-6, Poblacion, Trento, 8505 Agusan del Sur, Philippines, is the proposed personal information controller for information collected through this service. Confirm the clinic's legal entity, responsible contact, and privacy contact before publication.</p>
        </section>

        <section>
            <h2>2. Information this website collects</h2>
            <ul>
                <li><strong>Account information:</strong> name, email address, optional phone number, and a password stored as a hash rather than readable text.</li>
                <li><strong>Pet information:</strong> dog name, optional breed and date of birth, and notes entered by the account holder.</li>
                <li><strong>Appointment information:</strong> selected service, pet, requested date and time, appointment status, and optional customer notes.</li>
                <li><strong>Technical and session information:</strong> data needed to maintain signed-in sessions, protect forms, and operate the hosting environment. Confirm the host's access, logging, and retention practices.</li>
            </ul>
            <p>This application currently provides appointment and pet-profile fields; it does not provide a separate medical-document or payment-processing feature. Do not submit information that is not needed for arranging care.</p>
        </section>

        <section>
            <h2>3. Why information is used</h2>
            <p>Information is used to create and secure accounts, coordinate appointment requests, maintain the pet and appointment details submitted through the service, respond to requests, operate the website, and meet applicable legal obligations. The clinic must document and confirm the lawful basis applicable to each purpose under the Philippine Data Privacy Act of 2012 (Republic Act No. 10173) and related rules. This notice does not itself create blanket consent for all processing.</p>
        </section>

        <section>
            <h2>4. Access and service providers</h2>
            <p>Account holders can access their own pet and appointment information. Authorized clinic administrators can access information needed to manage the service. The clinic should apply least-privilege access and confirm which staff roles are authorized.</p>
            <p>Hosting, email, database, backup, or storage providers may process information on the clinic's behalf, depending on the deployment. <strong>[Clinic: list actual provider names, purposes, locations, and applicable safeguards before publication.]</strong> The homepage also links to Google Maps; following that link takes you to Google, which may process connection information under its own terms. This codebase currently has no payment gateway or analytics integration identified; verify production integrations before relying on that statement.</p>
        </section>

        <section>
            <h2>5. Retention and disposal</h2>
            <p><strong>[Clinic: set retention periods for accounts, pet notes, appointments, logs, backups, and email records based on operational and legal requirements.]</strong> When information is no longer needed and retention is not legally required, the clinic should securely delete or irreversibly de-identify it, including from backups according to a documented schedule.</p>
        </section>

        <section>
            <h2>6. Your rights and requests</h2>
            <p>Subject to applicable law and verification of identity, individuals may have rights to be informed, access or correct information, object to or restrict certain processing, request erasure or blocking, and seek redress. Some requests may be limited by legal duties or the need to retain appointment records. Contact <strong>[Clinic: privacy contact email and/or phone]</strong> to make a request. The clinic should provide a response process and identity-verification method that does not collect excessive information.</p>
        </section>

        <section>
            <h2>7. Safeguards and incidents</h2>
            <p>The application uses authenticated customer and administrator areas, server-side ownership checks, password hashing, request validation, and secure session protections configured for deployment. No website can guarantee absolute security. The clinic must also secure its hosting, devices, staff accounts, backups, and provider credentials, and maintain an incident response procedure. Report a suspected privacy incident to <strong>[Clinic: privacy/security contact]</strong>. The clinic should assess notification obligations and notify affected individuals and the National Privacy Commission when required by applicable rules.</p>
        </section>

        <section>
            <h2>8. Cross-border processing and changes</h2>
            <p><strong>[Clinic: identify whether any hosting, email, backup, or other provider stores or accesses information outside the Philippines and describe the applicable safeguards, or state that none is used after verification.]</strong> The clinic should date and version each approved update to this policy and communicate material changes appropriately.</p>
        </section>

        <section>
            <h2>9. Contact</h2>
            <address>Wade-County Animal Clinic<br>Garcia St., P-6, Poblacion,<br>Trento, 8505 Agusan del Sur, Philippines<br><strong>[Clinic: add verified privacy contact email and telephone number]</strong></address>
        </section>
    </article>
@endsection
