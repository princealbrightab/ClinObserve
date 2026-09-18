# ClinObserve

Clinical Patient Case & Student Observation Management System.

ClinObserve is a Laravel application built for academic clinical learning. It supports three roles: students who record observations, professors who review their assigned students, and HODs (Head of Department / HOS) who manage accounts, assignments, and department-wide activity.

> This system is intended for academic training and learning support only. It is not a clinical diagnosis or treatment tool.

## What the system does

- Student login with first-time password change and role-based access control
- HOD student and professor management, activation, deactivation, and credential reset
- One optional professor assignment per student, with HOD-controlled reassignment
- Shared patient case library with search and filters
- Clinical observation recording with repeat patient visits
- Private image uploads tied to each observation
- Faculty feedback and review workflow
- Dashboard summaries for students, professors, and HODs, scoped to their access
- Monthly calendar and reports for academic activity
- Optional AI-assisted educational review, when enabled

## Roles

### Student

- View their own dashboard and learning activity
- Search and open patient cases
- Record observations and attach images
- Edit their own unlocked encounters
- See faculty feedback relevant to their work
- Use the calendar, personal profile, and activity history

### Professor

- View assigned students' profiles, observations, images, and related patient cases
- Read faculty feedback and AI review history for assigned students
- Add faculty suggestions and edit their own feedback while the student remains assigned
- View an assignment-scoped dashboard and calendar
- Cannot manage accounts or assignments, access department reports, edit student observations, or request AI reviews on a student's behalf

### HOD / HOS

- Manage student accounts and profiles
- Create and manage professor accounts
- Assign, reassign, or unassign students while retaining department-wide access
- Create and update patient cases
- Review all student observations in the department
- Add private faculty feedback
- View reports and department activity trends
- Monitor student participation and unresolved feedback

## InfinityFree hosting (no server terminal)

