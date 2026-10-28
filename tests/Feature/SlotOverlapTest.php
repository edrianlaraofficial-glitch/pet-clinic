<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\Service;
use App\Models\User;
use App\Services\AppointmentBooker;
use App\Services\AvailabilityService;
use App\Services\SlotUnavailableException;
use Carbon\CarbonImmutable;
use Database\Seeders\BusinessHourSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlotOverlapTest extends TestCase
{
    use RefreshDatabase;

    private Service $checkup;

    private Pet $pet;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BusinessHourSeeder::class);

        $this->checkup = Service::create([
            'name' => 'Checkup', 'duration_minutes' => 30, 'price' => 800, 'is_active' => true,
        ]);

        $this->owner = User::create([
            'name' => 'Ana', 'email' => 'ana@example.test', 'password' => bcrypt('password'),
        ]);
        $this->pet = $this->owner->pets()->create(['name' => 'Biscuit']);
    }

    /** Next Monday at midnight in clinic time: always a future, open day. */
    private function nextMonday(): CarbonImmutable
    {
        return CarbonImmutable::now(config('app.timezone'))->addWeek()->startOfWeek(CarbonImmutable::MONDAY);
    }

    public function test_slots_are_generated_within_opening_hours(): void
    {
        $slots = app(AvailabilityService::class)->slotsFor($this->checkup, $this->nextMonday());

        // 09:00 to 18:00 in 30-min steps for a 30-min visit = 18 slots.
        $this->assertCount(18, $slots);
        $this->assertSame('09:00', $slots->first()->format('H:i'));
        $this->assertSame('17:30', $slots->last()->format('H:i'));
    }

    public function test_closed_sunday_has_no_slots(): void
    {
        $sunday = $this->nextMonday()->addDays(6);

        $this->assertCount(0, app(AvailabilityService::class)->slotsFor($this->checkup, $sunday));
    }

    public function test_booked_time_is_removed_from_available_slots(): void
    {
        $day = $this->nextMonday();

        app(AppointmentBooker::class)->book($this->owner, $this->pet, $this->checkup, $day->setTime(10, 0));

        $slots = app(AvailabilityService::class)->slotsFor($this->checkup, $day);

        $this->assertFalse($slots->contains(fn ($s) => $s->format('H:i') === '10:00'));
        $this->assertTrue($slots->contains(fn ($s) => $s->format('H:i') === '10:30'));
    }

    public function test_two_bookings_cannot_take_the_same_time(): void
    {
        $day = $this->nextMonday();
        $booker = app(AppointmentBooker::class);

        $booker->book($this->owner, $this->pet, $this->checkup, $day->setTime(11, 0));

        $other = User::create(['name' => 'Ben', 'email' => 'ben@example.test', 'password' => bcrypt('password')]);
        $otherPet = $other->pets()->create(['name' => 'Luna']);

        $this->expectException(SlotUnavailableException::class);
        $booker->book($other, $otherPet, $this->checkup, $day->setTime(11, 0));
    }

    public function test_overlapping_longer_visit_is_rejected(): void
    {
        $day = $this->nextMonday();
        $booker = app(AppointmentBooker::class);

        // Existing 30-min visit at 10:00 - 10:30.
        $booker->book($this->owner, $this->pet, $this->checkup, $day->setTime(10, 0));

        // A 60-min dental starting at 10:00 would overlap it.
        $dental = Service::create(['name' => 'Dental', 'duration_minutes' => 60, 'price' => 3500, 'is_active' => true]);

        $this->expectException(SlotUnavailableException::class);
        $booker->book($this->owner, $this->pet, $dental, $day->setTime(9, 30));
    }

    public function test_cancelled_appointments_free_their_slot(): void
    {
        $day = $this->nextMonday();
        $booker = app(AppointmentBooker::class);

        $appt = $booker->book($this->owner, $this->pet, $this->checkup, $day->setTime(12, 0));
        $appt->update(['status' => Appointment::CANCELLED]);

        $again = $booker->book($this->owner, $this->pet, $this->checkup, $day->setTime(12, 0));

        $this->assertSame(Appointment::PENDING, $again->status);
    }
}
