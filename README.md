# NMIMS-Anonymous-Complaint-Portal

A web-based platform for students and stakeholders of NMIMS to submit complaints anonymously, track their status, and ensure their concerns are addressed by the appropriate authorities. The portal supports multiple schools and escalation levels, providing a transparent and efficient complaint management system.

## Features

- **Anonymous Complaint Submission:** Users can submit complaints without revealing their identity.
- **Multi-School Support:** Handles complaints for STME, SBM, SOL, SPTM, and more.
- **Escalation Levels:** Complaints can be directed to Program Chair, Deputy Registrar, Hostel Rector, or Campus Director.
- **Status Tracking:** Users receive a reference number to check the status of their complaint.
- **Admin Panel:** For authorized personnel to view, update, and resolve complaints.
- **Statistics Dashboard:** Aggregated stats for complaints by status and school.
- **Modern UI:** Responsive and visually appealing interface.


## Getting Started

### Prerequisites

- PHP 7.4 or higher
- MySQL/MariaDB
- Web server (e.g., Apache, XAMPP)

### Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/Rayyan-mohammed/NMIMS-Anonymous-Complaint-Portal.git
   cd nmims-anonymous-complaint-portal
   ```

2. **Database Setup:**
   - Create a single database named `apnm6_db`.
   - Import the unified SQL file:
     - `database/apnm6_unified.sql`
   - Use phpMyAdmin or MySQL CLI:
     ```bash
     mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS apnm6_db"
     mysql -u root -p apnm6_db < database/apnm6_unified.sql
     ```

3. **Configure Database Credentials:**
   - Copy `.env.example` to `.env` and update DB values if needed.
   - Defaults:
     - `DB_HOST=127.0.0.1`
     - `DB_PORT=3306`
     - `DB_NAME=apnm6_db`
     - `DB_USER=root`
     - `DB_PASS=`

4. **Deploy the Application:**
   - Place the project files in your web server's root directory (e.g., `htdocs` for XAMPP).
   - Access the portal via `http://localhost/nmims-anonymous-complaint-portal/index.php`.

## Usage

- **Submit Complaint:** Fill out the form on the homepage, select your school, complaint type, escalation level, and provide details.
- **Check Status:** Use your reference number on the "Check Status" page.
- **Admin Panel:** Authorized users can log in to view and update complaint statuses.

### Quick Admin Seed Accounts
- `director@nmims.edu` / `password`
- `deputy.registrar@nmims.edu` / `password`
- `stme.chair@nmims.edu` / `password`
- `hostel.warden@nmims.edu` / `password`

### Automated Smoke Check
Run end-to-end baseline checks:

```bash
php tests/smoke_runner.php
```

Optional overrides:

```bash
set APNM6_SMOKE_BASE_URL=http://localhost/APNM6/public
set APNM6_SMOKE_ADMIN_EMAIL=director@nmims.edu
set APNM6_SMOKE_ADMIN_PASSWORD=password
php tests/smoke_runner.php
```

### Automated Backup and Restore (DB + Logs)

The project includes a PowerShell automation script:

- `scripts/db_backup_restore.ps1`

Run backup manually:

```powershell
powershell -ExecutionPolicy Bypass -File scripts\db_backup_restore.ps1 -Action backup
```

Run restore from a backup archive:

```powershell
powershell -ExecutionPolicy Bypass -File scripts\db_backup_restore.ps1 -Action restore -ArchivePath "storage\backups\apnm6_backup_YYYYMMDD_HHMMSS.zip"
```

Recommended schedule on Windows Task Scheduler:

1. Run `backup` daily at off-peak hours (example: 2:00 AM).
2. Keep at least 14 days of archives in `storage/backups`.
3. Add a weekly restore drill on a non-production database to verify backups.
4. Store backup archives on a second disk or network location for disaster recovery.

Admin monitoring page:

- Open `public/admin/backup_health.php` (or use Dashboard -> Backup Health) to view the latest backup archive timestamp and size.

## File Structure

- `index.php` — Main landing page and complaint submission form.
- `submit_complaint.php` — Handles complaint form submissions.
- `check_status.php` — Allows users to check complaint status.
- `admin.php` — Admin login and dashboard.
- `update_status.php` — API endpoint for updating complaint statuses.
- `get_complaint_stats.php` — Provides statistics for dashboards.
- `*.css` — Stylesheets for various pages.
- `*.sql` — Database schema for each school/authority.

## Database Structure

Each school/authority has its own database and tables, e.g.:

```sql
CREATE TABLE program_chair_complaints (
  reference_number VARCHAR(20) PRIMARY KEY,
  complaint_details TEXT NOT NULL,
  status VARCHAR(50) DEFAULT 'Pending',
  submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

## Security & Privacy

- All complaints are stored without user-identifying information.
- Reference numbers are generated for tracking.
- Only authorized personnel can access the admin panel.

## Customization

- Update the logo and branding in the HTML files as needed.
- Modify escalation levels or add new schools by updating the form and database.

## License

This project is for educational purposes. Please check with NMIMS administration before deploying in a production environment.

---

**Contributions and suggestions are welcome!** 
0