# Happy Tails Dog Clinic: Booking System

A Laravel 13 booking website for a dog veterinary clinic, built with Blade.

- Customers must verify their email before signing in, then manage their dogs and book,
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

Customer registration never accepts a role and requires email verification.
Admin routes use a separate guard and check the admin role on the server.
Ordinary users cannot grant themselves admin access by editing a form or
request.

To securely create the first administrator without Render Shell access:

1. Generate a temporary, high-entropy setup key in a trusted local shell:
   `php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"`. Do not commit the key
   or place it in a URL.
2. Set `ADMIN_SETUP_KEY` to that value in the Render service environment, then
   deploy the application code.
3. In GitHub, run **Actions** → **Run Production Database Migrations**. Wait
   for it to succeed; it creates the durable one-time setup state. The workflow
   uses the configured Supabase secrets and does not require Render Shell.
4. Open `https://<your-render-service>/trentopetclinic/login`. When setup is enabled, this
   existing page displays the first-account form; enter the setup key and
   create the new administrator using a unique email address and a password of
   at least 12 characters. The form is unavailable unless the key is
   configured, no administrator exists, and setup has not already completed.
   It never promotes an existing customer. No separate registration page or
   route is created.
5. Remove `ADMIN_SETUP_KEY` from Render and allow the service to redeploy.
   Setup is also permanently closed by the database completion marker. Sign in
   at `/trentopetclinic/login`.

The setup key is never included in the URL, is compared securely, and its
submission is rate-limited. Requests use a PostgreSQL-backed lock to prevent
simultaneous initial-admin creation. The first admin is signed in immediately.

For a later administrator, trusted repository collaborators can use the
private **Grant Clinic Admin Access** GitHub Actions workflow to grant the role
to an existing verified account. That workflow requires the repository secrets
`APP_KEY`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and
`DB_PASSWORD`; restrict workflow access to trusted collaborators. It does not
change the account password. Do not put administrator credentials in source
control or seed production with credentials.

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
5. Deploy the application. Then run the GitHub Actions **Run Production
   Database Migrations** workflow and wait for success before using routes
   that depend on the new schema.
6. Follow the temporary-key first-admin procedure above. Configure clinic
   services and opening hours from the admin pages, or seed them through a
   separately reviewed, controlled database task.
7. Test the home page, registration, booking, password reset, and `/admin`.
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

Set `ADMIN_SETUP_KEY` only for first-admin setup. It must be a random value of
at least 32 characters and should be removed from Render after setup.

The HTTP middleware trusts only the immediate proxy (`REMOTE_ADDR`) for
`X-Forwarded-Proto`, which Render uses when terminating HTTPS. It does not trust
forwarded host or client-IP headers, so generated asset URLs use HTTPS without
accepting a client-supplied hostname.

Customer email verification and administrator email-change verification use
Laravel's signed verification links. Set `APP_URL` to the canonical HTTPS URL of
the Render service so generated links point to the deployed website. For the
current Render hostname, set `APP_URL=https://pet-clinic-f1v4.onrender.com`.

### Email delivery through Gmail SMTP

Configure Gmail SMTP in the ignored local `.env` file and as protected
environment variables in Render:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-gmail-address
MAIL_PASSWORD=your-google-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-gmail-address
MAIL_FROM_NAME="Wade-County Animal Clinic"
```

Enable 2-Step Verification on the Google account, then create an App Password
from the Google Account security settings. Use the App Password for
`MAIL_PASSWORD`, not the account password. Keep it only in local `.env` or
Render's protected environment settings; never commit it or expose it in
frontend variables. Use the same Gmail address for `MAIL_USERNAME` and
`MAIL_FROM_ADDRESS`.

Render Free web services block outbound SMTP ports 25, 465, and 587. Gmail SMTP
therefore cannot deliver from a Free service. Confirm the Render service plan
allows outbound SMTP before using these settings; otherwise choose an HTTPS
email provider or a hosting plan that permits SMTP. Gmail variables alone do
not select the transport, so `MAIL_MAILER=smtp` is required.

Set Render's `APP_URL` to the canonical HTTPS URL of the deployed app so signed
verification links use the production host. The application sends verification
notifications synchronously, so a queue worker is not needed. If delivery
fails, the account remains saved and unverified, the page allows a retry, and
the application logs the sanitized transport exception class/message and
non-secret SMTP connection settings. Check the Render runtime logs for that
diagnostic; credentials, recipient addresses, and verification URLs are not
logged.

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
- `app/Http/Middleware/`: admin access and security headers
- `app/Services/`: booking availability and overlap protection
- `database/migrations/` and `database/seeders/`: schema and initial clinic data
- `resources/views/`: customer, admin, and public Blade templates
- `resources/css/app.css`: Tailwind CSS v4 input and theme tokens
- `public/css/app.css` and `public/js/app.js`: application styles and behavior
- `tests/Feature/`: authentication, booking, admin access, and workflow tests
