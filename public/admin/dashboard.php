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
    <link rel="stylesheet" href="../assets/css/admin/login.css?v=20260325b">
    <style>
        .dashboard-container {
            max-width: 1240px;
            margin: 20px auto 34px;
            padding: 24px;
            border: 1px solid rgba(14, 90, 102, 0.22);
            background: linear-gradient(180deg, rgba(255,255,255,0.94), rgba(247,250,253,0.9));
        }
        .welcome-message {
            margin-bottom: 18px;
            border-bottom: 1px solid rgba(31, 41, 51, 0.1);
            padding-bottom: 10px;
        }
        .complaints-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 22px rgba(22, 36, 54, 0.08);
        }
        .complaints-table th, .complaints-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(31, 41, 51, 0.1);
        }
        .complaints-table th {
            background: rgba(14, 90, 102, 0.12);
            color: #0f3c4f;
        }
        .status-pending {
            color: #b47803;
            font-weight: 700;
        }
        .status-in_progress {
            color: #0d6577;
            font-weight: 700;
        }
        .status-in-progress {
            color: #0d6577;
            font-weight: 700;
        }
        .status-resolved {
            color: #1f7d4d;
            font-weight: 700;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .action-buttons button {
            padding: 8px 12px;
            border: none;
            border-radius: 999px;
            cursor: pointer;
            font-weight: 700;
        }
        .view-btn {
            background: linear-gradient(180deg, #0e6d82, #0b5768);
            color: white;
        }
        .update-btn {
            background: linear-gradient(180deg, #2f9b63, #1f7d4d);
            color: white;
        }
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 9px 16px;
            background: linear-gradient(180deg, #b31b34, #8f162a);
            color: white;
            border: none;
            border-radius: 999px;
            cursor: pointer;
            font-weight: 700;
        }
        .no-complaints {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        .brand-logo {
            margin-bottom: 10px;
        }
        .brand-logo img {
            max-width: 220px;
            width: 100%;
            height: auto;
            filter: drop-shadow(0 8px 18px rgba(24, 42, 62, 0.2));
        }
        .filters {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin: 15px 0;
            align-items: end;
            background: rgba(14, 90, 102, 0.06);
            border: 1px solid rgba(14, 90, 102, 0.15);
            border-radius: 12px;
            padding: 12px;
        }
        .filters input, .filters select {
            padding: 8px;
            border: 1px solid rgba(31, 41, 51, 0.15);
            border-radius: 10px;
        }
        .filters button {
            padding: 8px 12px;
            border: none;
            border-radius: 999px;
            cursor: pointer;
            background: linear-gradient(180deg, #b31b34, #8f162a);
            color: #fff;
        }
        .filters a {
            color: #0e5a66;
            text-decoration: none;
            font-weight: 700;
        }
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: end;
            gap: 12px;
            margin-top: 12px;
            flex-wrap: wrap;
        }
        .toolbar .per-page select {
            padding: 8px;
            border: 1px solid rgba(31, 41, 51, 0.15);
            border-radius: 10px;
        }
        .export-btn {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 999px;
            color: #fff;
            background: linear-gradient(180deg, #2f9b63, #1f7d4d);
            text-decoration: none;
            font-weight: 700;
        }
        .toolbar-actions {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }
        .backup-btn {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 999px;
            color: #fff;
            background: linear-gradient(180deg, #0e6d82, #0b5768);
            text-decoration: none;
            font-weight: 700;
        }
        .pagination {
            margin-top: 16px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }
        .pagination a, .pagination span {
            padding: 6px 10px;
            border: 1px solid rgba(31, 41, 51, 0.15);
            border-radius: 10px;
            text-decoration: none;
            color: #333;
        }
        .pagination .active {
            background: linear-gradient(180deg, #0e6d82, #0b5768);
            color: #fff;
            border-color: #0b5768;
        }
        .summary {
            margin-top: 8px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>
        <div class="brand-logo">
            <img src="../assets/nmims_logo.jpg" alt="NMIMS University Logo">
        </div>
        
        <div class="welcome-message">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h2>
            <p>Role: <?php echo ucwords(str_replace('_', ' ', $role)); ?></p>
            <?php if ($school_name): ?>
                <p>School: <?php echo htmlspecialchars($school_name); ?></p>
            <?php endif; ?>
        </div>

        <h3>Your Complaints</h3>
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
                <input type="hidden" name="role" value="<?php echo htmlspecialchars((string) ($filters['role'] ?? '')); ?>">
                <input type="hidden" name="school" value="<?php echo htmlspecialchars((string) ($filters['school'] ?? '')); ?>">
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