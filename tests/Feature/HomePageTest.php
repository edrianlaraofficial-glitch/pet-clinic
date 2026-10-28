<?php

namespace Tests\Feature;

use App\Models\BusinessHour;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\BusinessHourSeeder;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shows_that_the_clinic_is_open_during_today_hours(): void
    {
        $this->seed(BusinessHourSeeder::class);
        $this->travelTo(CarbonImmutable::parse('2026-10-26 10:00:00', 'Asia/Manila'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Open now')
            ->assertSee('Today until 6:00 PM');
    }

    public function test_header_promotes_booking_and_keeps_sign_in_secondary_for_guests(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('href="'.route('login').'" class="btn btn-primary nav-book-visit"', false)
            ->assertSee('href="'.route('login').'" class="nav-sign-in"', false);
    }

    public function test_authenticated_header_links_directly_to_booking(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('booking.create').'" class="btn btn-primary nav-book-visit"', false)
            ->assertDontSee('class="nav-sign-in"', false);
    }

    public function test_footer_hours_follow_the_schedule_on_other_public_pages(): void
    {
        $this->seed(BusinessHourSeeder::class);
        BusinessHour::query()
            ->whereIn('weekday', [3, 5])
            ->update([
                'is_closed' => true,
                'opens_at' => null,
                'closes_at' => null,
            ]);

        $response = $this->get(route('policies.privacy'));

        $response->assertOk()
            ->assertSeeInOrder([
                'Wednesday',
                'Closed',
                'Friday',
                'Closed',
            ])
            ->assertSee('9:00 AM – 6:00 PM');
    }

    public function test_homepage_shows_when_the_clinic_opens_later_today(): void
    {
        $this->seed(BusinessHourSeeder::class);
        $this->travelTo(CarbonImmutable::parse('2026-10-26 08:00:00', 'Asia/Manila'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Opens today')
            ->assertSee('Today at 9:00 AM');
    }

    public function test_homepage_shows_the_next_opening_when_the_clinic_is_closed_today(): void
    {
        $this->seed(BusinessHourSeeder::class);
        $this->travelTo(CarbonImmutable::parse('2026-10-25 10:00:00', 'Asia/Manila'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Closed today')
            ->assertSee('Opens Monday at 9:00 AM');
    }

    public function test_homepage_renders_service_cards_in_reference_order_with_booking_links(): void
    {
        $this->seed(ServiceSeeder::class);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSeeInOrder([
                'General Checkup',
                'Vaccination',
                'Grooming',
                'Dental Cleaning',
                'Spay / Neuter Consultation',
            ])
            ->assertSee('Book this visit')
            ->assertSee('href="'.route('login').'"', false)
            ->assertSee('Prices are estimates. Final cost depends on your dog’s needs.');
    }
}
