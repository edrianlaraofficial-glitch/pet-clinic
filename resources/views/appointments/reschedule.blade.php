@extends('layouts.app')
@section('title', 'Reschedule appointment')

@section('content')
    <div class="page-head">
        <div>
            <p class="step-label">Reschedule</p>
            <h1>Move {{ $appointment->pet->name }}'s visit</h1>
            <p class="muted">
                {{ $appointment->service->name }} · currently {{ $appointment->starts_at->format('D, M j \a\t g:i A') }}.
                Changing the time sets the booking back to pending until we re-confirm.
            </p>
        </div>
        <a href="{{ route('my-bookings') }}" class="btn btn-ghost">← Back to my bookings</a>
    </div>

    <form method="GET" action="{{ route('appointments.reschedule', $appointment) }}" class="card row" style="align-items:end">
        <div class="field" style="margin:0;min-width:220px">
            <label for="date">New date</label>
            <input id="date" type="date" name="date" min="{{ $today }}" value="{{ $date?->toDateString() }}" required>
        </div>
        <button class="btn btn-primary" type="submit">Find times</button>
    </form>

    @if ($date)
        <section class="card" style="margin-top:1.25rem">
            <h2>{{ $date->format('l, F j, Y') }}</h2>

            @if ($slots->isEmpty())
                <p class="muted">No free times on this day. Try another date.</p>
            @else
                <form method="POST" action="{{ route('appointments.update-schedule', $appointment) }}" class="stack">
                    @csrf @method('PUT')
                    <div class="slot-grid">
                        @foreach ($slots as $slot)
                            <label class="choice">
                                <input type="radio" name="starts_at" value="{{ $slot->format('Y-m-d H:i') }}" required>
                                <span>{{ $slot->format('g:i A') }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('starts_at') <div class="field-error">{{ $message }}</div> @enderror
                    <div><button class="btn btn-primary" type="submit">Confirm new time</button></div>
                </form>
            @endif
        </section>
    @endif
@endsection
