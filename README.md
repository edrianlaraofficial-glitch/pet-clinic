# Happy Tails Dog Clinic: Booking System

A booking website for a dog veterinary clinic, built with Laravel 13 and Blade.

- **Customers:** sign up with email confirmation, add their dogs, book a service
  at a free time slot, and view, cancel or reschedule their bookings.
- **Admin:** see the day or week's appointments, confirm, complete, mark no-show
  or cancel, and manage services and opening hours.
- **Clinic details are placeholders** ("Happy Tails Dog Clinic", the services,
  prices and hours). Edit them in the admin settings or in `database/seeders/`.

---

## 1. Requirements

- PHP 8.3+ with the usual extensions (`pdo_sqlite` or `pdo_mysql`, `mbstring`,
  `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`)
- Composer 2
- SQLite (default, zero setup) or MySQL 8+

## 2. Install (VS Code friendly)

```bash
cd happy-tails-clinic

composer install
cp .env.example .env
php artisan key:generate

# SQLite (default): create the database file
touch database/database.sqlite

php artisan migrate --seed
php artisan serve
```

Open <http://localhost:8000>.

### Using MySQL instead

In `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=happy_tails
DB_USERNAME=root
DB_PASSWORD=secret
```

Then create the empty `happy_tails` database and run `php artisan migrate --seed`.

## 3. Make yourself an admin

1. Sign up on the site with your real email at <http://localhost:8000/auth> and
   confirm it (check the log file `storage/logs/laravel.log` for the link when
   `MAIL_MAILER=log`).
2. Grant the admin role:

   ```bash
   php artisan clinic:make-admin you@example.com
   ```

3. Sign in. An **Admin** link appears in the header.

Non-admins get a plain 404 on every `/admin` URL. Roles live in the
`user_roles` table and are checked on the server, never trusted from the browser.
Signup can't grant a role.

Alternatively, set `ADMIN_EMAIL` and `ADMIN_PASSWORD` in `.env` before seeding
to have the seeder create the admin account.

## 4. Pages

| URL | Who | What |
|---|---|---|
| `/` | Everyone | Landing page: intro, services, hours, Book now |
| `/auth` | Guests | Sign in / create account |
| `/email/verify` | Signed in, unverified | Confirmation notice and resend |
| `/my-pets` | Confirmed customers | Add, edit and remove dogs |
| `/book` | Confirmed customers | Service → dog → date → time → confirm |
| `/my-bookings` | Confirmed customers | Upcoming and past visits; cancel, reschedule |
| `/admin` | Admins | Day/week appointments with confirm, complete, no-show, cancel |
| `/admin/settings` | Admins | Services (add, edit, deactivate) and opening hours |

## 5. How the booking rules work

- **Slots** are every 30 minutes inside the day's opening hours. A slot is
  offered only if the full visit fits before closing time.
- **Closed days** and **past dates** can't be booked.
- **Overlaps are blocked.** `AppointmentBooker` re-checks the slot inside a
  database transaction (with a row lock on MySQL) right before writing, so two
  customers can't take the same time. Pending and confirmed appointments hold
  their time. Cancelled and completed ones don't.
- **Rescheduling** sets the appointment back to *pending* until the clinic
  re-confirms.
- **Services are deactivated, never deleted,** so past bookings keep their history.
- Times are stored and shown in **Asia/Manila** (`APP_TIMEZONE`).

## 6. Where things are

```
app/
  Http/Controllers/        Public, auth, pets, booking, appointments
  Http/Controllers/Admin/  Dashboard and settings
  Http/Middleware/EnsureAdmin.php   Hides /admin from non-admins (404)
  Models/                  User, Profile, UserRole, Pet, Service, BusinessHour, Appointment
  Services/AvailabilityService.php  Computes free slots
  Services/AppointmentBooker.php    The only place appointments are written
  Console/Commands/MakeAdmin.php    php artisan clinic:make-admin
database/
  migrations/              One migration per table
  seeders/                 Placeholder services, Mon–Sat 9–6 hours, optional admin
public/css/app.css         Design tokens (colours, radius, fonts) in :root
resources/views/           Blade templates
routes/web.php             All routes, grouped by access level
tests/Feature/             Overlap prevention and admin-access tests
```

### Re-theming

Open `public/css/app.css`. The `:root` block has the brand tokens: `--teal`,
`--coral`, `--cream`, and so on. Dark mode is set in the same file (automatic
from the OS, or force it with `data-theme="dark"` on `<html>`).

## 7. Tests

```bash
php artisan test
```

The suite covers registration and email verification, customer pet ownership,
booking and rescheduling, slot generation and overlap prevention, admin access,
service management, and opening-hours updates.

## 8. Going live

- Set `APP_ENV=production`, `APP_DEBUG=false`, and a real `APP_URL`.
- Set `MAIL_MAILER` to a real provider (SMTP, Postmark, SES, and so on). Email
  confirmation depends on it.
- Use MySQL, and back up the database.
- Run `php artisan config:cache` and `php artisan route:cache`.
- Replace the placeholder services, prices, hours and clinic name.

## Notes on the original brief

- **Lovable Cloud / Row-level security:** this is a Laravel build, so the same
  rules are enforced in code instead: policies are checks in controllers and
  the `admin` middleware, and the `user_roles` table with `User::hasRole()` plays
  the role of `has_role()`. Customers can only reach their own pets and
  appointments, because every query is scoped to `$request->user()`.
- **Public services and hours:** exposed read-only through the home page and
  the booking page, and editable only from the admin area.
- **Email/password auth with confirmation:** built in with Laravel's
  `MustVerifyEmail`.
