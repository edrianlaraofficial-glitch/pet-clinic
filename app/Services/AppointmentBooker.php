<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\BusinessHour;
use App\Models\Pet;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AppointmentBooker
{
    public function __construct(private AvailabilityService $availability) {}

    public function book(
        User $user,
        Pet $pet,
        Service $service,
        CarbonInterface $startsAt,
        ?string $customerNotes = null,
    ): Appointment {
        if ((int) $pet->user_id !== (int) $user->getKey()) {
            throw new InvalidArgumentException('This dog does not belong to your account.');
        }

        if (! $service->is_active) {
            throw new SlotUnavailableException('This service is not currently available.');
        }

        $start = CarbonImmutable::instance($startsAt)->setTimezone(config('app.timezone'));

        return DB::transaction(function () use ($user, $pet, $service, $start, $customerNotes): Appointment {
            $this->lockBusinessHours($start);

            if (! $this->containsSlot($this->availability->slotsFor($service, $start), $start)) {
                throw new SlotUnavailableException('That time is no longer available. Please choose another slot.');
            }

            return $user->appointments()->create([
                'pet_id' => $pet->getKey(),
                'service_id' => $service->getKey(),
                'starts_at' => $start,
                'ends_at' => $start->addMinutes($service->duration_minutes),
                'status' => Appointment::PENDING,
                'customer_notes' => $customerNotes,
            ]);
        });
    }

    public function reschedule(Appointment $appointment, CarbonInterface $startsAt): Appointment
    {
        $start = CarbonImmutable::instance($startsAt)->setTimezone(config('app.timezone'));

        return DB::transaction(function () use ($appointment, $start): Appointment {
            $this->lockBusinessHours($start);
            $lockedAppointment = Appointment::query()
                ->whereKey($appointment->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedAppointment->isCancellable()) {
                throw new SlotUnavailableException('This appointment can no longer be rescheduled.');
            }

            if (! $lockedAppointment->service->is_active) {
                throw new SlotUnavailableException('This service is not currently available.');
            }

            if (! $this->containsSlot(
                $this->availability->slotsFor($lockedAppointment->service, $start, $lockedAppointment),
                $start,
            )) {
                throw new SlotUnavailableException('That time is no longer available. Please choose another slot.');
            }

            $lockedAppointment->starts_at = $start;
            $lockedAppointment->ends_at = $start->addMinutes($lockedAppointment->service->duration_minutes);
            $lockedAppointment->status = Appointment::PENDING;
            $lockedAppointment->save();

            return $lockedAppointment;
        });
    }

    private function lockBusinessHours(CarbonImmutable $startsAt): void
    {
        $businessHours = BusinessHour::query()
            ->where('weekday', $startsAt->dayOfWeek)
            ->lockForUpdate()
            ->first();

        if (! $businessHours) {
            throw new SlotUnavailableException('The clinic is not accepting bookings for that day.');
        }
    }

    private function containsSlot(Collection $slots, CarbonImmutable $startsAt): bool
    {
        return $slots->contains(fn (CarbonImmutable $slot): bool => $slot->equalTo($startsAt));
    }
}
