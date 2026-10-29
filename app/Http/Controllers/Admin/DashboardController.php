<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'range' => ['sometimes', 'in:day,week'],
            'date' => ['sometimes', 'date_format:Y-m-d'],
        ]);

        $timezone = config('app.timezone');
        $today = CarbonImmutable::now($timezone);
        $anchor = CarbonImmutable::createFromFormat(
            '!Y-m-d',
            $filters['date'] ?? $today->toDateString(),
            $timezone,
        );
        $range = $filters['range'] ?? 'day';
        $from = $range === 'week'
            ? $anchor->startOfWeek(CarbonImmutable::MONDAY)
            : $anchor->startOfDay();
        $to = $range === 'week' ? $from->addWeek()->subSecond() : $from->endOfDay();
        $appointments = Appointment::query()
            ->with(['user.profile', 'pet', 'service'])
            ->whereBetween('starts_at', [$from, $to])
            ->orderBy('starts_at')
            ->get();
        $todayFrom = $today->startOfDay();
        $todayTo = $today->endOfDay();
        $weekFrom = $today->startOfWeek(CarbonImmutable::MONDAY);
        $weekTo = $today->endOfWeek(CarbonImmutable::SUNDAY);
        $weeklyAppointments = Appointment::query()
            ->whereBetween('starts_at', [$weekFrom, $weekTo])
            ->where('status', '!=', Appointment::CANCELLED)
            ->get(['starts_at', 'status']);
        $weeklyOverview = collect(range(0, 6))->map(function (int $dayOffset) use ($weekFrom, $weeklyAppointments): array {
            $date = $weekFrom->addDays($dayOffset);
            $appointmentsForDay = $weeklyAppointments->filter(
                fn (Appointment $appointment): bool => $appointment->starts_at->isSameDay($date),
            );

            return [
                'date' => $date,
                'scheduled' => $appointmentsForDay->whereIn('status', Appointment::ACTIVE_STATUSES)->count(),
                'completed' => $appointmentsForDay->where('status', Appointment::COMPLETED)->count(),
            ];
        });
        $todayAppointments = Appointment::query()
            ->whereBetween('starts_at', [$todayFrom, $todayTo])
            ->whereIn('status', Appointment::ACTIVE_STATUSES);
        $todayVisits = (clone $todayAppointments)->count();
        $needsConfirmation = (clone $todayAppointments)
            ->where('status', Appointment::PENDING)
            ->count();
        $confirmedToday = (clone $todayAppointments)
            ->where('status', Appointment::CONFIRMED)
            ->count();
        $todayBookedMinutes = (clone $todayAppointments)
            ->get(['starts_at', 'ends_at'])
            ->sum(fn (Appointment $appointment): float => $appointment->starts_at->diffInMinutes($appointment->ends_at));

        return view('admin.dashboard', [
            'range' => $range,
            'anchor' => $anchor,
            'from' => $from,
            'to' => $to,
            'appointments' => $appointments,
            'greeting' => match (true) {
                $today->hour < 12 => 'Good morning',
                $today->hour < 17 => 'Good afternoon',
                default => 'Good evening',
            },
            'todayVisits' => $todayVisits,
            'needsConfirmation' => $needsConfirmation,
            'confirmedToday' => $confirmedToday,
            'weeklyOverview' => $weeklyOverview,
            'weeklyOverviewMax' => max(1, $weeklyOverview->max(fn (array $day): int => $day['scheduled'] + $day['completed'])),
            'completedThisWeek' => Appointment::query()
                ->whereBetween('starts_at', [$weekFrom, $weekTo])
                ->where('status', Appointment::COMPLETED)
                ->count(),
            'noShowsThisWeek' => Appointment::query()
                ->whereBetween('starts_at', [$weekFrom, $weekTo])
                ->where('status', Appointment::NO_SHOW)
                ->count(),
            'todayBookedMinutes' => (int) $todayBookedMinutes,
        ]);
    }

    public function transition(Request $request, Appointment $appointment, string $action): RedirectResponse
    {
        $transition = Appointment::TRANSITIONS[$action] ?? null;
        abort_unless($transition, 404);
        [$allowedFrom, $nextStatus] = $transition;
        $updated = DB::transaction(function () use ($appointment, $allowedFrom, $nextStatus): bool {
            $lockedAppointment = Appointment::query()
                ->whereKey($appointment->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($lockedAppointment->status, $allowedFrom, true)) {
                return false;
            }

            $lockedAppointment->status = $nextStatus;
            $lockedAppointment->save();

            return true;
        });

        if (! $updated) {
            return back()->withErrors(['appointment' => 'That status change is no longer allowed.']);
        }

        return back()->with('status', 'Appointment marked '.$nextStatus.'.');
    }
}
