<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Services\AppointmentBooker;
use App\Services\AvailabilityService;
use App\Services\SlotUnavailableException;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $appointments = $request->user()->appointments()
            ->with(['pet', 'service'])
            ->orderBy('starts_at')
            ->get();
        $upcoming = $appointments->filter(fn (Appointment $appointment): bool => $appointment->starts_at->isFuture());
        $past = $appointments->reject(fn (Appointment $appointment): bool => $appointment->starts_at->isFuture())
            ->sortByDesc('starts_at');

        return view('appointments.index', [
            'upcoming' => $upcoming,
            'past' => $past,
        ]);
    }

    public function cancel(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->ensureOwnedBy($request, $appointment);

        $cancelled = DB::transaction(function () use ($appointment): bool {
            $lockedAppointment = Appointment::query()
                ->whereKey($appointment->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedAppointment->isCancellable()) {
                return false;
            }

            $lockedAppointment->status = Appointment::CANCELLED;
            $lockedAppointment->save();

            return true;
        });

        if (! $cancelled) {
            return back()->withErrors(['appointment' => 'This appointment can no longer be cancelled.']);
        }

        return redirect()->route('my-bookings')->with('status', 'Your appointment was cancelled.');
    }

    public function reschedule(
        Request $request,
        Appointment $appointment,
        AvailabilityService $availability,
    ): View {
        $this->ensureOwnedBy($request, $appointment);

        abort_unless($appointment->isCancellable(), 404);

        $validated = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $date = isset($validated['date'])
            ? CarbonImmutable::createFromFormat('!Y-m-d', $validated['date'], config('app.timezone'))
            : null;
        $slots = $date
            ? $availability->slotsFor($appointment->service, $date, $appointment)
            : collect();

        return view('appointments.reschedule', [
            'appointment' => $appointment->load(['pet', 'service']),
            'date' => $date,
            'today' => now(config('app.timezone'))->toDateString(),
            'slots' => $slots,
        ]);
    }

    public function updateSchedule(
        Request $request,
        Appointment $appointment,
        AppointmentBooker $booker,
    ): RedirectResponse {
        $this->ensureOwnedBy($request, $appointment);

        $validated = $request->validate([
            'starts_at' => ['required', 'date_format:Y-m-d H:i'],
        ]);
        $startsAt = CarbonImmutable::createFromFormat(
            '!Y-m-d H:i',
            $validated['starts_at'],
            config('app.timezone'),
        );

        try {
            $booker->reschedule($appointment, $startsAt);
        } catch (SlotUnavailableException $exception) {
            return back()->withErrors(['starts_at' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('my-bookings')->with('status', 'Your appointment was rescheduled and is awaiting confirmation.');
    }

    private function ensureOwnedBy(Request $request, Appointment $appointment): void
    {
        abort_unless((int) $appointment->user_id === (int) $request->user()->getKey(), 404);
    }
}
