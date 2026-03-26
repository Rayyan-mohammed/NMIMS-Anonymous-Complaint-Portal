<?php
session_start();
require_once '../config/database.php';
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Http\Controllers\Admin\DashboardController;

$controller = new DashboardController($conn);
$dashboardData = $controller->getData($_SESSION, $_GET);

if (($dashboardData['redirect'] ?? null) !== null) {
    header('Location: ' . $dashboardData['redirect']);
    exit();
}

if (($dashboardData['mode'] ?? 'html') === 'csv') {
    $fileName = 'complaints_export_' . date('Ymd_His') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Reference Number', 'School', 'Student Name', 'SAP ID', 'Year', 'Type', 'Subtype', 'Status', 'Assigned Role', 'Escalation', 'Anonymous', 'Submitted At', 'Details']);

    foreach ($dashboardData['rows'] as $row) {
        fputcsv($output, [
            (string) ($row['reference_number'] ?? ''),
            (string) ($row['school_name'] ?? ''),
            ((int) ($row['is_anonymous'] ?? 1) === 1) ? 'Anonymous' : (string) ($row['student_name'] ?? ''),
            ((int) ($row['is_anonymous'] ?? 1) === 1) ? '' : (string) ($row['sap_id'] ?? ''),
            ((int) ($row['is_anonymous'] ?? 1) === 1) ? '' : (string) ($row['study_year'] ?? ''),
            (string) ($row['complaint_type'] ?? ''),
            (string) ($row['complaint_subtype'] ?? ''),
            (string) ($row['status'] ?? ''),
            (string) ($row['assignment_role'] ?? ''),
            (string) ($row['escalation_level'] ?? ''),
            ((int) ($row['is_anonymous'] ?? 1) === 1) ? 'Yes' : 'No',
            (string) ($row['created_at'] ?? ''),
            (string) ($row['complaint_details'] ?? ''),
        ]);
    }

    fclose($output);
    exit();
}

$role = $dashboardData['role'];
$school_name = $dashboardData['school_name'];
$filters = $dashboardData['filters'];
$schools = $dashboardData['schools'];
$complaints = $dashboardData['complaints'];
$pagination = $dashboardData['pagination'];
$flashSuccess = (string) ($_SESSION['flash_success'] ?? '');
unset($_SESSION['flash_success']);

