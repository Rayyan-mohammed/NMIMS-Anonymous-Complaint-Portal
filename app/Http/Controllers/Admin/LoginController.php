<?php

namespace App\Http\Controllers\Admin;

use App\Support\Csrf;
use App\Support\Logger;
use App\Services\AuthService;

class LoginController
{
    private AuthService $authService;

    public function __construct(private \mysqli $conn)
    {
        $this->authService = new AuthService($conn);
    }

    public function handle(string $requestMethod, array $postData, array &$session): array
    {
        if (isset($session['user_id'])) {
            if (!empty($session['force_password_change'])) {
                return ['redirect' => 'change_password.php', 'error' => ''];
            }

            return ['redirect' => 'dashboard.php', 'error' => ''];
        }

        if ($requestMethod !== 'POST') {
            return ['redirect' => null, 'error' => ''];
        }

        if (!Csrf::validateToken($session, 'admin_login', (string) ($postData['csrf_token'] ?? ''))) {
            return ['redirect' => null, 'error' => 'Invalid request token. Please refresh and try again.'];
        }

        $failedAttempts = (int) ($session['login_failed_attempts'] ?? 0);
        $lockUntil = (int) ($session['login_lock_until'] ?? 0);
        if ($lockUntil > time()) {
            $remaining = $lockUntil - time();
            return ['redirect' => null, 'error' => 'Too many failed attempts. Try again in ' . $remaining . ' seconds.'];
        }

        $email = trim((string) ($postData['email'] ?? ''));
        $password = (string) ($postData['password'] ?? '');

        $user = $this->authService->authenticateAdmin($email, $password);
        if ($user === null) {
            $failedAttempts++;
            $session['login_failed_attempts'] = $failedAttempts;

            if ($failedAttempts >= 5) {
                $session['login_lock_until'] = time() + 300;
                Logger::warning('Admin login locked due to failed attempts', ['email' => $email]);
                return ['redirect' => null, 'error' => 'Too many failed attempts. Account temporarily locked for 5 minutes.'];
            }

            return ['redirect' => null, 'error' => 'Invalid email or password'];
        }

        $session['login_failed_attempts'] = 0;
        $session['login_lock_until'] = 0;
        session_regenerate_id(true);

        $session['user_id'] = $user['user_id'];
        $session['name'] = $user['name'];
        $session['email'] = $user['email'];
        $session['role'] = $user['role'];
        $session['school_id'] = $user['school_id'];
        $session['force_password_change'] = $this->authService->isUsingInitialPassword($user);

        if (!empty($session['force_password_change'])) {
            return ['redirect' => 'change_password.php', 'error' => ''];
        }

        return ['redirect' => 'dashboard.php', 'error' => ''];
    }
}
