# Security operations: local, production, and recovery

This guide reflects the current Wade-County Animal Clinic Laravel application. It is operational guidance, not a security certification or legal opinion. Deployment and clinic-specific choices still require confirmation.

## Local development on Windows

1. Install a supported PHP runtime, Composer, Node.js, and npm. The current project requires Laravel 13 and PHP 8.3 or newer; this workspace was checked with PHP 8.5.
2. Copy `.env.example` to `.env` in PowerShell:

   ```powershell
   Copy-Item .env.example .env
   ```

3. Keep local-only secrets in `.env`; never paste them into source, chat, logs, or issue trackers. The example file contains placeholders. Generate a local key once:

   ```powershell
   php artisan key:generate
   ```

   Do not repeat this on an existing application whose encrypted values or sessions must remain readable.
4. The example uses SQLite. Create the database if needed, then migrate and seed:

   ```powershell
   New-Item -ItemType File -Path database\database.sqlite -Force
   php artisan migrate --seed
   ```

   If using MySQL locally, set the `DB_*` values in `.env` and create a local database/user with least privilege. Never reuse a production credential.
5. To provision the first administrator through the seeder, set `ADMIN_EMAIL` and `ADMIN_PASSWORD` to local-only values before seeding. The seeder refuses to promote an existing non-admin account. For a reviewed existing account, use the explicit `clinic:make-admin` command from a trusted local/production console.
6. Create the public gallery symlink and compile frontend dependencies when needed:

   ```powershell
   php artisan storage:link
   npm install
   npm run build
   ```

   Gallery photos are intentionally public. Do not place confidential veterinary or client documents in `storage/app/public`.
7. Run the application using the existing Laravel local development command or `php artisan serve`. Local HTTP is allowed; production HTTPS settings must not be copied into local secret files without understanding their effect.

## Production deployment checklist

### Secret and application configuration

- Set `APP_ENV=production`, `APP_DEBUG=false`, and the canonical HTTPS `APP_URL` in the host's protected environment/secret manager. Do not commit a production `.env`.
- Create `APP_KEY` once for a new environment, store it in the secret manager, and preserve protected backups. Do not rotate it casually: it may invalidate sessions and prevent decryption of existing encrypted data.
- Configure distinct, least-privilege production database, mail, and storage credentials. Do not reuse local or staging credentials. Rotate credentials if they may have been exposed.
- Set `SESSION_SECURE_COOKIE=true` explicitly if the hosting platform does not preserve the production default. Keep `HttpOnly` and `SameSite=Lax`; verify trusted-proxy/HTTPS detection at the application edge.
- Use a production mail transport for email verification and password reset. Do not use the local `log` mailer in production.
- Check `php artisan config:cache` in the release process. Keep cached configuration in the private `bootstrap/cache` directory, never in a public directory or source control. Clear/rebuild it after changing deployment environment values.

### Web server, database, and storage

- Configure the web server document root to Laravel's `public` directory only. Do not expose the repository root, `.env`, `.git`, `storage`, `bootstrap/cache`, database files, backups, or logs.
- The Apache rules in `public/.htaccess` deny direct hidden-file requests, but do not replace correct web-root configuration. For Nginx or managed hosting, implement equivalent deny rules in the host configuration and verify them with the provider.
- Use a supported production database such as MySQL with a dedicated application account. Review migration SQL and take a verified backup before schema changes. Never run `migrate:fresh`, `db:wipe`, or destructive reset commands against production.
- The `public` storage link exposes only the intended public gallery disk. Keep any future medical documents or confidential uploads on private storage and serve them through an authenticated, authorized controller.
- Restrict file-system permissions for application code, environment configuration, logs, and storage. Ensure logs and backups are not web-accessible.

### Release verification and monitoring

- Require HTTPS at the edge. Confirm redirects, certificate renewal, secure cookies, proxy headers, CSP, and HSTS behavior from the deployed host.
- Run tests, dependency audits, secret scans, and a smoke test after deployment. Confirm authentication, email verification, password reset, role authorization, owner-only pet/appointment access, uploads, and deletion.
- Keep administrator access restricted, use unique credentials and MFA at the identity/hosting layer where available, and review admin provisioning. The application does not currently implement administrator MFA or administrative audit logging.
- Monitor errors without recording passwords, reset tokens, session identifiers, or unnecessary pet/client notes. Restrict access and retention for application, mail, database, and host logs.
- Review and replace every bracketed field in the public policy pages. Obtain clinic management and Philippine legal review before treating them as approved terms.
- This workspace has no Git metadata or production host configuration. Attach the project to its intended repository, inspect full commit history for secrets, and verify the host's document root and secret settings before release.

## Backup and recovery checklist

- Define and document backup frequency, retention, encryption, access control, and recovery objectives for the production database, private storage, public gallery, and required application secrets.
- Store backups outside the web root and outside the same failure domain as the primary system. Encrypt them and limit decryption/key access to authorized operators.
- Preserve the production `APP_KEY` separately in the approved secret manager/escrow procedure. A database backup without required encryption keys may not be recoverable.
- Test restoration to an isolated environment on a schedule. Verify schema, account access, appointment records, file integrity, permissions, and that restored test data is not accidentally exposed.
- For recovery: isolate the incident; preserve forensic evidence; rotate compromised credentials and tokens; restore a known-good release and data snapshot; validate secrets and permissions; smoke-test with authorized accounts; then resume traffic.
- For suspected personal-data incidents, follow the clinic's approved incident-response and notification procedure and obtain qualified legal/privacy advice. Do not rely on this checklist as a substitute.

## Secret scanning

`.gitignore` excludes environment files other than `.env.example`, local SQLite databases, SQL/backup files, private keys, logs, generated configuration cache, and frontend build output. Ignore rules do not remove files already tracked.

No Git repository metadata or Gitleaks installation was available in this workspace, so tracked files and commit history could not be verified or scanned. Before merging or deployment, run an approved secret scanner locally with redaction enabled against both the working tree and full Git history. Never upload real credentials to public scanning services. Revoke and rotate any credential found in repository history; deleting the latest copy is not sufficient.