function dashboardQuery(array $filters, array $overrides = []): string
{
    $query = array_merge([
        'search' => $filters['search'] ?? '',
        'status' => $filters['status'] ?? '',
        'type' => $filters['type'] ?? '',
        'role' => $filters['role'] ?? '',
        'school' => $filters['school'] ?? '',
        'from_date' => $filters['from_date'] ?? '',
        'to_date' => $filters['to_date'] ?? '',
    ], $overrides);

    return http_build_query(array_filter(
        $query,
        static fn($value) => $value !== null && $value !== '' && $value !== 0 && $value !== '0'
    ));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - NMIMS Complaint Portal</title>
    <link rel="stylesheet" href="../assets/css/admin/login.css?v=20260326f">
</head>
<body class="admin-dashboard-page">
    <div class="dashboard-container">
        <header class="dashboard-topbar">
            <div class="dashboard-topbar-left">
                <div class="brand-logo dashboard-brand-logo">
                    <img src="../assets/nmims_logo.jpg" alt="NMIMS University Logo">
                </div>
                <div class="dashboard-headline">
                    <h1>Complaint Dashboard</h1>
                    <p>
                        Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>
                        • <?php echo ucwords(str_replace('_', ' ', $role)); ?>
                        <?php if ($school_name): ?>
                            • <?php echo htmlspecialchars($school_name); ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="header-actions dashboard-topbar-actions">
                <a class="analytics-btn" href="analytics.php">Analytics</a>
                <a class="profile-btn" href="profile.php">Profile</a>
                <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>
            </div>
        </header>

        <div class="welcome-message">
            <h2>Your Complaints</h2>
            <p>Use filters to find specific records and manage updates quickly.</p>
        </div>

        <?php if ($flashSuccess !== ''): ?>
            <div class="success-message"><?php echo htmlspecialchars($flashSuccess); ?></div>
        <?php endif; ?>

        <form method="GET" class="filters">
            <div>
                <label for="search">Search</label>
                <input id="search" type="text" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>" placeholder="Ref, SAP, details">
            </div>
            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All</option>
                    <option value="pending" <?php echo $filters['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="in_progress" <?php echo $filters['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="resolved" <?php echo $filters['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                </select>
            </div>
            <div>
                <label for="type">Type</label>
                <select id="type" name="type">
                    <option value="">All</option>
                    <option value="academic" <?php echo $filters['type'] === 'academic' ? 'selected' : ''; ?>>Academic</option>
                    <option value="hostel" <?php echo $filters['type'] === 'hostel' ? 'selected' : ''; ?>>Hostel</option>
                </select>
            </div>
            <?php if ($role !== 'program_chair'): ?>
                <div>
                    <label for="role">Role</label>
                    <select id="role" name="role">
                        <option value="">All</option>
                        <option value="program_chair" <?php echo $filters['role'] === 'program_chair' ? 'selected' : ''; ?>>Program Chair</option>
                        <option value="deputy_registrar" <?php echo $filters['role'] === 'deputy_registrar' ? 'selected' : ''; ?>>Deputy Registrar</option>
                        <option value="campus_director" <?php echo $filters['role'] === 'campus_director' ? 'selected' : ''; ?>>Campus Director</option>
                        <option value="hostel_authority" <?php echo $filters['role'] === 'hostel_authority' ? 'selected' : ''; ?>>Hostel Authority</option>
                    </select>
                </div>
            <?php endif; ?>
            <?php if ($role !== 'program_chair'): ?>
                <div>
                    <label for="school">School</label>
                    <select id="school" name="school">
                        <option value="">All</option>
                        <?php foreach ($schools as $school): ?>
                            <option value="<?php echo (int) $school['school_id']; ?>" <?php echo (int) ($filters['school'] ?? 0) === (int) $school['school_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars((string) $school['school_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <div>
                <label for="from_date">From Date</label>
                <input id="from_date" type="date" name="from_date" value="<?php echo htmlspecialchars((string) ($filters['from_date'] ?? '')); ?>">
            </div>
            <div>
                <label for="to_date">To Date</label>
                <input id="to_date" type="date" name="to_date" value="<?php echo htmlspecialchars((string) ($filters['to_date'] ?? '')); ?>">
            </div>
            <button type="submit">Apply</button>
            <a href="dashboard.php">Reset</a>
        </form>

        <div class="toolbar">
            <form method="GET" class="per-page">
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>">
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($filters['status']); ?>">
                <input type="hidden" name="type" value="<?php echo htmlspecialchars($filters['type']); ?>">
                <?php if ($role !== 'program_chair'): ?>
                    <input type="hidden" name="role" value="<?php echo htmlspecialchars((string) ($filters['role'] ?? '')); ?>">
                <?php endif; ?>
                <?php if ($role !== 'program_chair'): ?>
                    <input type="hidden" name="school" value="<?php echo htmlspecialchars((string) ($filters['school'] ?? '')); ?>">
                <?php endif; ?>
                <input type="hidden" name="from_date" value="<?php echo htmlspecialchars((string) ($filters['from_date'] ?? '')); ?>">
                <input type="hidden" name="to_date" value="<?php echo htmlspecialchars((string) ($filters['to_date'] ?? '')); ?>">
                <label for="per_page">Rows per page</label>
                <select id="per_page" name="per_page" onchange="this.form.submit()">
                    <option value="10" <?php echo $pagination['per_page'] === 10 ? 'selected' : ''; ?>>10</option>
                    <option value="25" <?php echo $pagination['per_page'] === 25 ? 'selected' : ''; ?>>25</option>
                    <option value="50" <?php echo $pagination['per_page'] === 50 ? 'selected' : ''; ?>>50</option>
                </select>
            </form>

            <div class="toolbar-actions">
                <a class="backup-btn" href="backup_health.php">Backup Health</a>
                <a class="export-btn" href="dashboard.php?<?php echo htmlspecialchars(dashboardQuery($filters, ['export' => 'csv'])); ?>">Export CSV</a>
            </div>
        </div>

        <p class="summary">
            Showing page <?php echo (int) $pagination['page']; ?> of <?php echo (int) $pagination['total_pages']; ?>
            (<?php echo (int) $pagination['total']; ?> complaints)
        </p>

        <?php if (empty($complaints)): ?>
            <div class="no-complaints">
                <p>No complaints assigned to you at this time.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
            <table class="complaints-table">
                <thead>
                    <tr>
                        <th>Reference Number</th>
                        <th>School</th>
                        <th>Student Name</th>
                        <th>SAP ID</th>
                        <th>Year</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Submitted Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($complaints as $complaint): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($complaint['reference_number']); ?></td>
                            <td><?php echo htmlspecialchars($complaint['school_name']); ?></td>
                            <td>
                                <?php
                                if ((int) ($complaint['is_anonymous'] ?? 1) === 1) {
                                    echo 'Anonymous';
                                } else {
                                    echo htmlspecialchars((string) ($complaint['student_name'] ?? '-'));
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if ((int) ($complaint['is_anonymous'] ?? 1) === 1) {
                                    echo '-';
                                } else {
                                    echo htmlspecialchars((string) ($complaint['sap_id'] ?? '-'));
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if ((int) ($complaint['is_anonymous'] ?? 1) === 1) {
                                    echo '-';
                                } else {
                                    echo htmlspecialchars((string) ($complaint['study_year'] ?? '-'));
                                }
                                ?>
                            </td>
                            <td><?php echo ucwords($complaint['complaint_type']); ?></td>
                            <td class="status-<?php echo $complaint['status']; ?>">
                                <?php echo ucwords(str_replace('_', ' ', $complaint['status'])); ?>
                            </td>
                            <td><?php echo date('d M Y', strtotime($complaint['created_at'])); ?></td>
                            <td class="action-buttons">
                                <button class="view-btn" onclick="viewComplaint(<?php echo $complaint['complaint_id']; ?>)">View</button>
                                <button class="update-btn" onclick="updateStatus(<?php echo $complaint['complaint_id']; ?>)">Update Status</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>

            <div class="pagination">
                <?php if ($pagination['page'] > 1): ?>
                    <a href="dashboard.php?<?php echo htmlspecialchars(dashboardQuery($filters, ['page' => $pagination['page'] - 1, 'per_page' => $pagination['per_page']])); ?>">Previous</a>
                <?php endif; ?>

                <?php
                $startPage = max(1, $pagination['page'] - 2);
                $endPage = min($pagination['total_pages'], $pagination['page'] + 2);
                for ($p = $startPage; $p <= $endPage; $p++):
                ?>
                    <?php if ($p === (int) $pagination['page']): ?>
                        <span class="active"><?php echo $p; ?></span>
                    <?php else: ?>
                        <a href="dashboard.php?<?php echo htmlspecialchars(dashboardQuery($filters, ['page' => $p, 'per_page' => $pagination['per_page']])); ?>"><?php echo $p; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($pagination['page'] < $pagination['total_pages']): ?>
                    <a href="dashboard.php?<?php echo htmlspecialchars(dashboardQuery($filters, ['page' => $pagination['page'] + 1, 'per_page' => $pagination['per_page']])); ?>">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function viewComplaint(complaintId) {
            window.location.href = `view_complaint.php?id=${complaintId}`;
        }

        function updateStatus(complaintId) {
            window.location.href = `update_status.php?id=${complaintId}`;
        }
    </script>
</body>
</html> 