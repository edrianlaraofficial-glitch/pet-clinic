<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\BusinessHour;
use App\Models\BusinessHourException;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class AvailabilityService
{
    public function slotsFor(Service $service, CarbonInterface $date, ?Appointment $except = null): Collection
    {
        $timezone = config('app.timezone');
        $day = CarbonImmutable::instance($date)->setTimezone($timezone)->startOfDay();

        if ($day->isBefore(CarbonImmutable::now($timezone)->startOfDay())) {
            return collect();
        }

        // Check for exception date first
        $exception = BusinessHourException::query()->where('date', $day)->first();
        if ($exception) {
            if ($exception->is_closed || ! $exception->opens_at || ! $exception->closes_at) {
                return collect();
            }
            $opening = $day->setTimeFromTimeString($exception->opens_at);
            $closing = $day->setTimeFromTimeString($exception->closes_at);
        } else {
            $hours = BusinessHour::query()
                ->where('weekday', $day->dayOfWeek)
                ->first();

            if (! $hours || $hours->is_closed || ! $hours->opens_at || ! $hours->closes_at) {
                return collect();
            }

            $opening = $day->setTimeFromTimeString($hours->opens_at);
            $closing = $day->setTimeFromTimeString($hours->closes_at);
        }

        if (! $closing->greaterThan($opening)) {
            return collect();
        }

        $bookedAppointments = Appointment::query()
            ->whereIn('status', Appointment::ACTIVE_STATUSES)
            ->where('starts_at', '<', $closing)
            ->where('ends_at', '>', $opening)
            ->when($except, fn ($query) => $query->where('id', '!=', $except->getKey()))
            ->get(['id', 'starts_at', 'ends_at']);

        $now = CarbonImmutable::now($timezone);
        $slots = collect();

        for ($slot = $opening; $slot->addMinutes($service->duration_minutes)->lessThanOrEqualTo($closing); $slot = $slot->addMinutes(30)) {
            $endsAt = $slot->addMinutes($service->duration_minutes);

            if (! $slot->greaterThan($now)) {
                continue;
            }

            $overlaps = $bookedAppointments->contains(
                fn (Appointment $appointment): bool => $slot->lessThan($appointment->ends_at)
                    && $endsAt->greaterThan($appointment->starts_at),
            );

            if (! $overlaps) {
                $slots->push($slot);
            }
        }

        return $slots;
    }

    public function isDayOpen(CarbonInterface $date): bool
    {
        $timezone = config('app.timezone');
        $day = CarbonImmutable::instance($date)->setTimezone($timezone)->startOfDay();

        $exception = BusinessHourException::query()->where('date', $day)->first();
        if ($exception) {
            return ! $exception->is_closed && $exception->opens_at && $exception->closes_at;
        }

        $hours = BusinessHour::query()->where('weekday', $day->dayOfWeek)->first();

        return $hours !== null && ! $hours->is_closed && $hours->opens_at && $hours->closes_at;
    }

    public function getDayHours(CarbonInterface $date): ?array
    {
        $timezone = config('app.timezone');
        $day = CarbonImmutable::instance($date)->setTimezone($timezone)->startOfDay();

        $exception = BusinessHourException::query()->where('date', $day)->first();
        if ($exception) {
            if ($exception->is_closed) {
                return ['is_closed' => true, 'opens_at' => null, 'closes_at' => null];
            }
            return [
                'is_closed' => false,
                'opens_at' => $exception->opens_at,
                'closes_at' => $exception->closes_at,
            ];
        }

        $hours = BusinessHour::query()->where('weekday', $day->dayOfWeek)->first();
        if (! $hours || $hours->is_closed || ! $hours->opens_at || ! $hours->closes_at) {
            return ['is_closed' => true, 'opens_at' => null, 'closes_at' => null];
        }

        return [
            'is_closed' => false,
            'opens_at' => $hours->opens_at,
            'closes_at' => $hours->closes_at,
        ];
    }
}