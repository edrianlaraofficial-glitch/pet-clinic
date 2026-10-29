<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }

    public function test_landing_page_lists_only_active_services(): void
    {
        Service::create([
            'name' => 'Available checkup',
            'duration_minutes' => 30,
            'price' => 800,
            'is_active' => true,
        ]);
        Service::create([
            'name' => 'Retired service',
            'duration_minutes' => 30,
            'price' => 800,
            'is_active' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Available checkup')
            ->assertDontSee('Retired service');
    }

    public function test_landing_page_shows_the_clinic_details_and_google_rating(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Wade-County Animal Clinic')
            ->assertSee('Garcia St., P-6, Poblacion')
            ->assertSee('Kind care means the world.')
            ->assertSee('5.0')
            ->assertSee('Based on 2 reviews')
            ->assertSee('id="ratings"', false);
    }

    public function test_landing_page_navigation_links_to_homepage_sections(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('home').'#services"', false)
            ->assertSee('href="'.route('home').'#about"', false)
            ->assertSee('href="'.route('home').'#gallery"', false)
            ->assertSee('href="'.route('home').'#ratings"', false)
            ->assertSee('aria-controls="public-navigation"', false)
            ->assertDontSee('href="'.route('home').'#contact"', false)
            ->assertSee('id="about"', false)
            ->assertSee('id="gallery"', false)
            ->assertSee('data-public-nav="ratings"', false)
            ->assertDontSee('href="/ratings"', false)
            ->assertSee('id="contact"', false)
            ->assertSee('Photos of our clinic and its lovely visitors will be here soon.');
    }

    public function test_signed_in_customer_navigation_shows_account_menu_and_booking_link(): void
    {
        $customer = User::query()->create([
            'name' => 'Taylor Pet Parent',
            'email' => 'taylor-navigation@example.test',
            'password' => 'correct-horse-battery',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($customer)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Book a visit')
            ->assertSee('Open account menu for Taylor Pet Parent')
            ->assertSee('My dogs')
            ->assertSee('My bookings')
            ->assertSee('Sign out')
            ->assertSee('action="'.route('logout').'"', false)
            ->assertDontSee('Sign in');
    }

    public function test_ratings_are_not_a_separate_page(): void
    {
        $this->get('/ratings')->assertNotFound();
    }

    public function test_login_page_shows_sign_in_and_registration_forms(): void
    {
        $response = $this->get(route('login'));

        $response
            ->assertOk()
            ->assertSee('More tail wags start here.')
            ->assertSee('Sign in')
            ->assertSee('Create an account')
            ->assertSee('auth-tab-login', false)
            ->assertSee('auth-tab-register', false)
            ->assertSee('auth/login', false)
            ->assertSee('auth/register', false)
            ->assertSee('name="_form" value="login"', false)
            ->assertSee('name="_form" value="register"', false)
            ->assertSee('id="reg-phone" type="tel" name="phone" value="" placeholder="Your phone number" required', false)
            ->assertSee('Google', false)
            ->assertSee('Apple', false)
            ->assertSee('disabled', false)
            ->assertSee('Coming soon');

        $this->assertMatchesRegularExpression(
            '/<section class="auth-form-card auth-register-card" id="create-account".*?Google.*?Apple ID.*?or create account with email/s',
            $response->getContent(),
        );
    }

    public function test_invalid_registration_returns_with_the_register_tab_selected(): void
    {
        $response = $this->from(route('login'))
            ->followingRedirects()
            ->post(route('auth.register'), [
                '_form' => 'register',
                'name' => '',
                'email' => 'not-an-email',
                'password' => 'short',
                'password_confirmation' => 'different',
            ])
            ->assertOk();

        $this->assertMatchesRegularExpression(
            '/id="auth-tab-register".*?value="register"\s+checked/s',
            $response->getContent(),
        );
    }

    public function test_registration_requires_a_phone_number(): void
    {
        $response = $this->from(route('login'))
            ->post(route('auth.register'), [
                '_form' => 'register',
                'name' => 'Taylor Pet Parent',
                'email' => 'taylor@example.com',
                'password' => 'correct-horse-battery',
                'password_confirmation' => 'correct-horse-battery',
            ]);

        $response->assertRedirect(route('login'))
            ->assertSessionHasErrors(['phone']);

        $this->assertDatabaseMissing('users', ['email' => 'taylor@example.com']);
    }
}
