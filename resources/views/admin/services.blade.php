@extends('layouts.admin')
@section('title', 'Services')

@section('content')
    <div class="admin-page-heading">
        <div>
            <p class="step-label">Clinic management</p>
            <h1>Services</h1>
            <p class="muted">Manage the care options, pricing, visit length, and online booking availability.</p>
        </div>
        <span class="admin-count-pill">{{ $services->count() }} {{ \Illuminate\Support\Str::plural('service', $services->count()) }}</span>
    </div>

    <section class="admin-service-grid" aria-label="Clinic services">
        @forelse ($services as $service)
            <article class="admin-panel admin-service-card">
                <div class="admin-service-heading">
                    <div>
                        <span class="badge {{ $service->is_active ? '' : 'badge-inactive' }}">{{ $service->is_active ? 'Bookable online' : 'Not bookable' }}</span>
                        <h2>{{ $service->name }}</h2>
                    </div>
                    <span class="admin-service-price">₱{{ number_format($service->price, 2) }}</span>
                </div>
                <p class="admin-service-description">{{ $service->description ?: 'No description added yet.' }}</p>
                <p class="admin-service-duration">{{ $service->duration_minutes }} minute visit</p>

                <details class="admin-service-edit" @if ($errors->any()) open @endif>
                    <summary>Edit service details</summary>
                    <form method="POST" action="{{ route('admin.services.update', $service) }}">
                        @csrf
                        @method('PUT')
                        @include('admin._service_fields', ['service' => $service])
                        <button class="btn btn-primary btn-sm" type="submit">Save service</button>
                    </form>
                </details>

                <form method="POST" action="{{ route('admin.services.toggle', $service) }}" class="admin-service-toggle">
                    @csrf
                    <button class="btn btn-outline btn-sm" type="submit">
                        {{ $service->is_active ? 'Stop online bookings' : 'Allow online bookings' }}
                    </button>
                </form>
            </article>
        @empty
            <div class="admin-panel admin-service-empty">
                <h2>No services yet</h2>
                <p class="muted">Add a clinic service using the form below.</p>
            </div>
        @endforelse
    </section>

    <section class="admin-panel admin-service-create" aria-labelledby="create-service-title">
        <div class="admin-panel-heading">
            <div>
                <p class="step-label">Expand your care menu</p>
                <h2 id="create-service-title">Add a service</h2>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.services.store') }}">
            @csrf
            @include('admin._service_fields', ['service' => null])
            <button class="btn btn-primary" type="submit">Add service</button>
        </form>
    </section>
@endsection
