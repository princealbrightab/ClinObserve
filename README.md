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

## Quick start

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

Run `php artisan migrate` against the intended database to apply `2026_09_17_094841_add_professor_id_to_users_table.php`. Existing students remain unassigned until the HOD selects a professor; existing accounts and reviews are preserved.

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
| HOD | hod@clinobserve.test | FacultyDemo123! |
| Student | student1@clinobserve.test | StudentDemo123! |

Use fresh, secure credentials in any real environment. Professor accounts are created by the HOD; the demo seeder does not provision a professor account.

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

No Supervisor, cron, persistent queue worker, Redis, cloud storage, Docker or Node process is mandatory. A host that cannot keep the repository/private files outside web access is unsuitable.

## Security and project scope

- Active-account middleware and policies protect every authenticated request and object.
- Validated field allow-lists prevent unauthorized role or ownership changes. Only HOD student-management requests accept a validated professor assignment.
- CSRF protection, escaped output, password hashing, throttles and session invalidation are implemented.
- No user/case/encounter/review deletion UI; academic history is preserved.
- Faculty comment edits retain creation/update times but not previous text; append a comment for a historical correction.
- No real patient data is seeded and no patient image goes to AI.
- This college project is not a certified clinical records or medical decision system.
