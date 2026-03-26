<?php
require_once '../config/database.php';
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Http\Controllers\Web\CheckStatusController;

$controller = new CheckStatusController($conn);
$state = $controller->handle($_SERVER['REQUEST_METHOD'], $_POST);

$error = $state['error'];
$complaint = $state['complaint'];
$updates = $state['updates'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Complaint Status - NMIMS Complaint Portal</title>
    <link rel="stylesheet" href="../assets/css/check-status/status.css?v=20260326a">
</head>
<body class="public-page">
    <header class="site-header">
        <div class="navbar">
            <div class="navbar-logo">
                <img src="../assets/nmims_logo.jpg" alt="NMIMS Logo">
            </div>
            <div class="admin-login">
                <a href="../admin/login.php">Admin Login</a>
            </div>
        </div>
    </header>

    <main class="site-main">
    <div class="container">
        <!-- University Logo in Center -->
        <div class="university-logo">
            <img src="../assets/nmims_logo.jpg" alt="NMIMS University">
        </div>
        
        <header>
            <h1>Check Complaint Status</h1>
            <p>Enter your complaint reference number to check the current status.</p>
        </header>

        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="reference_number">Reference Number:</label>
                <input type="text" id="reference_number" name="reference_number" placeholder="e.g., COMP-1234567890" required>
            </div>
            <div class="form-group">
                <button type="submit">Check Status</button>
            </div>
        </form>

        <?php if ($complaint): ?>
            <div class="status-result">
                <h3>Complaint Details</h3>
                <div class="detail-row">
                    <span class="detail-label">Reference Number:</span>
                    <span><?php echo htmlspecialchars($complaint['reference_number']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">School:</span>
                    <span><?php echo htmlspecialchars($complaint['school_name']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Type:</span>
                    <span><?php echo ucwords($complaint['complaint_type']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="status-badge status-<?php echo $complaint['status']; ?>">
                        <?php echo ucwords(str_replace('_', ' ', $complaint['status'])); ?>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Submitted Date:</span>
                    <span><?php echo date('d M Y H:i', strtotime($complaint['created_at'])); ?></span>
                </div>

                <?php if (!empty($updates)): ?>
                    <div class="updates-section">
                        <h3>Updates</h3>
                        <?php foreach ($updates as $update): ?>
                            <div class="update-card">
                                <div class="update-header">
                                    <span>Updated by: <?php echo htmlspecialchars($update['updated_by_name']); ?></span>
                                    <span><?php echo date('d M Y H:i', strtotime($update['updated_at'])); ?></span>
                                </div>
                                <p><?php echo nl2br(htmlspecialchars($update['update_text'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="check-status">
            <a href="../index.php" class="back-link">Back to Complaint Portal</a>
        </div>
    </div>
    </main>

    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-brand">
                <strong>NMIMS Anonymous Complaint Portal</strong>
                <span>Track your complaint with secure reference-based lookup.</span>
            </div>
            <div class="footer-links">
                <a href="../index.php">Complaint Portal</a>
                <a href="../admin/login.php">Admin Login</a>
            </div>
            <div class="footer-copy">
                <span>2026 &copy; STME. All rights reserved.</span>
            </div>
        </div>
    </footer>
</body>
</html> 