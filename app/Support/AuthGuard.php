<?php

namespace App\Support;

class AuthGuard
{
    public static function requireLogin(array $session, string $redirectTo = 'login.php'): ?string
    {
        if (!isset($session['user_id'])) {
            return $redirectTo;
        }

        return null;
    }

    /**
     * @param array<int, string> $allowedRoles
     */
    public static function requireRole(array $session, array $allowedRoles, string $redirectTo = 'login.php'): ?string
    {
        $loginRedirect = self::requireLogin($session, $redirectTo);
        if ($loginRedirect !== null) {
            return $loginRedirect;
        }

        $role = (string) ($session['role'] ?? '');
        if (!in_array($role, $allowedRoles, true)) {
            return $redirectTo;
        }

        return null;
    }
}