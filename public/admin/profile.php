<?php
session_start();
require_once '../config/database.php';
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Support\AuthGuard;
use App\Support\Csrf;
use App\Services\AuthService;

$redirect = AuthGuard::requireRole($_SESSION, ['program_chair', 'deputy_registrar', 'hostel_authority', 'campus_director']);
if ($redirect !== null) {
    header('Location: ' . $redirect);
    exit();
}

$authService = new AuthService($conn);
$userId = (int) $_SESSION['user_id'];
$profile = $authService->getAdminById($userId);
if ($profile === null) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}

$summary = $authService->getAssignmentSummary($userId);
$error = '';
$success = '';

if (!empty($_SESSION['flash_success'])) {
    $success = (string) $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::validateToken($_SESSION, 'admin_profile_password_change', (string) ($_POST['csrf_token'] ?? ''))) {
        $error = 'Invalid request token. Please refresh and try again.';
    } else {
        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        if (!password_verify($currentPassword, (string) $profile['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($newPassword) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New password and confirm password do not match.';
        } elseif ($newPassword === AuthService::INITIAL_PASSWORD) {
            $error = 'Please choose a password different from the initial password.';
        } else {
            $updated = $authService->updateUserPassword($userId, $newPassword);
            if (!$updated) {
                $error = 'Could not update password. Please try again.';
            } else {
                $_SESSION['force_password_change'] = false;
                $success = 'Password updated successfully.';
                $profile = $authService->getAdminById($userId) ?? $profile;
            }
        }
    }
}

$csrfToken = Csrf::generateToken($_SESSION, 'admin_profile_password_change');
$roleLabel = ucwords(str_replace('_', ' ', (string) ($profile['role'] ?? '')));
$createdAt = (string) ($profile['created_at'] ?? '');
$createdAtLabel = $createdAt !== '' ? date('d M Y, h:i A', strtotime($createdAt)) : '-';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - NMIMS Complaint Portal</title>
    <link rel="stylesheet" href="../assets/css/admin/login.css?v=20260326f">
</head>
<body class="admin-profile-page">
    <div class="profile-wrap profile-shell">
        <header class="profile-topbar">
            <div class="profile-topbar-left">
                <div class="brand-logo profile-brand-logo">
                    <img src="../assets/nmims_logo.jpg" alt="NMIMS University Logo">
                </div>
                <div class="profile-topbar-title">
                    <h1>Admin Profile</h1>
                    <p>Manage your account details and security settings.</p>
                </div>
            </div>
            <div class="top-actions profile-topbar-actions">
                <a class="back-btn" href="dashboard.php">Back to Dashboard</a>
                <a class="logout-btn" href="logout.php">Logout</a>
            </div>
        </header>

        <div class="profile-header">
            <h2>Account Overview</h2>
            <p>Review your account information and complaint assignment performance at a glance.</p>
        </div>

        <div class="grid profile-main-grid">
            <section class="card profile-account-card">
                <h3>Account Details</h3>
                <div class="meta-grid">
                    <div class="meta-row"><span class="meta-label">Name</span><span><?php echo htmlspecialchars((string) ($profile['name'] ?? '-')); ?></span></div>
                    <div class="meta-row"><span class="meta-label">Email</span><span><?php echo htmlspecialchars((string) ($profile['email'] ?? '-')); ?></span></div>
                    <div class="meta-row"><span class="meta-label">Role</span><span><?php echo htmlspecialchars($roleLabel); ?></span></div>
                    <div class="meta-row"><span class="meta-label">School</span><span><?php echo htmlspecialchars((string) (($profile['school_name'] ?? '') !== '' ? $profile['school_name'] : 'All Schools')); ?></span></div>
                    <div class="meta-row"><span class="meta-label">Status</span><span><?php echo ((int) ($profile['is_active'] ?? 0) === 1) ? 'Active' : 'Inactive'; ?></span></div>
                    <div class="meta-row"><span class="meta-label">Account Created</span><span><?php echo htmlspecialchars($createdAtLabel); ?></span></div>
                </div>
            </section>

            <section class="card profile-summary-card">
                <h3>Complaint Assignment Summary</h3>
                <div class="stats">
                    <div class="stat"><strong><?php echo (int) $summary['total']; ?></strong>Total</div>
                    <div class="stat"><strong><?php echo (int) $summary['pending']; ?></strong>Pending</div>
                    <div class="stat"><strong><?php echo (int) $summary['in_progress']; ?></strong>In Progress</div>
                    <div class="stat"><strong><?php echo (int) $summary['resolved']; ?></strong>Resolved</div>
                </div>
            </section>
        </div>

        <section class="card password-section profile-password-card" id="password-section">
            <h3>Change Password</h3>
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required minlength="8" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <button type="submit">Update Password</button>
                </div>
            </form>
        </section>
    </div>
</body>
</html>
