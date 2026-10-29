<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserRole;
use Database\Seeders\BusinessHourSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    private function customer(): User
    {
        return User::create([
            'name' => 'Customer', 'email' => 'cust@example.test', 'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
    }

    private function admin(): User
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'admin@example.test', 'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
        $admin->grantRole(UserRole::ADMIN);

        return $admin;
    }

    public function test_guests_are_sent_to_the_separate_admin_login(): void
    {
        $this->assertSame('/trentopetclinic/login', parse_url(route('admin.login'), PHP_URL_PATH));
        $this->assertSame('/trentopetclinic/login', parse_url(route('admin.login.store'), PHP_URL_PATH));
        $this->assertSame('/auth', parse_url(route('login'), PHP_URL_PATH));

        $this->get(route('admin.login'))->assertOk();
        $this->get('/admin/login')->assertRedirect(route('admin.login'));
        $this->post('/admin/login')->assertRedirect(route('admin.login'));
        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->get('/admin/settings')->assertRedirect(route('admin.login'));
        $this->get(route('admin.customers.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.services.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.hours.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.account.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.gallery.index'))->assertRedirect(route('admin.login'));
    }

    public function test_public_customer_session_does_not_authorize_admin_pages(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer)->get('/admin')->assertRedirect(route('admin.login'));
        $this->actingAs($customer)->get('/admin/settings')->assertRedirect(route('admin.login'));
        $this->actingAs($customer)->get(route('admin.customers.index'))->assertRedirect(route('admin.login'));
    }

    public function test_public_admin_account_still_needs_the_separate_admin_session(): void
    {
        $this->actingAs($this->admin(), 'web')
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_customers_cannot_post_admin_actions(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer)
            ->put(route('admin.hours.update'), ['hours' => []])
            ->assertRedirect(route('admin.login'));

        $this->actingAs($customer)
            ->post(route('admin.hours.store'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admins_can_open_the_dashboard(): void
    {
        $this->travelTo('2026-10-26 12:00:00');
        $this->seed(BusinessHourSeeder::class);
        $admin = $this->admin();

        $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get('/admin')
            ->assertOk()
            ->assertSee(route('admin.gallery.index'), false)
            ->assertSee('id="dashboard-date"', false)
            ->assertSee('data-appointment-search', false)
            ->assertSee('Booking overview')
            ->assertSee('Scheduled')
            ->assertSee('Completed')
            ->assertSee('Confirmed today')
            ->assertSee('name="range" value="day"', false)
            ->assertSee('data-appointment-search-empty', false)
            ->assertHeader('Cache-Control', 'must-revalidate, no-cache, no-store, private')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertDontSee('Weekly schedule');
        $this->get(route('admin.gallery.index'))->assertOk();
        $this->get(route('admin.dashboard', ['range' => 'week', 'date' => '2026-10-29']))
            ->assertOk()
            ->assertSee('Appointments · Week of Oct 26');
        $this->get('/admin/settings')->assertRedirect(route('admin.services.index'));
        $this->get(route('admin.settings', ['section' => 'hours']))->assertRedirect(route('admin.hours.index'));
        $this->get(route('admin.settings', ['section' => 'account']))->assertRedirect(route('admin.account.index'));
    }

    public function test_admin_sign_in_uses_a_separate_guard_from_the_public_site(): void
    {
        $admin = $this->admin();

        $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertGuest('web');
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Sign in')
            ->assertDontSee('Sign out');
    }

    public function test_non_admin_cannot_sign_in_to_admin_area(): void
    {
        $customer = $this->customer();

        $this->from(route('admin.login'))
            ->post(route('admin.login.store'), [
                'email' => $customer->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest('admin');
        $this->assertGuest('web');
        $this->get('/admin')->assertRedirect(route('admin.login'));
    }

    public function test_unverified_admin_cannot_sign_in_when_verification_is_required(): void
    {
        $admin = User::create([
            'name' => 'Unverified Admin',
            'email' => 'unverified-admin@example.test',
            'password' => 'password',
        ]);
        $admin->grantRole(UserRole::ADMIN);

        $this->from(route('admin.login'))
            ->post(route('admin.login.store'), [
                'email' => $admin->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest('admin');
    }

    public function test_local_admin_can_sign_in_without_email_verification(): void
    {
        config(['app.require_admin_email_verification' => false]);
        $admin = User::create([
            'name' => 'Local Admin',
            'email' => 'local-admin@example.test',
            'password' => 'password',
        ]);
        $admin->grantRole(UserRole::ADMIN);

        $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'admin');
        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_admin_credentials_cannot_sign_in_through_the_public_form(): void
    {
        $admin = $this->admin();

        $this->from(route('login'))
            ->post(route('auth.login'), [
                'email' => $admin->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest('web');
        $this->assertGuest('admin');
    }

    public function test_admin_sign_out_preserves_any_separate_public_account_session(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();
        $this->actingAs($customer, 'web');

        $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($customer, 'web');
        $this->assertAuthenticatedAs($admin, 'admin');

        $this->post(route('admin.logout'))->assertRedirect(route('admin.login'));

        $this->assertAuthenticatedAs($customer, 'web');
        $this->assertGuest('admin');
    }

    public function test_customer_directory_is_private_to_admins_and_read_only(): void
    {
        $customer = $this->customer();
        $customer->profile()->create([
            'full_name' => 'Customer <script>alert(1)</script>',
            'phone' => '555-0100',
        ]);
        $admin = $this->admin();

        $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.customers.index'))
            ->assertOk()
            ->assertSee('Customer &lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee($customer->email)
            ->assertSee('555-0100')
            ->assertDontSee('Edit customer');
    }

    public function test_admin_dashboard_link_is_not_shown_on_the_public_homepage(): void
    {
        $this->actingAs($this->admin())
            ->get(route('home'))
            ->assertOk()
            ->assertDontSee('href="'.route('admin.dashboard').'"', false);
    }

    public function test_signup_cannot_grant_admin(): void
    {
        $this->post('/auth/register', [
            'name' => 'Sneaky',
            'email' => 'sneaky@example.test',
            'phone' => '555-0100',
            'password' => 'password-123',
            'password_confirmation' => 'password-123',
            'role' => 'admin',
            'is_admin' => 1,
        ]);

        $this->assertFalse(User::where('email', 'sneaky@example.test')->first()->isAdmin());
    }

    public function test_unverified_customers_are_redirected_to_email_verification(): void
    {
        $unverified = User::create([
            'name' => 'New', 'email' => 'new@example.test', 'password' => bcrypt('password'),
        ]);

        $this->actingAs($unverified)
            ->get('/book')
            ->assertRedirect(route('verification.notice'));

        $this->get(route('verification.notice'))->assertOk();
    }
}
