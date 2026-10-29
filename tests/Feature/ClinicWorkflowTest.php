<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\BusinessHour;
use App\Models\Service;
use App\Models\User;
use App\Models\UserRole;
use App\Services\AppointmentBooker;
use Carbon\CarbonImmutable;
use Database\Seeders\BusinessHourSeeder;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class ClinicWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_sends_verification_email_without_authenticating_customer(): void
    {
        Notification::fake();

        $response = $this->post(route('auth.register'), [
            'name' => 'Sam Rivera',
            'email' => 'sam@example.test',
            'phone' => '555-0100',
            'password' => 'password-123',
            'password_confirmation' => 'password-123',
            'role' => UserRole::ADMIN,
        ]);

        $response->assertRedirect(route('verification.notice', ['email' => 'sam@example.test']))
            ->assertSessionHas('status', 'Your account was created. Check your email for a verification link before signing in.');

        $user = User::query()->where('email', 'sam@example.test')->firstOrFail();
        $this->assertSame('Sam Rivera', $user->profile->full_name);
        $this->assertSame('555-0100', $user->profile->phone);
        $this->assertFalse($user->isAdmin());
        $this->assertNull($user->email_verified_at);
        $this->assertGuest('web');
        $verificationUrl = null;
        Notification::assertSentTo($user, VerifyEmail::class, function (VerifyEmail $notification) use ($user, &$verificationUrl): bool {
            $verificationUrl = $notification->toMail($user)->actionUrl;

            return true;
        });
        $this->assertSame(
            parse_url(config('app.url'), PHP_URL_HOST),
            parse_url($verificationUrl, PHP_URL_HOST),
        );
        $this->assertTrue(URL::hasValidSignature(Request::create($verificationUrl)));

        $this->get(route('verification.notice', ['email' => $user->email]))
            ->assertOk()
            ->assertSee('Check your inbox')
            ->assertSee($user->email)
            ->assertSee(route('verification.send'), false);
    }

    public function test_registration_uses_a_generic_error_for_an_existing_email(): void
    {
        User::query()->create([
            'name' => 'Existing Customer',
            'email' => 'existing@example.test',
            'password' => 'password-123',
        ]);

        $this->post(route('auth.register'), [
            'name' => 'Another Customer',
            'email' => 'existing@example.test',
            'phone' => '555-0102',
            'password' => 'password-123',
            'password_confirmation' => 'password-123',
        ])
            ->assertRedirect()
            ->assertSessionHasErrors([
                'email' => 'We could not create an account with those details. Try signing in or resetting your password.',
            ]);

        $this->assertDatabaseCount('users', 1);
        $this->assertGuest();
    }

    public function test_valid_signed_link_verifies_a_customer_email(): void
    {
        $user = $this->customer('verify@example.test', false);
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ],
        );

        $this->get($url)
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Your email address has been verified. You can now sign in.');

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertGuest('web');
    }

    public function test_unverified_customer_cannot_sign_in_and_can_request_another_verification_email(): void
    {
        Notification::fake();
        $customer = $this->customer('unverified-login@example.test', false);

        $this->from(route('login'))
            ->post(route('auth.login'), [
                'email' => $customer->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('verification.notice', ['email' => $customer->email]))
            ->assertSessionHasErrors([
                'email' => 'Please verify your email address before signing in.',
            ]);

        $this->assertGuest('web');

        $this->post(route('verification.send'), ['email' => $customer->email])
            ->assertRedirect()
            ->assertSessionHas('status', 'If an unverified customer account exists for that address, a verification email has been sent.');

        Notification::assertSentTo($customer, VerifyEmail::class);
    }

    public function test_registration_keeps_unverified_account_and_reports_mail_delivery_failure(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp.gmail.com',
            'mail.mailers.smtp.port' => 587,
            'mail.mailers.smtp.encryption' => 'tls',
            'mail.mailers.smtp.username' => 'clinic@gmail.test',
            'mail.mailers.smtp.password' => 'test-app-password',
        ]);
        Log::shouldReceive('error')
            ->once()
            ->withArgs(static function (string $message, array $context): bool {
                $loggedContext = json_encode($context, JSON_THROW_ON_ERROR);

                return $message === 'Verification email delivery failed.'
                    && $context['exception_class'] === TransportException::class
                    && $context['mailer'] === 'smtp'
                    && $context['host'] === 'smtp.gmail.com'
                    && $context['port'] === 587
                    && $context['encryption'] === 'tls'
                    && substr_count($context['message'], '[REDACTED]') >= 2
                    && str_contains($context['message'], '[URL REDACTED]')
                    && ! str_contains($loggedContext, 'clinic@gmail.test')
                    && ! str_contains($loggedContext, 'test-app-password')
                    && ! str_contains($loggedContext, 'signed-token-secret');
            });
        $this->mock(Dispatcher::class)
            ->shouldReceive('send')
            ->once()
            ->andThrow(new TransportException(
                'SMTP authentication failed for clinic@gmail.test using test-app-password. https://example.test/email/verify/1/signed-token-secret',
            ));

        $this->post(route('auth.register'), [
            'name' => 'Sam Rivera',
            'email' => 'delivery-failure@example.test',
            'phone' => '555-0100',
            'password' => 'password-123',
            'password_confirmation' => 'password-123',
        ])
            ->assertRedirect(route('verification.notice', ['email' => 'delivery-failure@example.test']))
            ->assertSessionHasErrors([
                'email' => 'We could not send your verification email. Please try again later or contact the clinic.',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'delivery-failure@example.test',
            'email_verified_at' => null,
        ]);
        $this->assertGuest('web');
    }

    public function test_resend_reports_a_clear_error_when_mail_transport_fails(): void
    {
        $customer = $this->customer('resend-failure@example.test', false);
        $this->mock(Dispatcher::class)
            ->shouldReceive('send')
            ->once()
            ->andThrow(new TransportException('SMTP connection failed'));

        $this->from(route('verification.notice', ['email' => $customer->email]))
            ->post(route('verification.send'), ['email' => $customer->email])
            ->assertRedirect(route('verification.notice', ['email' => $customer->email]))
            ->assertSessionHasErrors([
                'email' => 'We could not send your verification email. Please try again later or contact the clinic.',
            ]);

        $this->assertNull($customer->fresh()->email_verified_at);
    }

    public function test_invalid_signed_customer_verification_link_does_not_verify_email(): void
    {
        $customer = $this->customer('invalid-verify@example.test', false);
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $customer->getKey(),
                'hash' => sha1('different@example.test'),
            ],
        );

        $this->get($url)->assertForbidden();

        $this->assertNull($customer->fresh()->email_verified_at);
    }

    public function test_verification_resend_does_not_disclose_unknown_or_admin_accounts(): void
    {
        Notification::fake();
        $admin = $this->customer('verification-admin@example.test', false);
        $admin->grantRole(UserRole::ADMIN);
        $verifiedCustomer = $this->customer('verification-already-done@example.test');

        $this->post(route('verification.send'), ['email' => 'unknown-verification@example.test'])
            ->assertRedirect()
            ->assertSessionHas('status', 'If an unverified customer account exists for that address, a verification email has been sent.');

        $this->post(route('verification.send'), ['email' => $admin->email])
            ->assertRedirect()
            ->assertSessionHas('status', 'If an unverified customer account exists for that address, a verification email has been sent.');

        $this->post(route('verification.send'), ['email' => $verifiedCustomer->email])
            ->assertRedirect()
            ->assertSessionHas('status', 'If an unverified customer account exists for that address, a verification email has been sent.');

        Notification::assertNothingSent();
    }

    public function test_customer_can_sign_in_and_sign_out(): void
    {
        $customer = $this->customer('login@example.test');

        $this->post(route('auth.login'), [
            'email' => $customer->email,
            'password' => 'password',
        ])->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($customer);

        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_customer_cannot_view_another_customers_dog(): void
    {
        $owner = $this->customer('owner@example.test');
        $otherCustomer = $this->customer('other@example.test');
        $pet = $owner->pets()->create(['name' => 'Biscuit']);

        $this->actingAs($otherCustomer)
            ->get(route('pets.edit', $pet))
            ->assertNotFound();
    }

    public function test_customer_cannot_access_another_customers_appointment(): void
    {
        $this->seed(BusinessHourSeeder::class);
        $owner = $this->customer('appointment-owner@example.test');
        $otherCustomer = $this->customer('appointment-other@example.test');
        $pet = $owner->pets()->create(['name' => 'Biscuit']);
        $appointment = app(AppointmentBooker::class)->book(
            $owner,
            $pet,
            $this->service(),
            $this->nextMonday()->setTime(10, 0),
        );

        $this->actingAs($otherCustomer)
            ->get(route('appointments.reschedule', $appointment))
            ->assertNotFound();
        $this->actingAs($otherCustomer)
            ->post(route('appointments.cancel', $appointment))
            ->assertNotFound();
    }

    public function test_pet_names_are_escaped_in_the_customer_dashboard(): void
    {
        $customer = $this->customer('html-name@example.test');
        $customer->pets()->create(['name' => 'Rex<script>alert(1)</script>']);

        $this->actingAs($customer)
            ->get(route('pets.index'))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_customer_booking_persists_a_pending_appointment(): void
    {
        $this->seed(BusinessHourSeeder::class);
        $customer = $this->customer('booker@example.test');
        $pet = $customer->pets()->create(['name' => 'Luna']);
        $service = $this->service();
        $startsAt = $this->nextMonday()->setTime(10, 0);

        $response = $this->actingAs($customer)->post(route('booking.store'), [
            'service_id' => $service->getKey(),
            'pet_id' => $pet->getKey(),
            'starts_at' => $startsAt->format('Y-m-d H:i'),
            'customer_notes' => 'Please check her left paw.',
        ]);

        $response->assertRedirect(route('my-bookings'));
        $this->assertDatabaseHas('appointments', [
            'user_id' => $customer->getKey(),
            'pet_id' => $pet->getKey(),
            'service_id' => $service->getKey(),
            'starts_at' => $startsAt->format('Y-m-d H:i:s'),
            'ends_at' => $startsAt->addMinutes(30)->format('Y-m-d H:i:s'),
            'status' => Appointment::PENDING,
            'customer_notes' => 'Please check her left paw.',
        ]);
    }

    public function test_booking_page_shows_slots_for_a_customer_selected_service_dog_and_date(): void
    {
        $this->seed(BusinessHourSeeder::class);
        $customer = $this->customer('slots@example.test');
        $pet = $customer->pets()->create(['name' => 'Poppy']);
        $service = $this->service();
        $date = $this->nextMonday();

        $this->actingAs($customer)
            ->get(route('booking.create', [
                'service_id' => $service->getKey(),
                'pet_id' => $pet->getKey(),
                'date' => $date->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Pick a service, your dog, and a time')
            ->assertSee('Your visit')
            ->assertSee('Checkup')
            ->assertSee('Poppy')
            ->assertSee('Confirm booking')
            ->assertSee('10:00 AM');
    }

    public function test_rescheduling_moves_a_confirmed_appointment_back_to_pending(): void
    {
        $this->seed(BusinessHourSeeder::class);
        $customer = $this->customer('reschedule@example.test');
        $pet = $customer->pets()->create(['name' => 'Poppy']);
        $service = $this->service();
        $appointment = app(AppointmentBooker::class)->book(
            $customer,
            $pet,
            $service,
            $this->nextMonday()->setTime(10, 0),
        );
        $appointment->update(['status' => Appointment::CONFIRMED]);
        $newStart = $this->nextMonday()->setTime(11, 30);

        $this->actingAs($customer)
            ->put(route('appointments.update-schedule', $appointment), [
                'starts_at' => $newStart->format('Y-m-d H:i'),
            ])
            ->assertRedirect(route('my-bookings'));

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->getKey(),
            'starts_at' => $newStart->format('Y-m-d H:i:s'),
            'ends_at' => $newStart->addMinutes(30)->format('Y-m-d H:i:s'),
            'status' => Appointment::PENDING,
        ]);
    }

    public function test_customer_can_cancel_a_future_appointment(): void
    {
        $this->seed(BusinessHourSeeder::class);
        $customer = $this->customer('cancel@example.test');
        $pet = $customer->pets()->create(['name' => 'Poppy']);
        $appointment = app(AppointmentBooker::class)->book(
            $customer,
            $pet,
            $this->service(),
            $this->nextMonday()->setTime(12, 0),
        );

        $this->actingAs($customer)
            ->post(route('appointments.cancel', $appointment))
            ->assertRedirect(route('my-bookings'));

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->getKey(),
            'status' => Appointment::CANCELLED,
        ]);
    }

    public function test_admin_can_update_opening_hours_for_the_week(): void
    {
        $this->seed(BusinessHourSeeder::class);
        $admin = $this->customer('hours-admin@example.test');
        $admin->grantRole(UserRole::ADMIN);
        $hours = [
            0 => ['is_closed' => '1', 'opens_at' => '', 'closes_at' => ''],
            1 => ['opens_at' => '08:30', 'closes_at' => '17:00'],
            2 => ['opens_at' => '09:00', 'closes_at' => '18:00'],
            3 => ['opens_at' => '09:00', 'closes_at' => '18:00'],
            4 => ['opens_at' => '09:00', 'closes_at' => '18:00'],
            5 => ['opens_at' => '09:00', 'closes_at' => '18:00'],
            6 => ['opens_at' => '09:00', 'closes_at' => '18:00'],
        ];

        $this->actingAs($admin, 'admin')
            ->put(route('admin.hours.update'), ['hours' => $hours])
            ->assertRedirect();

        $this->assertDatabaseHas('business_hours', [
            'weekday' => 0,
            'is_closed' => 1,
            'opens_at' => null,
            'closes_at' => null,
        ]);
        $this->assertDatabaseHas('business_hours', [
            'weekday' => 1,
            'is_closed' => 0,
            'opens_at' => '08:30:00',
            'closes_at' => '17:00:00',
        ]);
    }

    public function test_admin_can_create_a_weekly_schedule_when_no_hours_exist_yet(): void
    {
        $admin = $this->customer('empty-hours-admin@example.test');
        $admin->grantRole(UserRole::ADMIN);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.hours.update'), [
                'hours' => [
                    0 => ['is_closed' => '1'],
                    1 => ['opens_at' => '08:30', 'closes_at' => '17:00'],
                    2 => ['opens_at' => '09:00', 'closes_at' => '18:00'],
                    3 => ['opens_at' => '09:00', 'closes_at' => '18:00'],
                    4 => ['opens_at' => '09:00', 'closes_at' => '18:00'],
                    5 => ['opens_at' => '09:00', 'closes_at' => '18:00'],
                    6 => ['opens_at' => '09:00', 'closes_at' => '18:00'],
                ],
            ])
            ->assertRedirect(route('admin.hours.index'))
            ->assertSessionHas('status', 'Opening hours were updated.');

        $this->assertDatabaseCount('business_hours', 7);
        $this->assertDatabaseHas('business_hours', [
            'weekday' => 0,
            'is_closed' => 1,
            'opens_at' => null,
            'closes_at' => null,
        ]);
        $this->assertDatabaseHas('business_hours', [
            'weekday' => 1,
            'is_closed' => 0,
            'opens_at' => '08:30:00',
            'closes_at' => '17:00:00',
        ]);
    }

    public function test_admin_can_create_the_default_weekly_schedule_from_an_empty_state(): void
    {
        $admin = $this->customer('default-hours-admin@example.test');
        $admin->grantRole(UserRole::ADMIN);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.hours.store'))
            ->assertRedirect(route('admin.hours.index'))
            ->assertSessionHas('status', 'The weekly schedule was created. Existing days were kept unchanged.');

        $this->assertDatabaseCount('business_hours', 7);
        $this->assertDatabaseHas('business_hours', [
            'weekday' => 0,
            'is_closed' => 1,
            'opens_at' => null,
            'closes_at' => null,
        ]);
        $this->assertDatabaseHas('business_hours', [
            'weekday' => 1,
            'is_closed' => 0,
            'opens_at' => '09:00:00',
            'closes_at' => '18:00:00',
        ]);

        $this->post(route('admin.hours.store'))
            ->assertRedirect(route('admin.hours.index'))
            ->assertSessionHas('status', 'The weekly schedule is already complete.');

        $this->assertDatabaseCount('business_hours', 7);
    }

    public function test_admin_can_create_missing_weekly_hours_without_overwriting_saved_days(): void
    {
        $admin = $this->customer('create-hours-admin@example.test');
        $admin->grantRole(UserRole::ADMIN);
        $savedDay = BusinessHour::query()->create([
            'weekday' => 1,
            'is_closed' => false,
            'opens_at' => '08:00:00',
            'closes_at' => '16:00:00',
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.hours.store'))
            ->assertRedirect(route('admin.hours.index'))
            ->assertSessionHas('status', 'The weekly schedule was created. Existing days were kept unchanged.');

        $this->assertDatabaseCount('business_hours', 7);
        $this->assertDatabaseHas('business_hours', [
            'id' => $savedDay->getKey(),
            'weekday' => 1,
            'opens_at' => '08:00:00',
            'closes_at' => '16:00:00',
        ]);
        $this->assertDatabaseHas('business_hours', [
            'weekday' => 0,
            'is_closed' => 1,
            'opens_at' => null,
            'closes_at' => null,
        ]);
        $this->assertDatabaseHas('business_hours', [
            'weekday' => 2,
            'is_closed' => 0,
            'opens_at' => '09:00:00',
            'closes_at' => '18:00:00',
        ]);
    }

    public function test_admin_can_confirm_a_pending_appointment(): void
    {
        $this->seed(BusinessHourSeeder::class);
        $admin = $this->customer('admin-workflow@example.test');
        $admin->grantRole(UserRole::ADMIN);
        $customer = $this->customer('customer-workflow@example.test');
        $pet = $customer->pets()->create(['name' => 'Mochi']);
        $service = $this->service();
        $appointment = app(AppointmentBooker::class)->book(
            $customer,
            $pet,
            $service,
            $this->nextMonday()->setTime(11, 0),
        );

        $this->actingAs($admin, 'admin')
            ->post(route('admin.appointments.transition', [$appointment, 'confirm']))
            ->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->getKey(),
            'status' => Appointment::CONFIRMED,
        ]);
    }

    public function test_admin_dashboard_shows_live_appointment_search_and_weekly_counts(): void
    {
        $this->travelTo('2026-10-26 10:00:00');
        $this->seed(BusinessHourSeeder::class);
        $admin = $this->customer('dashboard-admin@example.test');
        $admin->grantRole(UserRole::ADMIN);
        $customer = $this->customer('dashboard-customer@example.test');
        $pet = $customer->pets()->create(['name' => 'Mochi']);
        $appointment = app(AppointmentBooker::class)->book(
            $customer,
            $pet,
            $this->service(),
            CarbonImmutable::parse('2026-10-26 11:00', config('app.timezone')),
        );

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('data-search="clinic customer mochi checkup"', false)
            ->assertSee('Monday Oct 26: 1 scheduled, 0 completed');

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->getKey(),
            'status' => Appointment::PENDING,
        ]);
    }

    public function test_admin_dashboard_cards_count_active_statuses_within_their_time_periods(): void
    {
        $this->travelTo('2026-10-29 10:00:00');
        $admin = $this->customer('dashboard-stats-admin@example.test');
        $admin->grantRole(UserRole::ADMIN);
        $customer = $this->customer('dashboard-stats-customer@example.test');
        $pet = $customer->pets()->create(['name' => 'Mochi']);
        $service = $this->service();
        $createAppointment = function (string $startsAt, string $status) use ($customer, $pet, $service): Appointment {
            $start = CarbonImmutable::parse($startsAt, config('app.timezone'));

            return Appointment::query()->create([
                'user_id' => $customer->getKey(),
                'pet_id' => $pet->getKey(),
                'service_id' => $service->getKey(),
                'starts_at' => $start,
                'ends_at' => $start->addMinutes(30),
                'status' => $status,
            ]);
        };

        $createAppointment('2026-10-29 09:00', Appointment::PENDING);
        $createAppointment('2026-10-29 10:00', Appointment::CONFIRMED);
        $createAppointment('2026-10-29 11:00', Appointment::COMPLETED);
        $createAppointment('2026-10-29 12:00', Appointment::NO_SHOW);
        $createAppointment('2026-10-29 13:00', Appointment::CANCELLED);
        $createAppointment('2026-10-26 09:00', Appointment::COMPLETED);
        $createAppointment('2026-10-25 09:00', Appointment::NO_SHOW);
        $createAppointment('2026-11-02 09:00', Appointment::COMPLETED);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('<span class="admin-stat-label">Today’s bookings</span><strong>2</strong><small>Active appointments</small>', false)
            ->assertSee('<span class="admin-stat-label">Pending confirmation</span><strong>1</strong><small>Awaiting your review</small>', false)
            ->assertSee('<span class="admin-stat-label">Confirmed today</span><strong>1</strong><small>Ready for their visit</small>', false)
            ->assertSee('<span class="admin-stat-label">Completed this week</span><strong>2</strong><small>Appointments completed</small>', false)
            ->assertSee('<span class="admin-stat-label">No-shows this week</span><strong>1</strong><small>Track follow-up if needed</small>', false);
    }

    public function test_admin_can_add_and_deactivate_a_service(): void
    {
        $admin = $this->customer('service-admin@example.test');
        $admin->grantRole(UserRole::ADMIN);

        $this->actingAs($admin, 'admin')->post(route('admin.services.store'), [
            'name' => 'Wellness exam',
            'description' => 'Annual health check.',
            'duration_minutes' => 45,
            'price' => 950,
            'is_active' => '1',
        ])->assertRedirect();

        $service = Service::query()->where('name', 'Wellness exam')->firstOrFail();
        $this->assertTrue($service->is_active);

        $this->actingAs($admin, 'admin')->put(route('admin.services.update', $service), [
            'name' => 'Wellness exam plus',
            'description' => 'Annual health check with follow-up.',
            'duration_minutes' => 50,
            'price' => 1100,
        ])->assertRedirect();

        $service->refresh();
        $this->assertSame('Wellness exam plus', $service->name);
        $this->assertSame(50, $service->duration_minutes);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.services.toggle', $service))
            ->assertRedirect();

        $this->assertFalse($service->fresh()->is_active);
    }

    private function customer(string $email, bool $verified = true): User
    {
        return User::create([
            'name' => 'Clinic Customer',
            'email' => $email,
            'password' => 'password',
            'email_verified_at' => $verified ? now() : null,
        ]);
    }

    private function service(): Service
    {
        return Service::create([
            'name' => 'Checkup',
            'duration_minutes' => 30,
            'price' => 800,
            'is_active' => true,
        ]);
    }

    private function nextMonday(): CarbonImmutable
    {
        return CarbonImmutable::now(config('app.timezone'))
            ->addWeek()
            ->startOfWeek(CarbonImmutable::MONDAY);
    }
}
