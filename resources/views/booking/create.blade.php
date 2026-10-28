@extends('layouts.app')
@section('title', 'Book an appointment')
@section('body-class', 'booking-page')

@section('content')
    <div class="booking-heading">
        <p class="step-label">Book a visit</p>
        <h1>Pick a service, your dog, and a time</h1>
        <p class="muted">Times in Philippine time. We only offer slots that are open and free.</p>
    </div>

    <ol class="booking-progress" aria-label="Booking steps">
        <li class="{{ $service ? 'is-complete' : 'is-current' }}">1 · Service</li>
        <li class="{{ $pet ? 'is-complete' : ($service ? 'is-current' : '') }}">2 · Dog</li>
        <li class="{{ $date ? 'is-complete' : ($service && $pet ? 'is-current' : '') }}">3 · Date</li>
        <li class="{{ $slots->isNotEmpty() ? 'is-current' : '' }}">4 · Time</li>
    </ol>

    @if ($pets->isEmpty())
        <div class="alert alert-info booking-alert">
            You don't have a dog on your account yet. <a href="{{ route('pets.index') }}">Add your dog</a> first, then come back to book.
        </div>
    @endif

    <div class="booking-layout">
        <div class="booking-main">
            <form method="GET" action="{{ route('booking.create') }}" class="booking-picker">
                <section class="booking-card">
                    <div class="booking-section-heading">
                        <h2>1 · Service</h2>
                        <span>{{ $service ? 'Selected' : 'Required' }}</span>
                    </div>

                    @if ($services->isNotEmpty())
                        <div class="booking-service-grid">
                            @foreach ($services as $item)
                                <label class="choice booking-choice booking-service-choice">
                                    <input
                                        type="radio"
                                        name="service_id"
                                        value="{{ $item->id }}"
                                        data-name="{{ $item->name }}"
                                        data-price="₱{{ number_format($item->price, 0) }}"
                                        @checked($service?->id === $item->id)
                                        required
                                    >
                                    <span class="booking-option">
                                        <span class="booking-option-name">{{ $item->name }}</span>
                                        <span class="booking-option-meta">{{ $item->duration_minutes }} min</span>
                                        <span class="booking-option-price">₱{{ number_format($item->price, 0) }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <p class="booking-empty">There are no services available to book right now.</p>
                    @endif
                </section>

                <section class="booking-card">
                    <div class="booking-section-heading">
                        <h2>2 · Your dog</h2>
                        <span>{{ $pet ? 'Selected' : 'Required' }}</span>
                    </div>

                    <div class="booking-dog-grid">
                        @foreach ($pets as $item)
                            <label class="choice booking-choice booking-dog-choice">
                                <input
                                    type="radio"
                                    name="pet_id"
                                    value="{{ $item->id }}"
                                    data-name="{{ $item->name }}"
                                    @checked($pet?->id === $item->id)
                                    required
                                >
                                <span class="booking-option">
                                    <span class="booking-pet-icon" aria-hidden="true">🐕</span>
                                    <span class="booking-pet-copy">
                                        <span class="booking-option-name">{{ $item->name }}</span>
                                        <span class="booking-option-meta">{{ $item->breed ?: 'Breed not set' }}</span>
                                    </span>
                                </span>
                            </label>
                        @endforeach
                        <a href="{{ route('pets.index') }}" class="booking-add-dog">
                            <span aria-hidden="true">＋</span> Add a dog
                        </a>
                    </div>
                </section>

                <section class="booking-card booking-date-card">
                    <div class="booking-section-heading">
                        <h2>3 · Date &amp; time</h2>
                        <span>Mon – Sat</span>
                    </div>
                    <p class="booking-help">Choose a preferred date to see available appointment times.</p>
                    <div class="booking-date-controls">
                        <div class="field">
                            <label for="date">Preferred date</label>
                            <input
                                id="date"
                                type="date"
                                name="date"
                                min="{{ $today }}"
                                value="{{ $date?->toDateString() }}"
                                required
                            >
                        </div>
                        <button class="btn btn-primary" type="submit">
                            {{ $date ? 'Update available times' : 'Find available times' }}
                        </button>
                    </div>
                </section>
            </form>

            @if ($service && $pet && $date)
                <section class="booking-card booking-time-card">
                    <div class="booking-section-heading">
                        <h2>Available times</h2>
                        <span>{{ $date->format('D · M j') }}</span>
                    </div>

                    @if ($dayClosed)
                        <p class="booking-empty">We're closed on {{ $date->format('l') }}s. Please choose another date.</p>
                    @elseif ($slots->isEmpty())
                        <p class="booking-empty">No free times left for this {{ $service->duration_minutes }}-minute visit. Try another date.</p>
                    @else
                        <p class="booking-help">All times are shown in Philippine time.</p>
                        <div class="booking-time-grid">
                            @foreach ($slots as $slot)
                                <label class="choice booking-choice booking-time-choice">
                                    <input
                                        type="radio"
                                        name="starts_at"
                                        value="{{ $slot->format('Y-m-d H:i') }}"
                                        data-label="{{ $slot->format('g:i A') }}"
                                        data-end-label="{{ $slot->addMinutes($service->duration_minutes)->format('g:i A') }}"
                                        form="booking-confirm"
                                        @checked(old('starts_at') === $slot->format('Y-m-d H:i'))
                                        required
                                    >
                                    <span class="booking-option">{{ $slot->format('g:i A') }}</span>
                                </label>
                            @endforeach
                        </div>

                        <p class="booking-help booking-struck-note">Only currently available appointment times are shown.</p>
                    @endif
                </section>

                @if (! $dayClosed && $slots->isNotEmpty())
                    <section class="booking-card booking-notes-card">
                        <div class="booking-section-heading">
                            <h2>4 · Anything we should know?</h2>
                            <span>Optional</span>
                        </div>
                        <textarea
                            id="customer_notes"
                            name="customer_notes"
                            maxlength="500"
                            placeholder="e.g. Limping on the left front paw since Monday"
                            form="booking-confirm"
                        >{{ old('customer_notes') }}</textarea>
                    </section>
                @endif
            @endif

        </div>

        <aside class="booking-summary" aria-labelledby="booking-summary-title">
            <h2 id="booking-summary-title">Your visit</h2>
            <dl class="booking-summary-list">
                <div>
                    <dt>Service</dt>
                    <dd id="summary-service">{{ $service?->name ?? 'Choose a service' }}</dd>
                </div>
                <div>
                    <dt>Dog</dt>
                    <dd id="summary-dog">{{ $pet?->name ?? 'Choose your dog' }}</dd>
                </div>
                <div>
                    <dt>Date</dt>
                    <dd id="summary-date">{{ $date?->format('D, M j') ?? 'Choose a date' }}</dd>
                </div>
                <div>
                    <dt>Time</dt>
                    <dd id="summary-time">Choose a time</dd>
                </div>
            </dl>

            <div class="booking-summary-total">
                <span>Estimated total</span>
                <strong id="summary-price">{{ $service ? '₱'.number_format($service->price, 0) : '—' }}</strong>
            </div>

            @if ($service && $pet && $date && ! $dayClosed && $slots->isNotEmpty())
                <form
                    id="booking-confirm"
                    method="POST"
                    action="{{ route('booking.store') }}"
                    class="booking-confirm-form"
                >
                    @csrf
                    <input type="hidden" name="service_id" value="{{ $service->id }}">
                    <input type="hidden" name="pet_id" value="{{ $pet->id }}">
                    <button class="btn btn-primary" type="submit">Confirm booking</button>
                </form>
                <p class="booking-summary-note">
                    This request is not confirmed until the clinic responds.
                    See our <a href="{{ route('policies.privacy') }}">Privacy Policy</a> and <a href="{{ route('policies.veterinary') }}">Veterinary Disclaimer</a>.
                </p>
            @else
                <button class="btn btn-primary" type="button" disabled>Choose a time to continue</button>
                <p class="booking-summary-note">Select a service, dog, and date to see open times.</p>
            @endif
        </aside>
    </div>

@endsection
