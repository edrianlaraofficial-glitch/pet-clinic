# Happy Tails Dog Clinic: Booking System

A Laravel 13 booking website for a dog veterinary clinic, built with Blade.

- Customers can register, verify their email, manage their dogs, and book,
  cancel, or reschedule appointments.
- Administrators can manage appointments, services, opening hours, customers,
  clinic details, and gallery images.
- Clinic name, services, prices, and hours are sample content. Update them
  through the admin pages or the seeders.

## Requirements

- PHP 8.3 or newer with `pdo_sqlite`, `fileinfo`, `mbstring`, `openssl`,
  `tokenizer`, `xml`, `ctype`, `json`, and `bcmath`; add `pdo_pgsql` to use
  PostgreSQL locally
- Composer 2
- Node.js 22 and npm (Vite 8)
- SQLite for local development, or PostgreSQL for production
- Docker for container builds

The Docker image uses PHP 8.4 with the PostgreSQL, SQLite, `mbstring`, `bcmath`,
`intl`, and `zip` extensions.

## Local development

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
if (-not (Test-Path database/database.sqlite)) {
    New-Item -ItemType File database/database.sqlite
}
php artisan migrate --seed
npm ci
npm run build
php artisan serve
```

Open <http://localhost:8000>. The application layouts load the Tailwind CSS v4
production bundle from Vite alongside the existing `public/css/app.css` design
stylesheet and `public/js/app.js` interactions. This keeps the current customer
and admin experience intact while making Tailwind available throughout the
application. The welcome view also uses Vite.

## Accounts and authorization

Customer registration never accepts a role. Admin routes use a separate guard
and check the admin role on the server. Ordinary users cannot grant themselves
admin access by editing a form or request.

To create the first administrator safely:

1. Register a customer account through the site and verify its email.
2. In the Render service Shell, run
   `php artisan clinic:make-admin admin@example.com`, replacing the email with
   the account you verified.
3. Sign in at `/admin/login`.

The command only grants the role to an existing account and does not change its
password. The optional `AdminSeeder` can create the initial admin only while no
admin role exists; later runs skip setup. Do not put an administrator password
in source control or seed production with credentials.

## Database and Supabase PostgreSQL

Local development defaults to SQLite. Production defaults to PostgreSQL even
if `DB_CONNECTION` is omitted; set Supabase connection values through Render
environment variables and never copy a developer's SQLite database into the
deployment.

In the Supabase Dashboard, open **Connect** and copy the connection details for
the selected endpoint. Use those exact host, port, database, and username
values:

- Use the **direct** endpoint for a persistent Render container when its network
  supports the endpoint's IP version. Supabase direct connections are IPv6 by
  default unless the project has IPv4 support enabled.
- If Render cannot reach the direct endpoint over IPv6, use Supabase's **shared
  session pooler** (IPv4, usually port `5432`). Copy its host and pooler username
  exactly; do not construct them yourself.
- Use **SSL mode `require`**. Do not use the transaction pooler for migrations;
  prefer the direct endpoint or session pooler.

Set `DB_CONNECTION=pgsql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`,
`DB_PASSWORD`, `DB_SCHEMA=public`, and `DB_SSLMODE=require` in Render. The
password belongs only in Render's environment settings or an approved secret
store, never in GitHub files or frontend variables.

Migrations create application tables; they do not erase existing data. For an
existing database, take a backup and inspect pending migrations before running
them. Do not use `migrate:fresh` or reset commands against production.

## Build and run the Docker image

The multi-stage Docker build creates the Vite production assets and installs
production Composer dependencies. Apache serves only Laravel's `public`
directory and listens on Render's default port `10000`.

```powershell
docker build -t happy-tails-clinic .
docker run --rm -p 10000:10000 `
  -e APP_KEY `
  -e APP_URL `
  -e DB_CONNECTION=pgsql `
  -e DB_HOST `
  -e DB_PORT `
  -e DB_DATABASE `
  -e DB_USERNAME `
  -e DB_PASSWORD `
  happy-tails-clinic
```

The container intentionally does not run migrations at startup. Run migrations
as a separate, deliberate deployment step after confirming the database
settings.

## Deploy with GitHub, Render, and Supabase

1. Push the reviewed source to a GitHub repository. `.env`, local databases,
   logs, generated caches, uploads, and build outputs are excluded from Git.
2. Create or select a Supabase PostgreSQL project. In its **Connect** dialog,
   choose the direct connection or session pooler appropriate for Render's
   network, and keep the database password private.
