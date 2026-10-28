@extends('layouts.admin')
@section('title', 'Appointments')

@section('content')
    <div class="admin-page-heading admin-dashboard-heading">
        <div>
            <p class="step-label">Today at the clinic</p>
            <h1>{{ $greeting }}, {{ auth('admin')->user()->name }}</h1>
            <p class="muted">{{ $todayVisits }} visits booked, {{ $needsConfirmation }} waiting for confirmation</p>
        </div>
        <div class="admin-date-controls">
            <div class="pill-nav" role="tablist" aria-label="Appointment date range">
                <a class="{{ $range === 'day' ? 'active' : '' }}" href="{{ route('admin.dashboard', ['range' => 'day', 'date' => $anchor->toDateString()]) }}">Day</a>
                <a class="{{ $range === 'week' ? 'active' : '' }}" href="{{ route('admin.dashboard', ['range' => 'week', 'date' => $anchor->toDateString()]) }}">Week</a>
            </div>
            <a class="btn btn-ghost btn-sm" href="{{ route('admin.dashboard', ['range' => $range, 'date' => $anchor->copy()->subDays($range === 'week' ? 7 : 1)->toDateString()]) }}">← Prev</a>
            <span class="admin-date-label">{{ $anchor->format('D, M j') }}</span>
            <a class="btn btn-ghost btn-sm" href="{{ route('admin.dashboard', ['range' => $range, 'date' => $anchor->copy()->addDays($range === 'week' ? 7 : 1)->toDateString()]) }}">Next →</a>
        </div>
    </div>

    <section class="admin-stat-grid" aria-label="Clinic overview">
        <article class="admin-stat-card">
            <span class="admin-stat-icon admin-stat-paw" aria-hidden="true">🐾</span>
            <div><span class="admin-stat-label">Today's visits</span><strong>{{ $todayVisits }}</strong><small>Scheduled appointments</small></div>
        </article>
        <article class="admin-stat-card">
            <span class="admin-stat-icon admin-stat-clock" aria-hidden="true">◷</span>
            <div><span class="admin-stat-label">Needs confirmation</span><strong>{{ $needsConfirmation }}</strong><small>Pending for today</small></div>
        </article>
        <article class="admin-stat-card">
            <span class="admin-stat-icon admin-stat-check" aria-hidden="true">✓</span>
            <div><span class="admin-stat-label">Completed this week</span><strong>{{ $completedThisWeek }}</strong><small>Appointments completed</small></div>
        </article>
        <article class="admin-stat-card">
            <span class="admin-stat-icon admin-stat-alert" aria-hidden="true">△</span>
            <div><span class="admin-stat-label">No-shows this week</span><strong>{{ $noShowsThisWeek }}</strong><small>Track follow-up if needed</small></div>
        </article>
    </section>

    <div class="admin-dashboard-grid">
        <section class="admin-panel admin-appointments-panel">
            <div class="admin-panel-heading admin-appointments-heading">
                <div>
                    <p class="step-label">Clinic schedule</p>
                    <h2>Appointments · {{ $range === 'week' ? 'Week of '.$from->format('M j') : $anchor->format('l') }}</h2>
                </div>
                <span class="admin-count-pill">{{ $appointments->count() }} visits</span>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Customer & dog</th>
                            <th>Service</th>
                            <th>Customer notes</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($appointments as $appointment)
                            <tr>
                                <td>
                                    <strong>{{ $appointment->starts_at->format('g:i A') }}</strong>
                                    @if ($range === 'week')<br><span class="muted small">{{ $appointment->starts_at->format('D, M j') }}</span>@endif
                                </td>
                                <td>
                                    <strong>{{ $appointment->user->profile?->full_name ?? $appointment->user->name }}</strong><br>
                                    <span class="muted small">{{ $appointment->pet->name }} · {{ $appointment->pet->breed }}</span>
                                </td>
                                <td><strong>{{ $appointment->service->name }}</strong><br><span class="muted small">{{ $appointment->service->duration_minutes }} min</span></td>
                                <td class="admin-note-cell">
                                    @if ($appointment->customer_notes)
                                        {{ $appointment->customer_notes }}
                                    @else
                                        <span class="muted">—</span>
                                    @endif
                                    @if ($appointment->pet->notes)
                                        <span class="admin-vet-note"><strong>Vet notes:</strong> {{ $appointment->pet->notes }}</span>
                                    @endif
                                </td>
                                <td><span class="badge badge-{{ $appointment->status }}">{{ $appointment->statusLabel() }}</span></td>
                                <td>
                                    <div class="admin-action-list">
                                        @foreach (['confirm' => 'Confirm', 'complete' => 'Complete', 'no_show' => 'No-show', 'cancel' => 'Cancel'] as $action => $label)
                                            @php
                                                [$allowedFrom] = \App\Models\Appointment::TRANSITIONS[$action];
                                                $enabled = in_array($appointment->status, $allowedFrom, true);
                                            @endphp
                                            <form method="POST" action="{{ route('admin.appointments.transition', [$appointment, $action]) }}"
                                                  @if ($action === 'cancel') data-confirm="Cancel this appointment?" @endif>
                                                @csrf
                                                <button type="submit" class="btn btn-sm {{ $action === 'cancel' ? 'btn-ghost' : ($action === 'confirm' ? 'btn-primary' : 'btn-outline') }}" @disabled(! $enabled)>{{ $label }}</button>
                                            </form>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="muted">No appointments in this {{ $range === 'week' ? 'week' : 'day' }}.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="admin-sidebar">
            <section class="admin-notice-card">
                <p class="step-label">Heads up</p>
                <h2>{{ $needsConfirmation ? 'Visits need confirmation' : 'All caught up' }}</h2>
                <p>
                    @if ($needsConfirmation)
                        {{ $needsConfirmation }} appointment{{ $needsConfirmation === 1 ? '' : 's' }} still need{{ $needsConfirmation === 1 ? 's' : '' }} your confirmation today.
                    @else
                        No appointments are waiting for confirmation today.
                    @endif
                </p>
            </section>

            <section class="admin-panel admin-chair-card">
                <div class="admin-panel-heading">
                    <div><p class="step-label">Today's schedule</p><h2>Chair time</h2></div>
                </div>
                @if ($todayBookedMinutes)
                    <p class="admin-chair-summary"><strong>{{ number_format($todayBookedMinutes / 60, 1) }} hrs</strong> booked today</p>
                @else
                    <p class="admin-chair-summary">No active appointments booked today.</p>
                @endif
            </section>
        </aside>
    </div>
@endsection
