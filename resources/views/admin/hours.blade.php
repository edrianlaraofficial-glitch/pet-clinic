@extends('layouts.admin')
@section('title', 'Opening hours')

@section('content')
    <div class="admin-page-heading">
        <div>
            <p class="step-label">Clinic management</p>
            <h1>Opening hours</h1>
            <p class="muted">Set when visits can be booked. Changes update the clinic schedule and available appointment slots.</p>
        </div>
        <div class="admin-hours-toolbar">
            <button type="button" class="btn btn-outline btn-sm" id="copy-week-btn" aria-label="Copy this week's schedule to all weeks">
                <svg viewBox="0 0 24 24" aria-hidden="true" class="icon"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                <span>Copy week</span>
            </button>
            <button type="button" class="btn btn-ghost btn-sm" id="reset-form-btn" aria-label="Reset form to saved values">
                <svg viewBox="0 0 24 24" aria-hidden="true" class="icon"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M21 3v5h-5"/></svg>
                <span>Reset</span>
            </button>
        </div>
    </div>

    <section class="admin-panel admin-hours-settings" aria-labelledby="opening-hours-title">
        <div class="admin-panel-heading">
            <div>
                <p class="step-label">Weekly schedule</p>
                <h2 id="opening-hours-title">Clinic schedule</h2>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.hours.update') }}" class="admin-hours-form" id="hours-form">
            @csrf
            @method('PUT')

            <div class="admin-hours-grid" role="list" aria-label="Days of the week">
                @foreach ($hours as $hour)
                    <?php
                        $isClosed = (bool) old("hours.{$hour->weekday}.is_closed", $hour->is_closed);
                        $isToday = $hour->weekday === now()->dayOfWeek;
                        $isWeekend = in_array($hour->weekday, [0, 6]);
                        $hasHours = !$isClosed && $hour->opens_at && $hour->closes_at;
                    ?>
                    <article class="admin-day-card {{ $isClosed ? 'is-closed' : '' }} {{ $isToday ? 'is-today' : '' }} {{ $isWeekend ? 'is-weekend' : '' }}" role="listitem" data-weekday="{{ $hour->weekday }}">
                        <div class="admin-day-header">
                            <div class="admin-day-info">
                                <h3 class="admin-day-name">{{ $hour->dayName() }}</h3>
                                @if ($isToday)
                                    <span class="admin-day-badge today" aria-label="Today">Today</span>
                                @endif
                                @if ($isWeekend && !$isToday)
                                    <span class="admin-day-badge weekend" aria-label="Weekend">Weekend</span>
                                @endif
                            </div>
                            <label class="admin-day-toggle" for="closed-{{ $hour->weekday }}">
                                <input type="hidden"
                                    name="hours[{{ $hour->weekday }}][is_closed]"
                                    value="0">
                                <input type="checkbox"
                                    id="closed-{{ $hour->weekday }}"
                                    name="hours[{{ $hour->weekday }}][is_closed]"
                                    value="1"
                                    @checked($isClosed)
                                    class="admin-toggle-input"
                                    aria-label="{{ $hour->dayName() }} closed">
                                <span class="admin-toggle-slider" aria-hidden="true"></span>
                                <span class="admin-toggle-label">{{ $isClosed ? 'Closed' : 'Open' }}</span>
                            </label>
                        </div>

                        <div class="admin-day-times">
                            <div class="admin-time-input-group">
                                <label for="opens-{{ $hour->weekday }}" class="admin-time-label">
                                    <svg viewBox="0 0 24 24" aria-hidden="true" class="admin-time-icon"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                    <span>Opens</span>
                                </label>
                                <div class="admin-time-input-wrapper">
                                    <input type="time"
                                        id="opens-{{ $hour->weekday }}"
                                        name="hours[{{ $hour->weekday }}][opens_at]"
                                        value="{{ old("hours.{$hour->weekday}.opens_at", $hour->opens_at ? \Carbon\Carbon::parse($hour->opens_at)->format('H:i') : '') }}"
                                        class="admin-time-input"
                                        aria-label="{{ $hour->dayName() }} opening time"
                                        placeholder="HH:MM"
                                        @required(!$isClosed)
                                        @disabled($isClosed)>
                                </div>
                                <span class="admin-time-hint" aria-hidden="true">e.g. 9:00 AM</span>
                            </div>

                            <div class="admin-time-separator" aria-hidden="true">
                                <span>to</span>
                            </div>

                            <div class="admin-time-input-group">
                                <label for="closes-{{ $hour->weekday }}" class="admin-time-label">
                                    <svg viewBox="0 0 24 24" aria-hidden="true" class="admin-time-icon"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                    <span>Closes</span>
                                </label>
                                <div class="admin-time-input-wrapper">
                                    <input type="time"
                                        id="closes-{{ $hour->weekday }}"
                                        name="hours[{{ $hour->weekday }}][closes_at]"
                                        value="{{ old("hours.{$hour->weekday}.closes_at", $hour->closes_at ? \Carbon\Carbon::parse($hour->closes_at)->format('H:i') : '') }}"
                                        class="admin-time-input"
                                        aria-label="{{ $hour->dayName() }} closing time"
                                        placeholder="HH:MM"
                                        @required(!$isClosed)
                                        @disabled($isClosed)>
                                </div>
                                <span class="admin-time-hint" aria-hidden="true">e.g. 6:00 PM</span>
                            </div>
                        </div>

                        @if ($hasHours)
                            <div class="admin-day-summary" aria-label="Open {{ $hour->opens_at ? \Carbon\Carbon::parse($hour->opens_at)->format('g:i A') : '' }} to {{ $hour->closes_at ? \Carbon\Carbon::parse($hour->closes_at)->format('g:i A') : '' }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true" class="admin-summary-icon"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                <span>
                                    {{ \Carbon\Carbon::parse($hour->opens_at)->format('g:i A') }} – {{ \Carbon\Carbon::parse($hour->closes_at)->format('g:i A') }}
                                    <span class="admin-duration">({{ \Carbon\Carbon::parse($hour->opens_at)->diffInHours(\Carbon\Carbon::parse($hour->closes_at)) }} hrs)</span>
                                </span>
                            </div>
                        @endif

                        @error("hours.{$hour->weekday}.opens_at")
                            <div class="field-error" role="alert">{{ $message }}</div>
                        @enderror
                        @error("hours.{$hour->weekday}.closes_at")
                            <div class="field-error" role="alert">{{ $message }}</div>
                        @enderror
                    </article>
                @endforeach
            </div>

            @error('hours')
                <div class="field-error form-error" role="alert">{{ $message }}</div>
            @enderror

            <div class="admin-hours-actions">
                <p class="muted small admin-hours-hint">
                    <svg viewBox="0 0 24 24" aria-hidden="true" class="hint-icon"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                    Open days require both opening and closing times. Closing time must be later than opening time.
                </p>
                <button class="btn btn-primary" type="submit">
                    <svg viewBox="0 0 24 24" aria-hidden="true" class="icon"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-5a2 2 0 0 1 2-2h2"/></svg>
                    Save opening hours
                </button>
            </div>
        </form>
    </section>

    {{-- Exception dates section --}}
    <section class="admin-panel admin-hours-exceptions" aria-labelledby="exceptions-title">
        <div class="admin-panel-heading">
            <div>
                <p class="step-label">Special dates</p>
                <h2 id="exceptions-title">Exception dates & holidays</h2>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.hours.exceptions.store') }}" class="admin-exceptions-form" id="exception-form">
            @csrf

            <div class="admin-exceptions-form-row">
                <div class="admin-exception-field">
                    <label for="exception-date" class="admin-exception-label">
                        <svg viewBox="0 0 24 24" aria-hidden="true" class="admin-time-icon"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                        <span>Date</span>
                    </label>
                    <input type="date"
                        id="exception-date"
                        name="date"
                        value="{{ old('date', now()->addDay()->format('Y-m-d')) }}"
                        min="{{ now()->format('Y-m-d') }}"
                        class="admin-exception-input"
                        required>
                    @error('date')
                        <div class="field-error" role="alert">{{ $message }}</div>
                    @enderror
                </div>

                <div class="admin-exception-field">
                    <label for="exception-reason" class="admin-exception-label">
                        <svg viewBox="0 0 24 24" aria-hidden="true" class="admin-time-icon"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                        <span>Reason (optional)</span>
                    </label>
                    <input type="text"
                        id="exception-reason"
                        name="reason"
                        value="{{ old('reason') }}"
                        placeholder="e.g. Christmas, Staff training, Vacation"
                        class="admin-exception-input"
                        maxlength="120">
                </div>
            </div>

            <div class="admin-exception-toggle-row">
                <label class="admin-day-toggle" for="exception-closed">
                    <input type="checkbox"
                        id="exception-closed"
                        name="is_closed"
                        value="1"
                        @checked(old('is_closed', true))
                        class="admin-toggle-input"
                        aria-label="Closed all day">
                    <span class="admin-toggle-slider" aria-hidden="true"></span>
                    <span class="admin-toggle-label">Closed all day</span>
                </label>
            </div>

            <div class="admin-exception-times" id="exception-times" style="display: none;">
                <div class="admin-time-input-group">
                    <label for="exception-opens" class="admin-time-label">
                        <svg viewBox="0 0 24 24" aria-hidden="true" class="admin-time-icon"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        <span>Opens</span>
                    </label>
                    <div class="admin-time-input-wrapper">
                        <input type="time"
                            id="exception-opens"
                            name="opens_at"
                            value="{{ old('opens_at') }}"
                            class="admin-time-input"
                            aria-label="Opening time"
                            placeholder="HH:MM">
                    </div>
                </div>

                <div class="admin-time-separator" aria-hidden="true">
                    <span>to</span>
                </div>

                <div class="admin-time-input-group">
                    <label for="exception-closes" class="admin-time-label">
                        <svg viewBox="0 0 24 24" aria-hidden="true" class="admin-time-icon"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        <span>Closes</span>
                    </label>
                    <div class="admin-time-input-wrapper">
                        <input type="time"
                            id="exception-closes"
                            name="closes_at"
                            value="{{ old('closes_at') }}"
                            class="admin-time-input"
                            aria-label="Closing time"
                            placeholder="HH:MM">
                    </div>
                </div>
            </div>

            @error('opens_at')
                <div class="field-error" role="alert">{{ $message }}</div>
            @enderror
            @error('closes_at')
                <div class="field-error" role="alert">{{ $message }}</div>
            @enderror

            <div class="admin-exceptions-actions">
                <button class="btn btn-primary" type="submit">
                    <svg viewBox="0 0 24 24" aria-hidden="true" class="icon"><path d="M12 5v14M5 12h14"/></svg>
                    Add exception date
                </button>
            </div>
        </form>

        @if ($exceptions->count() > 0)
            <div class="admin-exceptions-list" role="list" aria-label="Exception dates">
                @foreach ($exceptions as $exception)
                    <article class="admin-exception-item {{ $exception->is_closed ? 'is-closed' : '' }}" role="listitem" data-date="{{ $exception->date->format('Y-m-d') }}">
                        <div class="admin-exception-info">
                            <div class="admin-exception-date">
                                <svg viewBox="0 0 24 24" aria-hidden="true" class="admin-exception-icon"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                <div>
                                    <strong>{{ $exception->date->format('l, F j, Y') }}</strong>
                                    @if ($exception->reason)
                                        <span class="admin-exception-reason">{{ $exception->reason }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="admin-exception-status">
                                @if ($exception->is_closed)
                                    <span class="badge badge-danger">Closed</span>
                                @else
                                    <span class="badge badge-success">{{ \Carbon\Carbon::parse($exception->opens_at)->format('g:i A') }} – {{ \Carbon\Carbon::parse($exception->closes_at)->format('g:i A') }}</span>
                                @endif
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.hours.exceptions.destroy', $exception) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-ghost btn-sm admin-exception-delete" aria-label="Remove exception for {{ $exception->date->format('F j, Y') }}" data-confirm="Remove this exception date?">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </form>
                    </article>
                @endforeach
            </div>
        @else
            <p class="admin-exceptions-empty muted">No exception dates set. Add holidays, vacation days, or special hours above.</p>
        @endif
    </section>

    {{-- Preview section --}}
    <section class="admin-panel admin-hours-preview" aria-labelledby="preview-title">
        <div class="admin-panel-heading">
            <div>
                <p class="step-label">Preview</p>
                <h2 id="preview-title">How it appears to customers</h2>
            </div>
        </div>
        <div class="admin-preview-grid" id="hours-preview">
            @foreach ($hours as $hour)
                <?php
                    $isToday = $hour->weekday === now()->dayOfWeek;
                    $isWeekend = in_array($hour->weekday, [0, 6]);
                ?>
                <div class="admin-preview-day {{ $hour->is_closed ? 'closed' : '' }} {{ $isToday ? 'today' : '' }}">
                    <span class="admin-preview-day-name">{{ $hour->dayName() }}</span>
                    @if ($hour->is_closed)
                        <span class="admin-preview-status closed">Closed</span>
                    @else
                        <span class="admin-preview-times">
                            {{ $hour->opens_at ? \Carbon\Carbon::parse($hour->opens_at)->format('g:i A') : '—' }} – {{ $hour->closes_at ? \Carbon\Carbon::parse($hour->closes_at)->format('g:i A') : '—' }}
                        </span>
                    @endif
                    @if ($isToday)
                        <span class="admin-preview-today" aria-label="Today">Today</span>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('hours-form');
    const dayCards = form.querySelectorAll('.admin-day-card');
    const copyWeekBtn = document.getElementById('copy-week-btn');
    const resetFormBtn = document.getElementById('reset-form-btn');
    const previewContainer = document.getElementById('hours-preview');

    // Store initial values for reset
    const initialValues = new FormData(form);

    // Toggle closed/open state
    dayCards.forEach(card => {
        const checkbox = card.querySelector('.admin-toggle-input');
        const timeInputs = card.querySelectorAll('.admin-time-input');

        const syncTimeInputs = () => {
            timeInputs.forEach(input => {
                input.disabled = checkbox.checked;
                input.required = !checkbox.checked;
            });
        };

        syncTimeInputs();

        checkbox.addEventListener('change', function() {
            const isClosed = this.checked;
            card.classList.toggle('is-closed', isClosed);
            syncTimeInputs();

            // Update toggle label
            const label = card.querySelector('.admin-toggle-label');
            if (label) label.textContent = isClosed ? 'Closed' : 'Open';

            updatePreview();
        });
    });

    // Update preview on time input changes
    form.querySelectorAll('.admin-time-input').forEach(input => {
        input.addEventListener('change', updatePreview);
        input.addEventListener('blur', updatePreview);
    });

    function updatePreview() {
        const previewDays = previewContainer.querySelectorAll('.admin-preview-day');
        dayCards.forEach((card, index) => {
            const checkbox = card.querySelector('.admin-toggle-input');
            const opensInput = card.querySelector('[name$="[opens_at]"]');
            const closesInput = card.querySelector('[name$="[closes_at]"]');
            const previewDay = previewDays[index];

            if (!previewDay) return;

            const isClosed = checkbox.checked;
            previewDay.classList.toggle('closed', isClosed);

            const statusEl = previewDay.querySelector('.admin-preview-status');
            const timesEl = previewDay.querySelector('.admin-preview-times');

            if (isClosed) {
                if (statusEl) statusEl.textContent = 'Closed';
                if (timesEl) timesEl.style.display = 'none';
            } else {
                const opens = opensInput?.value ? formatTime(opensInput.value) : '—';
                const closes = closesInput?.value ? formatTime(closesInput.value) : '—';
                if (timesEl) {
                    timesEl.textContent = `${opens} – ${closes}`;
                    timesEl.style.display = 'inline';
                }
                if (statusEl) statusEl.style.display = 'none';
            }
        });
    }

    function formatTime(time24) {
        const [hours, minutes] = time24.split(':');
        const h = parseInt(hours, 10);
        const ampm = h >= 12 ? 'PM' : 'AM';
        const h12 = h % 12 || 12;
        return `${h12}:${minutes} ${ampm}`;
    }

    // Copy week functionality
    copyWeekBtn.addEventListener('click', function() {
        const firstCard = dayCards[0];
        const firstCheckbox = firstCard.querySelector('.admin-toggle-input');
        const firstOpens = firstCard.querySelector('[name$="[opens_at]"]');
        const firstCloses = firstCard.querySelector('[name$="[closes_at]"]');

        const isClosed = firstCheckbox.checked;
        const opensVal = firstOpens?.value || '';
        const closesVal = firstCloses?.value || '';

        dayCards.forEach((card, index) => {
            if (index === 0) return; // Skip first day

            const checkbox = card.querySelector('.admin-toggle-input');
            const opensInput = card.querySelector('[name$="[opens_at]"]');
            const closesInput = card.querySelector('[name$="[closes_at]"]');

            checkbox.checked = isClosed;
            card.classList.toggle('is-closed', isClosed);

            if (opensInput) {
                opensInput.value = opensVal;
            }
            if (closesInput) {
                closesInput.value = closesVal;
            }

            const label = card.querySelector('.admin-toggle-label');
            if (label) label.textContent = isClosed ? 'Closed' : 'Open';
        });

        updatePreview();

        // Visual feedback
        const originalText = this.innerHTML;
        this.innerHTML = `<svg viewBox="0 0 24 24" aria-hidden="true" class="icon"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg><span>Copied!</span>`;
        this.classList.add('btn-success');
        setTimeout(() => {
            this.innerHTML = originalText;
            this.classList.remove('btn-success');
        }, 1500);
    });

    // Reset form
    resetFormBtn.addEventListener('click', function() {
        if (confirm('Reset all changes to the last saved values?')) {
            // Reload the page to get original values
            window.location.reload();
        }
    });

    // Keyboard support for toggle
    dayCards.forEach(card => {
        const checkbox = card.querySelector('.admin-toggle-input');
        const label = card.querySelector('.admin-day-toggle');

        label.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                checkbox.click();
            }
        });
    });

    // Exception dates form handling
    const exceptionForm = document.getElementById('exception-form');
    const exceptionClosedCheckbox = document.getElementById('exception-closed');
    const exceptionTimes = document.getElementById('exception-times');
    const exceptionOpens = document.getElementById('exception-opens');
    const exceptionCloses = document.getElementById('exception-closes');

    // Toggle time inputs based on closed checkbox
    exceptionClosedCheckbox.addEventListener('change', function() {
        const isClosed = this.checked;
        exceptionTimes.style.display = isClosed ? 'none' : 'flex';

        if (isClosed) {
            exceptionOpens.removeAttribute('required');
            exceptionCloses.removeAttribute('required');
            exceptionOpens.value = '';
            exceptionCloses.value = '';
        } else {
            exceptionOpens.setAttribute('required', '');
            exceptionCloses.setAttribute('required', '');
        }
    });

    // Initialize state
    exceptionTimes.style.display = exceptionClosedCheckbox.checked ? 'none' : 'flex';
    if (!exceptionClosedCheckbox.checked) {
        exceptionOpens.setAttribute('required', '');
        exceptionCloses.setAttribute('required', '');
    }

    // Confirmation for delete buttons
    document.querySelectorAll('.admin-exception-delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endpush