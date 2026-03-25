<?php

namespace App\Support;

class Csrf
{
    public static function generateToken(array &$session, string $key): string
    {
        if (empty($session['_csrf'][$key])) {
            $session['_csrf'][$key] = bin2hex(random_bytes(32));
        }

        return $session['_csrf'][$key];
    }

    public static function validateToken(array &$session, string $key, ?string $token): bool
    {
        $sessionToken = $session['_csrf'][$key] ?? null;
        if (!is_string($sessionToken) || !is_string($token) || $token === '') {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }
}
