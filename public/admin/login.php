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
    <link rel="stylesheet" href="../assets/css/admin/login.css?v=20260325b">
    <style>
        .login-box {
            border: 1px solid rgba(14, 90, 102, 0.22);
            background: linear-gradient(180deg, rgba(255,255,255,0.94), rgba(246,249,252,0.9));
        }
        .brand-logo {
            text-align: center;
            margin-bottom: 14px;
        }
        .brand-logo img {
            max-width: 220px;
            width: 100%;
            height: auto;
            filter: drop-shadow(0 8px 18px rgba(24, 42, 62, 0.2));
        }
        h2 {
            letter-spacing: 0.3px;
            margin-bottom: 12px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="brand-logo">
                <img src="../assets/nmims_logo.jpg" alt="NMIMS University Logo">
            </div>
            <h2>Admin Login</h2>
            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <button type="submit">Login</button>
                </div>
            </form>
            <div class="back-link">
                <a href="../index.php">Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html> 