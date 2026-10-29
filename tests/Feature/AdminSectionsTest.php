<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserRole;
use Database\Seeders\BusinessHourSeeder;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSectionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_pages_have_separate_content_and_active_navigation(): void
    {
        $this->seed([BusinessHourSeeder::class, ServiceSeeder::class]);
        $admin = User::query()->create([
            'name' => 'Clinic Admin',
            'email' => 'admin@example.test',
            'password' => 'current-password',
            'email_verified_at' => now(),
        ]);
        $admin->grantRole(UserRole::ADMIN);
        $this->actingAs($admin, 'admin');

        $pages = [
            [route('admin.dashboard'), 'Appointments', 'Today at the clinic'],
            [route('admin.customers.index'), 'Customers', 'Pet parents'],
            [route('admin.services.index'), 'Services', 'Add a service'],
            [route('admin.hours.index'), 'Opening hours', 'Clinic schedule'],
            [route('admin.account.index'), 'Account', 'Personal information'],
            [route('admin.gallery.index'), 'Gallery', 'Published photos'],
        ];

        foreach ($pages as [$url, $section, $pageHeading]) {
            $this->get($url)
                ->assertOk()
                ->assertSee('href="'.$url.'" class="active"', false)
                ->assertSee('aria-current="page"', false)
                ->assertSee($pageHeading);
        }

        $this->get(route('admin.services.index'))
            ->assertDontSee('Personal information')
            ->assertDontSee('Clinic schedule')
            ->assertDontSee('Published photos');

        $this->get(route('admin.hours.index'))
            ->assertDontSee('General Checkup')
            ->assertDontSee('Personal information')
            ->assertDontSee('Published photos');

        $this->get(route('admin.account.index'))
            ->assertDontSee('General Checkup')
            ->assertDontSee('Clinic schedule')
            ->assertDontSee('Published photos');

        $this->get(route('admin.gallery.index'))
            ->assertDontSee('General Checkup')
            ->assertDontSee('Clinic schedule')
            ->assertDontSee('Personal information');

        $this->get(route('admin.customers.index'))
            ->assertDontSee('General Checkup')
            ->assertDontSee('Clinic schedule')
            ->assertDontSee('Personal information');

        $this->get(route('admin.dashboard'))
            ->assertDontSee('Weekly schedule')
            ->assertDontSee('Opening hours today');
    }

    public function test_services_page_contains_service_descriptions_and_booking_controls_only(): void
    {
        $this->seed(ServiceSeeder::class);
        $admin = User::query()->create([
            'name' => 'Clinic Admin',
            'email' => 'admin@example.test',
            'password' => 'current-password',
            'email_verified_at' => now(),
        ]);
        $admin->grantRole(UserRole::ADMIN);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.services.index'))
            ->assertOk()
            ->assertSee('A full health exam with weight check')
            ->assertSee('Bookable online')
            ->assertSee('Stop online bookings')
            ->assertSee('name="description"', false)
            ->assertDontSee('Save opening hours')
            ->assertDontSee('Change password');
    }

    public function test_opening_hours_page_provides_a_full_week_of_schedule_controls(): void
    {
        $this->seed(BusinessHourSeeder::class);
        $admin = User::query()->create([
            'name' => 'Clinic Admin',
            'email' => 'admin@example.test',
            'password' => 'current-password',
            'email_verified_at' => now(),
        ]);
        $admin->grantRole(UserRole::ADMIN);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.hours.index'))
            ->assertOk()
            ->assertSee('Sunday')
            ->assertSee('Saturday')
            ->assertSee('name="hours[0][is_closed]"', false)
            ->assertSee('name="hours[6][opens_at]"', false)
            ->assertSee('name="hours[6][closes_at]"', false)
            ->assertDontSee('Add a service')
            ->assertDontSee('Change password');

        $response->assertDontSee('Create weekly schedule');
        $this->assertMatchesRegularExpression(
            '/<input(?=[^>]*id="opens-0")(?=[^>]*disabled)[^>]*>/',
            $response->getContent(),
        );
        $this->assertMatchesRegularExpression(
            '/<input(?=[^>]*id="opens-1")(?=[^>]*required)(?![^>]*disabled)[^>]*>/',
            $response->getContent(),
        );
    }

    public function test_opening_hours_page_displays_controls_when_no_schedule_has_been_saved(): void
    {
        $admin = User::query()->create([
            'name' => 'Clinic Admin',
            'email' => 'admin@example.test',
            'password' => 'current-password',
            'email_verified_at' => now(),
        ]);
        $admin->grantRole(UserRole::ADMIN);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.hours.index'))
            ->assertOk()
            ->assertSee('Sunday')
            ->assertSee('Monday')
            ->assertSee('Saturday')
            ->assertSee('name="hours[0][is_closed]"', false)
            ->assertSee('name="hours[1][opens_at]"', false)
            ->assertSee('value="09:00"', false)
            ->assertSee('name="hours[6][closes_at]"', false)
            ->assertSee('Create weekly schedule')
            ->assertSee('action="'.route('admin.hours.store').'"', false);
    }
}
