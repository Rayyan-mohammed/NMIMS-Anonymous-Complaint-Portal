# APNM6 Complaint Portal

Unified NMIMS complaint portal for public complaint submission, complaint tracking by reference number, and role-based admin handling across schools and escalation levels.

## Overview

APNM6 provides:

- Public complaint submission with anonymous or identified mode.
- School-aware validation (including max allowed study year by school).
- Assignment-based admin dashboard (Program Chair, Deputy Registrar, Campus Director, Hostel Authority).
- Complaint updates and full complaint status history.
- CSV export with advanced filters.
- Backup, restore, and backup health visibility for operations.

## Tech Stack

- PHP >= 8.1
- MySQL/MariaDB
- XAMPP/Apache (or any PHP-compatible web server)
- Plain JavaScript + CSS (no frontend framework)
- PSR-4 namespaced app structure under `app/`

## Current Feature Set

### Public UX

- Inline field-level validation and top form summary on submit form.
- No alert popups for validation errors.
- Loading/success states for submit and copy-reference buttons.
- Dynamic complaint subtype sections (Academic/Hostel).
- Anonymous toggle with conditional identity requirements (Name, SAP ID, Year).

### Complaint Flow

- Complaint gets unique reference number.
- Complaint can be escalated to:
  - Program Chair (school-specific selection)
  - Deputy Registrar
  - Campus Director
- Hostel complaints support hostel authority assignment.

### Admin Productivity

- Role-protected dashboard with:
  - Search
  - Status filter
  - Type filter
  - Role filter
  - School filter
  - Date range filter (from/to)
  - Pagination + per-page controls
- CSV export honoring active filters.
- Complaint detail page includes:
  - Full update timeline
  - Full status history timeline
  - Quick actions without page jumps:
    - Mark In Progress
    - Resolve
    - Add Update

### Operational Features

- Automated DB + logs backup and restore via PowerShell.
- Backup health page to view latest archive timestamp and size.
- Smoke runner for end-to-end baseline verification.

## Project Structure

Key paths:

- `app/bootstrap.php` - autoload, env loading, global error registration.
- `app/Config/database.php` - DB connection, initial schema import, migration queries.
- `app/Http/Controllers/Admin/` - login/dashboard/complaint controllers.
- `app/Http/Controllers/Web/CheckStatusController.php` - status lookup logic.
- `app/Services/AuthService.php` - admin auth.
- `app/Services/ComplaintService.php` - complaint query/update/status-history logic.
- `app/Support/` - auth guard, csrf, logger, env, validator, error handler.
- `public/index.php` - public complaint form.
- `public/submit_complaint.php` - complaint submit endpoint.
- `public/check-status/check-status.php` - reference status page.
- `public/admin/dashboard.php` - admin dashboard + CSV export.
- `public/admin/view_complaint.php` - complaint view + quick actions UI.
- `public/admin/complaint_actions.php` - AJAX quick actions endpoint.
- `public/admin/backup_health.php` - backup monitoring page.
- `scripts/db_backup_restore.ps1` - backup/restore automation.
- `tests/smoke_runner.php` - smoke tests.

## Database Model (Unified)

Primary tables in `app/Config/schema.sql`:

- `schools`
- `users`
- `complaints`
- `complaint_assignments`
- `complaint_updates`
- `complaint_status_history`

Seed data includes key schools and admin accounts for immediate local testing.

## Security Notes

- CSRF token validation for login, complaint submission, and admin actions.
- Role-based access checks via `AuthGuard`.
- Login throttling/temporary lock for repeated failures.
- Password verification uses `password_verify`.
- Centralized logging and registered global error handling.

## Setup

### 1. Requirements

- PHP 8.1+
- MySQL/MariaDB
- Web server serving this repo under document root (for example XAMPP)
- PHP extensions: `mysqli`, `curl` (curl needed for smoke runner)

### 2. Install

```bash
git clone <your-repo-url>
cd APNM6
```

Optional (autoload metadata is present in `composer.json`):

```bash
composer install
```

### 3. Configure Environment

Create `.env` in project root (optional; defaults are used if absent):

```env
DB_HOST=127.0.0.1
DB_NAME=apnm6_db
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4
```

### 4. Run

If hosted as `http://localhost/APNM6/public`, open:

- `http://localhost/APNM6/public/index.php`

Database behavior on startup:

- Creates DB if missing.
- Imports schema if DB is empty.
- Applies backward-compatible migration queries on existing installations.

## Default Admin Accounts (Seeded)

- `director@nmims.edu` / `password`
- `deputy.registrar@nmims.edu` / `password`
- `stme.chair@nmims.edu` / `password`
- `hostel.warden@nmims.edu` / `password`

## Routes and Endpoints

Router mappings in `public/router.php` include:

- `/` -> `public/index.php`
- `/admin/login` -> `public/admin/login.php`
- `/admin/dashboard` -> `public/admin/dashboard.php`
- `/check-status` -> `public/check-status/check-status.php`
- `/submit-complaint` -> `public/submit_complaint.php`

Additional admin pages used directly:

- `public/admin/view_complaint.php?id=<id>`
- `public/admin/complaint_actions.php` (POST)
- `public/admin/backup_health.php`

## Testing

### Smoke Runner

```bash
php tests/smoke_runner.php
```

With overrides:

```bash
set APNM6_SMOKE_BASE_URL=http://localhost/APNM6/public
set APNM6_SMOKE_ADMIN_EMAIL=director@nmims.edu
set APNM6_SMOKE_ADMIN_PASSWORD=password
php tests/smoke_runner.php
```

Smoke checks include:

- Public index reachability
- Anonymous complaint submission
- Non-anonymous validation enforcement
- Reference status lookup
- Admin login
- Admin dashboard access
- CSV export response and content-type

## Backup and Restore (DB + Logs)

Script:

- `scripts/db_backup_restore.ps1`

### Backup

```powershell
powershell -ExecutionPolicy Bypass -File scripts\db_backup_restore.ps1 -Action backup
```

### Restore

```powershell
powershell -ExecutionPolicy Bypass -File scripts\db_backup_restore.ps1 -Action restore -ArchivePath "storage\backups\apnm6_backup_YYYYMMDD_HHMMSS.zip"
```

### Recommended Scheduling

1. Schedule daily backup (for example 2:00 AM) via Windows Task Scheduler.
2. Retain at least 14 days of backup archives.
3. Run weekly restore drills to a safe non-production database.
4. Replicate archives to separate storage for disaster recovery.

### Backup Monitoring

- Open `public/admin/backup_health.php` or use Dashboard -> Backup Health.
- Shows latest archive name, timestamp, size, and recent archive list.

## Notes for Contributors

- This codebase contains both modernized and legacy admin assets; active flow uses the unified controller/service stack under `app/` plus `public/admin/` pages.
- Keep schema and migration behavior backward-compatible for existing local installs.

## License

Educational project. Validate policy and compliance requirements before production deployment.
0