Website: [ClinObserve](https://clinobserve.freedev.app).

InfinityFree uses `htdocs` as the web root and does not provide shell access. Prepare dependencies on your own computer, upload with File Manager/FTP, and use the protected browser pages below for database maintenance. See [InfinityFree's Laravel hosting guidance](https://forum.infinityfree.com/t/how-to-install-a-laravel-site-on-infinityfree/118578) for host limitations. This project's database sessions and private clinical image storage remain in use; do not add a public storage rewrite or switch sessions to file storage, because account management revokes database sessions.

### Upload layout

For the existing `/clinobserve_app/run-migrations.php` and `/clinobserve_app/run-seeder.php` URLs, use this layout:

```text
htdocs/
  index.php                     # deployed copy of public/index.php
  .htaccess                     # copy of public/.htaccess
  css/, js/, vendor/bootstrap/   # public assets
  clinobserve_app/
    .htaccess                   # project-root protection rules
    .env                        # hosted settings; never committed
    run-migrations.php
    run-seeder.php
    app/, bootstrap/, config/, database/, resources/, routes/
    storage/, vendor/
```

In the deployed `htdocs/index.php`, change the three Laravel file references to `__DIR__.'/clinobserve_app/storage/framework/maintenance.php'`, `__DIR__.'/clinobserve_app/vendor/autoload.php'`, and `__DIR__.'/clinobserve_app/bootstrap/app.php'`. After loading the application, call `$app->usePublicPath(__DIR__);` before `$app->handleRequest(...)`. Keep the repository's `public/index.php` unchanged for local development.

Upload the project-root `.htaccess` to **`htdocs/clinobserve_app/.htaccess`**, not `htdocs/.htaccess`. It denies browser access to application files while allowing the two protected scripts and the optional `public` directory. Keep `.env`, logs, clinical images, and vendor files under the protected application directory. Never copy them beside `htdocs/index.php`.

Include `bootstrap/browser-maintenance.php`, `app/Services/BrowserMaintenanceService.php`, `config/browser-maintenance.php`, `resources/views/maintenance.blade.php`, both new seeders, and all pending migrations when uploading these scripts. Upload the updated application files for professor support as well. No maintenance script contains a password or token; keep the scripts tracked in Git.

Composer and test commands in this README run **locally**, not on InfinityFree. Build a separate deployment copy with `composer install --no-dev`, then upload its `vendor` directory. Do not upload local `.env`, configuration/route/view caches, `node_modules`, tests, or development tooling. The existing Bootstrap assets need no Node process on the host. Ensure the host's PHP version and extensions satisfy the installed packages, including GD for images. If a file disappears during upload, check InfinityFree's file-size limits; oversized PHP autoload files may need an unoptimized Composer autoloader prepared locally.

### Hosted configuration in File Manager

Edit `htdocs/clinobserve_app/.env` using the actual MySQL values from InfinityFree's control panel. The host is the supplied database hostname, not `localhost`. Keep an existing `APP_KEY`; do not regenerate it during an update. On a fresh installation, generate the key in the local deployment copy before uploading its hosted configuration.

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://clinobserve.freedev.app
DB_CONNECTION=mysql
DB_HOST=your-control-panel-mysql-host
DB_PORT=3306
DB_DATABASE=your-control-panel-database-name
DB_USERNAME=your-control-panel-database-user
DB_PASSWORD="your-database-password"
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=file
QUEUE_CONNECTION=sync
BROWSER_MAINTENANCE_ENABLED=true
BROWSER_MAINTENANCE_TOKEN="your-random-maintenance-token"
BROWSER_MAINTENANCE_ALLOW_DEMO_SEED=true
INITIAL_HOD_NAME="Your Department Head"
INITIAL_HOD_EMAIL="your-hod-email@example.com"
INITIAL_HOD_PASSWORD="your-unique-temporary-password"
```

Replace every placeholder. Generate the maintenance token with a password manager: use at least 32 random characters, preferably 48 or more, and never put it in a URL. The placeholder token above is intentionally too short to enable access. The initial HOD password needs at least 12 characters with letters and numbers. Keep `APP_ENV=production`, including for synthetic demonstration data.

If configuration was cached, remove **only `bootstrap/cache/config.php`** in File Manager after changing `.env`; otherwise the old enablement flag, token, or database settings can remain active. Keep `storage` and `bootstrap/cache` writable. The browser scripts use a separate native PHP session, so they work before the application's database `sessions` table exists.

### Run migrations from the browser

1. Back up the hosted database with phpMyAdmin before schema updates.
2. Open [run-migrations.php](https://clinobserve.freedev.app/clinobserve_app/run-migrations.php) over HTTPS. A page visit only shows a form.
3. Enter the maintenance token, choose **Run pending migrations**, confirm the action, and submit. This applies all pending migrations, including the professor assignment column, without dropping existing tables.
4. Choose **Check migration status** to verify completion. On a brand-new database, run migrations before checking status because the migration repository may not exist yet.
5. After uploading code or changing settings, use **Clear configuration, route and view caches** when needed. This does not clear application data or sessions. Clear a stale `bootstrap/cache/config.php` manually first if it prevents access to the page.

Arbitrary command names, rollback commands, and shell commands are not accepted. The explicit reset workflow below is the only action that drops tables. A shared filesystem lock prevents overlapping maintenance requests. Migrations may finish partially before a host timeout; check status before retrying. Pending migrations can be rerun, but custom data migrations must still be written to tolerate their own failure modes.

### Fix a maintenance setup error

If the page says **Maintenance is disabled**, add `BROWSER_MAINTENANCE_ENABLED=true` to the **hosted** `htdocs/clinobserve_app/.env`. If it says **Maintenance token is not configured**, add `BROWSER_MAINTENANCE_TOKEN` with a random value of at least 32 characters. Set `BROWSER_MAINTENANCE_ALLOW_DEMO_SEED=true` for demo seeding or a reset with demo data.

Delete `htdocs/clinobserve_app/bootstrap/cache/config.php` if it exists, then reload. These settings must be read after Laravel boots: do not put `getenv()` overrides in the entry-point files and do not use `APP_KEY` as a maintenance password. Upload both runners, their shared bootstrap, the service, config, view, and seeders together. The older generic **Not found** response can also mean the server still has an older copy of these files.

### Clean the database, migrate, and seed

This is a destructive setup operation for the database configured in the hosted application. **It permanently deletes every table and view, including all existing accounts, sessions, observations, feedback, and any unrelated tables in that same database.** Export a backup with phpMyAdmin first. Uploaded files are not deleted; any old file references disappear with the old database rows.

1. Open the migration page and select **Delete all database tables, migrate and seed**.
2. Enter the maintenance token and the new HOD's name, email, temporary password, and password confirmation. These credentials are validated before deletion and are not displayed in the result.
3. Enter the exact database name from InfinityFree's MySQL panel and type `RESET DATABASE`.
4. Tick the action confirmation and submit once.
5. The fixed sequence drops tables/views, runs all uploaded migrations, creates the new HOD, and adds the hosted synthetic dataset. Commands stop on failure. No migration files are generated on the host; the uploaded migration files recreate the schema.
6. Sign in with the new HOD credentials and complete the password change. Reset passwords for whichever demo professor/student accounts you want to use.

The reset is not one atomic database transaction. If migration or seeding fails after deletion, check migration status and use the normal migration/seeding actions to finish; do not assume the old data was restored. Do not repeat reset merely to recover from a seeding error.

### Run both HOD setup and demo seeding

1. Open [run-seeder.php](https://clinobserve.freedev.app/clinobserve_app/run-seeder.php).
2. Enter the token, select **Create initial HOD**, confirm, and submit. Enter the new HOD credentials in the form, or leave them blank to use `INITIAL_HOD_*` settings. `DeploymentSeeder` requires a password change on first login. If any HOD already exists, it does nothing and never resets an existing password or promotes an existing student.
3. Select **Add synthetic demo students and cases**, confirm, and submit. This requires `BROWSER_MAINTENANCE_ALLOW_DEMO_SEED=true` and an active HOD. `HostedDemoSeeder` adds two demo professors, six assigned students, eight synthetic cases, 24 observations, and eight sample faculty comments.
4. Sign in as HOD. Reset the desired demo professor/student account's password through its account page before trying that account; hosted demo passwords are random and are never displayed. Students and professors must change the reset password at login.
5. Repeating demo seeding skips the operation if any reserved hosted demo account email already exists. It does not overwrite existing accounts, duplicate observations, or fill in a partially edited demo set.

Hosted demo email patterns are `hosted-professor1@clinobserve.test` through `hosted-professor2@clinobserve.test`, and `hosted-student1@clinobserve.test` through `hosted-student6@clinobserve.test`. These are synthetic accounts, not real email inboxes. The original `DatabaseSeeder`/`DemoSeeder` remain local/testing-only and are not used by the hosted runner.

### Disable maintenance after use

Set `BROWSER_MAINTENANCE_ENABLED=false` and `BROWSER_MAINTENANCE_ALLOW_DEMO_SEED=false`, then clear `BROWSER_MAINTENANCE_TOKEN` and `INITIAL_HOD_PASSWORD` in the hosted `.env`. Remove `bootstrap/cache/config.php` if present so the changes apply. Both URLs should return 404 while disabled. You may also remove the two entry-point scripts from the host and upload them again for the next maintenance window.

Check that application files return 403/404 rather than downloadable content. If a page reports a failure, inspect the uploaded files, database settings, and writable directories through File Manager/phpMyAdmin; raw exceptions and credentials are intentionally not printed. Do not publish logs or enable public debug output to troubleshoot.

### Verification scope

The latest targeted maintenance run passed 22 tests with 99 assertions, including reset preflight checks and execution through the actual PHP entry points. Pint passed. The preceding full regression run passed 63 of 66 tests; three existing image tests could not run because the local PHP runtime lacks GD.

The two actual entry-point scripts are covered by tests against temporary SQLite databases with `APP_ENV=production` and `SESSION_DRIVER=database`, including migrations, HOD setup, hosted demo seeding, cache clearing, and destructive reset with replacement credentials. The application directory protection rules and HTTPS/session behavior still need verification on InfinityFree after upload. No live migration or seed operation was executed during this code review.

## Local quick start

### Prerequisites

- PHP 8.3+
- Composer 2
- MySQL 8.x or compatible MariaDB
- GD extension enabled for image processing

### Install

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configure the database connection in `.env` before migrating. For MySQL/MariaDB, set `DB_CONNECTION=mysql` and valid host, database, username, and password values. For SQLite, use an existing SQLite file path as `DB_DATABASE`.

```bash
php artisan migrate
```

### Upgrade for professor assignments

On InfinityFree, use **Run pending migrations** on the browser page described above. Locally, run `php artisan migrate` against the intended database to apply `2026_09_17_094841_add_professor_id_to_users_table.php`. Existing students remain unassigned until the HOD selects a professor; existing accounts and reviews are preserved.

In the HOD workspace, open **Professors > Add professor**, then select **Assigned professor** when adding or editing a student. New assignments require an active professor. A professor can supervise many students; each student has at most one current professor. Reassignment revokes the previous professor's access to that student's records, including previously authored feedback, while preserving the feedback for the student, current professor, and HODs.

### Run locally

```bash
php artisan serve
```

Or use the local helper in this workspace:

```powershell
./scripts/start-local.ps1
```

## Demo accounts

Local demo accounts are included for testing only:

| Role | Email | Password |
| --- | --- | --- |
| HOD | hod@clinobserve.test | FacultyDemo123 |
| Student | student1@clinobserve.test | StudentDemo123 |

Use fresh, secure credentials in any real environment. The local demo seeder does not provision professors. The hosted demo action above creates two demo professors with random temporary passwords; ordinary professor accounts are created by the HOD.

## Main documentation

- [USER_GUIDE.md](USER_GUIDE.md) — end-user workflows for students, professors, and HODs
- [PROJECT_PLAN.md](PROJECT_PLAN.md) — architecture and implementation plan
- [DATABASE_DESIGN.md](DATABASE_DESIGN.md) — database structure

## Project status

ClinObserve is implemented as a Laravel-based academic observation and review platform with role-based access, private records, dashboard analytics, and secure handling for patient-related documentation.

### Verification status for professor support

- All 13 professor-management tests passed, including assignment validation, reassignment, private record access, and account restrictions.
- The full regression run before the final edge-case test had 45 passing tests and three existing image-test errors because the local PHP runtime lacks GD. The final professor-only rerun passed all 13 tests; Pint passed.
- The assignment migration was tested on isolated SQLite. It has not been applied to the configured application database, which pointed to a missing SQLite file during verification. Validate the migration on the intended MySQL/MariaDB environment before release.

```bash
php artisan test --compact tests/Feature/ProfessorManagementTest.php
php artisan test --compact
```

## Notes

- Patient data is de-identified and shared only within the training environment.
- AI review is optional and must be enabled explicitly with provider configuration.
- Faculty feedback and AI reviews are visible to the authoring student, their currently assigned professor, and HODs; unrelated students and professors cannot access them.
- Professor case lists, timelines, encounter counts, calendars, and images exclude unassigned students' observations, including on shared patient cases.

For full operational guidance, begin with [USER_GUIDE.md](USER_GUIDE.md).

No Supervisor, cron, persistent queue worker, Redis, cloud storage, Docker or Node process is mandatory. On InfinityFree's fixed web root, the protected `clinobserve_app/.htaccess` and separate public files must prevent direct access to private application files.

## Security and project scope

- Active-account middleware and policies protect every authenticated request and object.
- Validated field allow-lists prevent unauthorized role or ownership changes. Only HOD student-management requests accept a validated professor assignment.
- CSRF protection, escaped output, password hashing, throttles and session invalidation are implemented.
- No user/case/encounter/review deletion UI; academic history is preserved.
- Faculty comment edits retain creation/update times but not previous text; append a comment for a historical correction.
- No real patient data is seeded and no patient image goes to AI.
- This college project is not a certified clinical records or medical decision system.
