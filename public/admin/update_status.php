<?php
session_start();
require_once '../config/database.php';
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Support\Csrf;
use App\Http\Controllers\Admin\ComplaintController;

$csrfToken = Csrf::generateToken($_SESSION, 'admin_actions');

$complaint_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$controller = new ComplaintController($conn);
$pageData = $controller->getComplaintPageData($_SESSION, $complaint_id);

if (($pageData['redirect'] ?? null) !== null) {
    header('Location: ' . $pageData['redirect']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirect = $controller->handleStatusUpdate($_SESSION, $complaint_id, $_POST);
    header('Location: ' . $redirect);
    exit();
}

$complaint = $pageData['complaint'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Status - NMIMS Complaint Portal</title>
    <link rel="stylesheet" href="../assets/css/admin/login.css?v=20260326a">
</head>
<body>
    <div class="update-container">
        <div class="brand-logo">
            <img src="../assets/nmims_logo.jpg" alt="NMIMS University Logo">
        </div>
        <a href="view_complaint.php?id=<?php echo $complaint_id; ?>" class="back-btn">← Back to Complaint</a>
        
        <div class="complaint-info">
            <h3>Complaint Information</h3>
            <p><strong>Reference Number:</strong> <?php echo htmlspecialchars($complaint['reference_number']); ?></p>
            <p><strong>School:</strong> <?php echo htmlspecialchars($complaint['school_name']); ?></p>
            <p><strong>Current Status:</strong> <?php echo ucwords($complaint['status']); ?></p>
            <p><strong>Identity:</strong> <?php echo ((int) ($complaint['is_anonymous'] ?? 1) === 1) ? 'Anonymous' : 'Shared with admin'; ?></p>
            <?php if ((int) ($complaint['is_anonymous'] ?? 1) === 0): ?>
                <p><strong>Student Name:</strong> <?php echo htmlspecialchars((string) ($complaint['student_name'] ?? '-')); ?></p>
                <p><strong>SAP ID:</strong> <?php echo htmlspecialchars((string) ($complaint['sap_id'] ?? '-')); ?></p>
                <p><strong>Year:</strong> <?php echo htmlspecialchars((string) ($complaint['study_year'] ?? '-')); ?></p>
            <?php endif; ?>
        </div>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <div class="form-group">
                <label for="status">New Status:</label>
                <select id="status" name="status" required>
                    <option value="pending" <?php echo $complaint['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="in_progress" <?php echo $complaint['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="resolved" <?php echo $complaint['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                </select>
            </div>

            <div class="form-group">
                <label for="update_text">Update Details:</label>
                <textarea id="update_text" name="update_text" rows="4" required placeholder="Please provide details about the status update..."></textarea>
            </div>

            <button type="submit" class="submit-btn">Update Status</button>
        </form>
    </div>
</body>
</html>