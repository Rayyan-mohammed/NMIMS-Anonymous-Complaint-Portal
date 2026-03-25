<?php

namespace App\Services;

class AuthService
{
    public function __construct(private \mysqli $conn)
    {
    }

    public function authenticateAdmin(string $email, string $password): ?array
    {
        $sql = 'SELECT user_id, name, email, password, role, school_id FROM users WHERE email = ? AND is_active = 1';
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows !== 1) {
            return null;
        }

        $user = $result->fetch_assoc();
        if (!password_verify($password, $user['password'])) {
            return null;
        }

        return $user;
    }
}
