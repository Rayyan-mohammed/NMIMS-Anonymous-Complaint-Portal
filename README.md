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
   - Import the SQL files for each school and authority:
     - `campus_director.sql`
     - `hostel_rector.sql`
     - `stme.sql`
     - `sbm.sql`
     - `sol.sql`
     - `sptm.sql`
   - Use phpMyAdmin or the MySQL CLI:
     ```bash
     mysql -u root -p < campus_director.sql
     mysql -u root -p < hostel_rector.sql
     mysql -u root -p < stme.sql
     mysql -u root -p < sbm.sql
     mysql -u root -p < sol.sql
     mysql -u root -p < sptm.sql
     ```

3. **Configure Database Credentials:**
   - By default, the PHP files use:
     ```
     $host = "localhost";
     $user = "root";
     $password = "";
     ```
   - Update these in the PHP files if your database credentials differ.

4. **Deploy the Application:**
   - Place the project files in your web server's root directory (e.g., `htdocs` for XAMPP).
   - Access the portal via `http://localhost/nmims-anonymous-complaint-portal/index.php`.

## Usage

- **Submit Complaint:** Fill out the form on the homepage, select your school, complaint type, escalation level, and provide details.
- **Check Status:** Use your reference number on the "Check Status" page.
- **Admin Panel:** Authorized users can log in to view and update complaint statuses.

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
