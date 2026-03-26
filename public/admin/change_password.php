<?php
session_start();
require_once '../config/database.php';
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Support\AuthGuard;
use App\Support\Csrf;
use App\Services\AuthService;

$redirect = AuthGuard::requireLogin($_SESSION, 'login.php');
if ($redirect !== null) {
    header('Location: ' . $redirect);
    exit();
}

if (empty($_SESSION['force_password_change'])) {
    header('Location: profile.php');
    exit();
}

$authService = new AuthService($conn);
$currentUser = $authService->getAdminById((int) $_SESSION['user_id']);
if ($currentUser === null) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::validateToken($_SESSION, 'admin_force_password_change', (string) ($_POST['csrf_token'] ?? ''))) {
        $error = 'Invalid request token. Please refresh and try again.';
    } else {
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        if (strlen($newPassword) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New password and confirm password do not match.';
        } elseif ($newPassword === AuthService::INITIAL_PASSWORD) {
            $error = 'Please choose a password different from the initial password.';
        } else {
            $updated = $authService->updateUserPassword((int) $_SESSION['user_id'], $newPassword);
            if (!$updated) {
                $error = 'Could not update password. Please try again.';
            } else {
                $_SESSION['force_password_change'] = false;
                $_SESSION['flash_success'] = 'Password updated successfully.';
                header('Location: dashboard.php');
                exit();
            }
        }
    }
}

$csrfToken = Csrf::generateToken($_SESSION, 'admin_force_password_change');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - NMIMS Complaint Portal</title>
    <link rel="stylesheet" href="../assets/css/admin/login.css?v=20260326a">
</head>
<body>
    <div class="password-wrap">
        <h1>Change Password</h1>
        <p class="subtitle">Please set a new password before continuing to your dashboard.</p>

        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

            <div class="form-group">
                <label for="email">NMIMS College Email</label>
                <input type="email" id="email" value="<?php echo htmlspecialchars((string) ($currentUser['email'] ?? '')); ?>" readonly>
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
    </div>
</body>
</html>
