<?php
session_start();
require_once '../config/database.php';
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Support\Csrf;
use App\Http\Controllers\Admin\LoginController;

$csrfToken = Csrf::generateToken($_SESSION, 'admin_login');

$controller = new LoginController($conn);
$loginResult = $controller->handle($_SERVER['REQUEST_METHOD'], $_POST, $_SESSION);

if (($loginResult['redirect'] ?? null) !== null) {
    header('Location: ' . $loginResult['redirect']);
    exit();
}

$error = $loginResult['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - NMIMS Complaint Portal</title>
    <link rel="stylesheet" href="../assets/css/admin/login.css?v=20260326c">
</head>
<body class="admin-login-page">
    <div class="login-container">
        <div class="login-box admin-login-shell">
            <section class="login-panel login-panel-brand" aria-label="Portal intro">
                <div class="brand-logo admin-login-brand-logo">
                    <img src="../assets/nmims_logo.jpg" alt="NMIMS University Logo">
                </div>
                <span class="login-eyebrow">Authority Portal</span>
                <h1>NMIMS Complaint Management</h1>
                <p>Sign in to review assigned complaints, update progress, and resolve student concerns through the official workflow.</p>
                <ul class="login-feature-list">
                    <li>Role-based complaint dashboard</li>
                    <li>Escalation-aware workflow updates</li>
                    <li>Secure session and audit-friendly access</li>
                </ul>
            </section>

            <section class="login-panel login-panel-form" aria-labelledby="adminLoginHeading">
                <h2 id="adminLoginHeading">Admin Login</h2>
                <p class="login-subtext">Use your authorized email and password to continue.</p>

                <?php if ($error): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="" class="admin-login-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" autocomplete="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" autocomplete="current-password" required>
                    </div>
                    <div class="form-group">
                        <button type="submit">Login</button>
                    </div>
                </form>

                <div class="back-link">
                    <a href="../index.php">Back to Home</a>
                </div>
            </section>
        </div>
    </div>
</body>
</html> 