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
    <link rel="stylesheet" href="../assets/css/admin/login.css?v=20260325b">
    <style>
        .update-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid rgba(14, 90, 102, 0.22);
            background: linear-gradient(180deg, rgba(255,255,255,0.94), rgba(247,250,253,0.9));
        }
        .complaint-info {
            background: rgba(14, 90, 102, 0.06);
            border: 1px solid rgba(14, 90, 102, 0.15);
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid rgba(31, 41, 51, 0.15);
            border-radius: 10px;
        }
        .back-btn {
            display: inline-block;
            padding: 8px 16px;
            background: linear-gradient(180deg, #0e6d82, #0b5768);
            color: white;
            text-decoration: none;
            border-radius: 999px;
            margin-bottom: 20px;
        }
        .submit-btn {
            padding: 10px 20px;
            background: linear-gradient(180deg, #2f9b63, #1f7d4d);
            color: white;
            border: none;
            border-radius: 999px;
            cursor: pointer;
            font-weight: 700;
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
    </style>
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