3. In Render, create a **Web Service** from the GitHub repository and select
   **Docker**. Use the repository root as the Docker build context and set the
   health-check path to `/up`.
4. Configure the environment variables listed below in Render. Keep `APP_DEBUG`
   false, use a real generated `APP_KEY`, and set the exact public service URL
   as `APP_URL`.
5. Deploy. In the Render Shell, run `php artisan migrate --force`, then run
   `php artisan db:seed --class=ServiceSeeder --force` and
   `php artisan db:seed --class=BusinessHourSeeder --force` once to add the
   sample services and hours. Do not run these seeders again after editing
   clinic data; they update the seeded records.
6. Register and verify the intended admin account, then grant it the admin
   role with `php artisan clinic:make-admin admin@example.com` in the Render
   Shell.
7. Test the home page, registration, email verification, booking, and `/admin`.
   Check Render runtime logs if a request fails; do not enable public debug
   output.

### Render environment variables

Required:

| Variable | Purpose |
|---|---|
| `APP_ENV=production` | Enables production behavior |
| `APP_DEBUG=false` | Prevents public exception details |
| `APP_KEY` | Laravel encryption and signed data; generate a fresh value with `php artisan key:generate --show` in a trusted local shell and add it only in Render |
| `APP_URL` | Public HTTPS URL of the Render service |
| `APP_TIMEZONE=Asia/Manila` | Clinic time zone |
| `LOG_CHANNEL=stderr` | Sends Laravel logs and exceptions to Render logs |
| `LOG_LEVEL=error` | Limits production log verbosity |
| `DB_CONNECTION=pgsql` | Selects PostgreSQL |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Values copied from the Supabase Connect dialog |
| `DB_SCHEMA=public` | PostgreSQL schema |
| `DB_SSLMODE=require` | Requires TLS for the database connection |
| `SESSION_DRIVER=database` | Persists sessions in PostgreSQL across restarts |
| `SESSION_SECURE_COOKIE=true` | Sends session cookies only over HTTPS |
| `CACHE_STORE=database` | Uses the migrated cache table |
| `QUEUE_CONNECTION=sync` | Avoids queuing work without a running worker |
| `FILESYSTEM_DISK=public` | Stores gallery images on the public disk |

The HTTP middleware trusts only the immediate proxy (`REMOTE_ADDR`) for
`X-Forwarded-Proto`, which Render uses when terminating HTTPS. It does not trust
forwarded host or client-IP headers, so generated asset URLs use HTTPS without
accepting a client-supplied hostname.

For email verification and password resets, configure a real mail provider in
Render (`MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`,
`MAIL_PASSWORD`, `MAIL_ENCRYPTION`, `MAIL_FROM_ADDRESS`, and `MAIL_FROM_NAME`).
The local `log` mailer does not deliver email.

### Uploaded gallery images

Render's container filesystem is ephemeral. This application saves gallery
images to `storage/app/public`; attach a Render persistent disk at exactly
`/var/www/html/storage/app/public` before relying on uploaded images. The
Docker image creates Laravel's `public/storage` link for that mount. Without a
persistent disk, uploads can disappear on a redeploy or instance replacement.
Back up existing image files separately before moving a live deployment.

## GitHub Actions migrations

The manual workflow at `.github/workflows/migrate.yml` runs only when triggered
from GitHub Actions. Configure `APP_KEY`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`,
`DB_USERNAME`, and `DB_PASSWORD` as repository or environment secrets before
using it. It runs `php artisan migrate --force`; it does not seed or reset the
database.

## Tests and checks

```powershell
php artisan test --compact
composer check-platform-reqs
composer audit
npm audit --omit=optional
npm run build
```

Tests use an in-memory SQLite database. PostgreSQL-specific behavior and
migrations should also be tested against a disposable PostgreSQL database before
production rollout.

## Main project areas

- `app/Http/Controllers/` and `routes/web.php`: customer, admin, and public
  requests
- `app/Http/Middleware/`: admin, email-verification, and security headers
- `app/Services/`: booking availability and overlap protection
- `database/migrations/` and `database/seeders/`: schema and initial clinic data
- `resources/views/`: customer, admin, and public Blade templates
- `resources/css/app.css`: Tailwind CSS v4 input and theme tokens
- `public/css/app.css` and `public/js/app.js`: application styles and behavior
- `tests/Feature/`: authentication, booking, admin access, and workflow tests
