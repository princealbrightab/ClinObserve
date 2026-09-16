# ClinObserve

Clinical Patient Case & Student Observation Management System.

ClinObserve is a Laravel application built for academic clinical learning. It supports two main roles: students who record and review observations, and HODs (Head of Department / HOS) who manage students, monitor cases, and provide faculty feedback.

> This system is intended for academic training and learning support only. It is not a clinical diagnosis or treatment tool.

## What the system does

- Student login with first-time password change and role-based access control
- HOD student management, activation, deactivation, and credential reset
- Shared patient case library with search and filters
- Clinical observation recording with repeat patient visits
- Private image uploads tied to each observation
- Faculty feedback and review workflow
- Dashboard summaries for both students and HODs
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

### HOD / HOS
- Manage student accounts and profiles
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
php artisan migrate
```

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

Use fresh, secure credentials in any real environment.

## Main documentation

- [USER_GUIDE.md](USER_GUIDE.md) — end-user workflow for students and HODs
- [PROJECT_PLAN.md](PROJECT_PLAN.md) — architecture and implementation plan
- [DATABASE_DESIGN.md](DATABASE_DESIGN.md) — database structure

## Project status

ClinObserve is implemented as a Laravel-based academic observation and review platform with role-based access, private records, dashboard analytics, and secure handling for patient-related documentation.

## Notes

- Patient data is de-identified and shared only within the training environment.
- AI review is optional and must be enabled explicitly with provider configuration.
- Faculty feedback and AI review content are private and not visible to unrelated students.

For full operational guidance, begin with [USER_GUIDE.md](USER_GUIDE.md).

No Supervisor, cron, persistent queue worker, Redis, cloud storage, Docker or Node process is mandatory. A host that cannot keep the repository/private files outside web access is unsuitable.

## Security and project scope

- Active-account middleware and policies protect every authenticated request and object.
- Validated field allow-lists prevent role/ownership assignments from browser payloads.
- CSRF protection, escaped output, password hashing, throttles and session invalidation are implemented.
- No user/case/encounter/review deletion UI; academic history is preserved.
- Faculty comment edits retain creation/update times but not previous text; append a comment for a historical correction.
- No real patient data is seeded and no patient image goes to AI.
- This college project is not a certified clinical records or medical decision system.

