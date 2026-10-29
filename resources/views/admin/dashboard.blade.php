@extends('layouts.admin')
@section('title', 'Appointments')

@section('content')
    <div class="admin-page-heading admin-dashboard-heading">
        <div>
            <p class="step-label">Today at the clinic</p>
            <h1>{{ $greeting }}, {{ auth('admin')->user()->name }}</h1>
            <p class="muted">Here’s what’s happening at Wade-County Animal Clinic today.</p>
        </div>
        <div class="admin-date-controls">
            <div class="pill-nav" role="tablist" aria-label="Appointment date range">
                <a class="{{ $range === 'day' ? 'active' : '' }}" href="{{ route('admin.dashboard', ['range' => 'day', 'date' => $anchor->toDateString()]) }}">Day</a>
                <a class="{{ $range === 'week' ? 'active' : '' }}" href="{{ route('admin.dashboard', ['range' => 'week', 'date' => $anchor->toDateString()]) }}">Week</a>
            </div>
            <form class="admin-date-picker" id="dashboard-date-form" action="{{ route('admin.dashboard') }}" method="GET">
                <a class="admin-date-step" aria-label="Previous {{ $range }}" href="{{ route('admin.dashboard', ['range' => $range, 'date' => $anchor->copy()->subDays($range === 'week' ? 7 : 1)->toDateString()]) }}">‹</a>
                <input type="hidden" name="range" value="{{ $range }}">
                <label class="sr-only" for="dashboard-date">Choose appointment date</label>
                <input id="dashboard-date" type="date" name="date" value="{{ $anchor->toDateString() }}">
                <a class="admin-date-step" aria-label="Next {{ $range }}" href="{{ route('admin.dashboard', ['range' => $range, 'date' => $anchor->copy()->addDays($range === 'week' ? 7 : 1)->toDateString()]) }}">›</a>
            </form>
            <a class="btn btn-ghost btn-sm admin-today-link" href="{{ route('admin.dashboard', ['range' => $range]) }}">Today</a>
        </div>
    </div>

    <section class="admin-stat-grid" aria-label="Clinic overview">
        <article class="admin-stat-card">
            <span class="admin-stat-icon admin-stat-paw" aria-hidden="true">▦</span>
            <div><span class="admin-stat-label">Today’s bookings</span><strong>{{ $todayVisits }}</strong><small>Active appointments</small></div>
        </article>
        <article class="admin-stat-card">
            <span class="admin-stat-icon admin-stat-clock" aria-hidden="true">◷</span>
            <div><span class="admin-stat-label">Pending confirmation</span><strong>{{ $needsConfirmation }}</strong><small>Awaiting your review</small></div>
        </article>
        <article class="admin-stat-card">
            <span class="admin-stat-icon admin-stat-check" aria-hidden="true">✓</span>
            <div><span class="admin-stat-label">Confirmed today</span><strong>{{ $confirmedToday }}</strong><small>Ready for their visit</small></div>
        </article>
        <article class="admin-stat-card">
            <span class="admin-stat-icon admin-stat-completed" aria-hidden="true">✓</span>
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
                <div class="admin-appointments-tools">
                    <label class="admin-appointment-search">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 5 5"/></svg>
                        <span class="sr-only">Search by customer, pet, or service</span>
                        <input type="search" data-appointment-search placeholder="Search customer, pet, service…" autocomplete="off">
                    </label>
                    <span class="admin-count-pill">{{ $appointments->count() }} visits</span>
                </div>
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
                            @php
                                $appointmentSearchText = \Illuminate\Support\Str::lower(implode(' ', array_filter([
                                    $appointment->user->profile?->full_name ?? $appointment->user->name,
                                    $appointment->pet->name,
                                    $appointment->pet->breed,
                                    $appointment->service->name,
                                    $appointment->customer_notes,
                                ])));
                            @endphp
                            <tr data-appointment-row data-search="{{ $appointmentSearchText }}">
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
                <p class="admin-search-empty" data-appointment-search-empty hidden>No appointments match your search.</p>
            </div>
        </section>

        <aside class="admin-sidebar">
            <section class="admin-panel admin-booking-overview" aria-labelledby="booking-overview-title">
                <div class="admin-panel-heading">
                    <div><p class="step-label">This week</p><h2 id="booking-overview-title">Booking overview</h2></div>
                </div>
                <div class="admin-week-chart" role="img" aria-label="Weekly appointments chart: scheduled and completed appointments by day">
                    @foreach ($weeklyOverview as $day)
                        @php
                            $totalForDay = $day['scheduled'] + $day['completed'];
                            $scheduledHeight = (int) round($day['scheduled'] / $weeklyOverviewMax * 100);
                            $completedHeight = (int) round($day['completed'] / $weeklyOverviewMax * 100);
                        @endphp
                        <div class="admin-week-chart-day" aria-label="{{ $day['date']->format('l M j') }}: {{ $day['scheduled'] }} scheduled, {{ $day['completed'] }} completed">
                            <span class="admin-week-chart-count">{{ $totalForDay ?: '' }}</span>
                            <span class="admin-week-chart-column">
                                @if ($totalForDay)
                                    <span class="admin-week-chart-scheduled" style="height: {{ $scheduledHeight }}%"></span>
                                    @if ($day['completed'])
                                        <span class="admin-week-chart-completed" style="height: {{ $completedHeight }}%"></span>
                                    @endif
                                @endif
                            </span>
                            <span class="admin-week-chart-label">{{ $day['date']->format('D') }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="admin-week-chart-legend">
                    <span><i class="scheduled" aria-hidden="true"></i> Scheduled</span>
                    <span><i class="completed" aria-hidden="true"></i> Completed</span>
                </div>
                <div class="admin-week-summary">
                    <span class="admin-week-summary-icon" aria-hidden="true">✓</span>
                    <div><strong>{{ $weeklyOverview->sum(fn (array $day): int => $day['scheduled'] + $day['completed']) }}</strong><span>active and completed visits this week</span></div>
                </div>
            </section>

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
                    <div><p class="step-label">Today at the clinic</p><h2>Booked care time</h2></div>
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
