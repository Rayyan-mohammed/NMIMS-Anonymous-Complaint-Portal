<?php

namespace App\Services;

class AuthService
{
    public const INITIAL_PASSWORD = 'password';

    public function __construct(private \mysqli $conn)
    {
    }

    public function authenticateAdmin(string $email, string $password): ?array
    {
        $sql = 'SELECT user_id, name, email, password, role, school_id, created_at FROM users WHERE email = ? AND is_active = 1';
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

    public function isUsingInitialPassword(array $user): bool
    {
        $hash = (string) ($user['password'] ?? '');
        if ($hash === '') {
            return false;
        }

        return password_verify(self::INITIAL_PASSWORD, $hash);
    }

    public function getAdminById(int $userId): ?array
    {
        $sql = "SELECT u.user_id, u.name, u.email, u.password, u.role, u.school_id, u.is_active, u.created_at, s.school_name
                FROM users u
                LEFT JOIN schools s ON s.school_id = u.school_id
                WHERE u.user_id = ? AND u.is_active = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows !== 1) {
            return null;
        }

        return $result->fetch_assoc();
    }

    public function updateUserPassword(int $userId, string $newPassword): bool
    {
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = 'UPDATE users SET password = ? WHERE user_id = ? AND is_active = 1';
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('si', $newHash, $userId);
        $stmt->execute();

        return $stmt->affected_rows === 1;
    }

    public function getAssignmentSummary(int $userId): array
    {
        $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress,
                    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) AS resolved
                FROM complaint_assignments
                WHERE assigned_to = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc() ?: [];

        return [
            'total' => (int) ($row['total'] ?? 0),
            'pending' => (int) ($row['pending'] ?? 0),
            'in_progress' => (int) ($row['in_progress'] ?? 0),
            'resolved' => (int) ($row['resolved'] ?? 0),
        ];
    }
}
