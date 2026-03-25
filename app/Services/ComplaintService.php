<?php

namespace App\Services;

class ComplaintService
{
    private const ALLOWED_STATUS = ['pending', 'in_progress', 'resolved'];
    private const ALLOWED_COMPLAINT_TYPES = ['academic', 'hostel'];
    private const ALLOWED_ASSIGNMENT_ROLES = ['program_chair', 'deputy_registrar', 'campus_director', 'hostel_authority'];

    public function __construct(private \mysqli $conn)
    {
    }

    public function getAllSchools(): array
    {
        $sql = 'SELECT school_id, school_code, school_name FROM schools ORDER BY school_name ASC';
        $result = $this->conn->query($sql);
        if ($result === false) {
            return [];
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getSchoolName(?int $schoolId): string
    {
        if (empty($schoolId)) {
            return '';
        }

        $sql = 'SELECT school_name FROM schools WHERE school_id = ?';
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $schoolId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return '';
        }

        return (string) $result->fetch_assoc()['school_name'];
    }

    public function getAssignedComplaintsPage(
        int $userId,
        string $role,
        ?int $schoolId,
        array $filters = [],
        int $page = 1,
        int $perPage = 10
    ): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $query = $this->buildAssignedComplaintsQuery($userId, $role, $schoolId, $filters);
        $countQuery = $this->buildAssignedComplaintsQuery($userId, $role, $schoolId, $filters, true);

        $countStmt = $this->conn->prepare($countQuery['sql']);
        $this->bindParams($countStmt, $countQuery['types'], $countQuery['params']);
        $countStmt->execute();
        $total = (int) ($countStmt->get_result()->fetch_assoc()['total_count'] ?? 0);

        $sql = $query['sql'] . ' ORDER BY c.created_at DESC LIMIT ? OFFSET ?';
        $types = $query['types'] . 'ii';
        $params = $query['params'];
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->conn->prepare($sql);
        $this->bindParams($stmt, $types, $params);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return [
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function getAssignedComplaintsCsvRows(int $userId, string $role, ?int $schoolId, array $filters = []): array
    {
        $query = $this->buildAssignedComplaintsQuery($userId, $role, $schoolId, $filters);
        $sql = $query['sql'] . ' ORDER BY c.created_at DESC';

        $stmt = $this->conn->prepare($sql);
        $this->bindParams($stmt, $query['types'], $query['params']);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function bindParams(\mysqli_stmt $stmt, string $types, array $params): void
    {
        $bind = [$types];
        foreach ($params as $index => $value) {
            $bind[] = &$params[$index];
        }

        call_user_func_array([$stmt, 'bind_param'], $bind);
    }

    private function buildAssignedComplaintsQuery(
        int $userId,
        string $role,
        ?int $schoolId,
        array $filters,
        bool $countOnly = false
    ): array {
        $isSchoolScopedRole = ($role === 'program_chair' && !empty($schoolId));
        $statusFilter = (string) ($filters['status'] ?? '');
        $typeFilter = (string) ($filters['type'] ?? '');
        $roleFilter = (string) ($filters['role'] ?? '');
        $schoolFilter = isset($filters['school']) ? (int) $filters['school'] : 0;
        $fromDateFilter = $this->normalizeDate((string) ($filters['from_date'] ?? ''));
        $toDateFilter = $this->normalizeDate((string) ($filters['to_date'] ?? ''));
        $searchFilter = trim((string) ($filters['search'] ?? ''));

        $select = $countOnly
            ? 'SELECT COUNT(*) AS total_count'
            : 'SELECT c.*, s.school_name, ca.status AS assignment_status, ca.role AS assignment_role';

        $sql = $select . '
                FROM complaints c
                JOIN schools s ON c.school_id = s.school_id
                JOIN complaint_assignments ca ON c.complaint_id = ca.complaint_id
                WHERE ca.assigned_to = ? AND ca.role = ?';

        $types = 'is';
        $params = [$userId, $role];

        if ($isSchoolScopedRole) {
            $sql .= ' AND c.school_id = ?';
            $types .= 'i';
            $params[] = $schoolId;
        }

        if (in_array($statusFilter, self::ALLOWED_STATUS, true)) {
            $sql .= ' AND c.status = ?';
            $types .= 's';
            $params[] = $statusFilter;
        }

        if (in_array($typeFilter, self::ALLOWED_COMPLAINT_TYPES, true)) {
            $sql .= ' AND c.complaint_type = ?';
            $types .= 's';
            $params[] = $typeFilter;
        }

        if (in_array($roleFilter, self::ALLOWED_ASSIGNMENT_ROLES, true)) {
            $sql .= ' AND ca.role = ?';
            $types .= 's';
            $params[] = $roleFilter;
        }

        if ($schoolFilter > 0) {
            $sql .= ' AND c.school_id = ?';
            $types .= 'i';
            $params[] = $schoolFilter;
        }

        if ($fromDateFilter !== null) {
            $sql .= ' AND c.created_at >= ?';
            $types .= 's';
            $params[] = $fromDateFilter . ' 00:00:00';
        }

        if ($toDateFilter !== null) {
            $sql .= ' AND c.created_at <= ?';
            $types .= 's';
            $params[] = $toDateFilter . ' 23:59:59';
        }

        if ($searchFilter !== '') {
            $sql .= ' AND (c.reference_number LIKE ? OR c.complaint_details LIKE ? OR COALESCE(c.sap_id, "") LIKE ?)';
            $search = '%' . $searchFilter . '%';
            $types .= 'sss';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        return [
            'sql' => $sql,
            'types' => $types,
            'params' => $params,
        ];
    }

    public function getComplaintForUser(int $complaintId, int $userId, string $role, ?int $schoolId): ?array
    {
        $isSchoolScopedRole = ($role === 'program_chair' && !empty($schoolId));

        $sql = 'SELECT c.*, s.school_name, ca.status AS assignment_status
                FROM complaints c
                JOIN schools s ON c.school_id = s.school_id
                JOIN complaint_assignments ca ON c.complaint_id = ca.complaint_id
                WHERE c.complaint_id = ? AND ca.assigned_to = ? AND ca.role = ?';

        if ($isSchoolScopedRole) {
            $sql .= ' AND c.school_id = ?';
        }

        $stmt = $this->conn->prepare($sql);

        if ($isSchoolScopedRole) {
            $stmt->bind_param('iisi', $complaintId, $userId, $role, $schoolId);
        } else {
            $stmt->bind_param('iis', $complaintId, $userId, $role);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return null;
        }

        return $result->fetch_assoc();
    }

    public function getComplaintUpdates(int $complaintId): array
    {
        $sql = 'SELECT u.*, us.name AS updated_by_name
                FROM complaint_updates u
                JOIN users us ON u.updated_by = us.user_id
                WHERE u.complaint_id = ?
                ORDER BY u.updated_at DESC';

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $complaintId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getComplaintStatusHistory(int $complaintId): array
    {
        $sql = 'SELECT h.*, u.name AS changed_by_name
                FROM complaint_status_history h
                JOIN users u ON h.changed_by = u.user_id
                WHERE h.complaint_id = ?
                ORDER BY h.changed_at DESC';

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $complaintId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function addUpdate(int $complaintId, int $updatedBy, string $updateText): void
    {
        $sql = 'INSERT INTO complaint_updates (complaint_id, update_text, updated_by, updated_at)
                VALUES (?, ?, ?, NOW())';
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('isi', $complaintId, $updateText, $updatedBy);
        $stmt->execute();
    }

    public function updateStatusAndAddUpdate(int $complaintId, string $status, string $updateText, int $updatedBy): void
    {
        $this->conn->begin_transaction();

        try {
            $oldStatus = 'pending';
            $oldStatusSql = 'SELECT status FROM complaints WHERE complaint_id = ?';
            $oldStatusStmt = $this->conn->prepare($oldStatusSql);
            $oldStatusStmt->bind_param('i', $complaintId);
            $oldStatusStmt->execute();
            $oldStatusResult = $oldStatusStmt->get_result();
            if ($oldStatusResult->num_rows > 0) {
                $oldStatus = (string) $oldStatusResult->fetch_assoc()['status'];
            }

            $sql = 'UPDATE complaints SET status = ? WHERE complaint_id = ?';
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('si', $status, $complaintId);
            $stmt->execute();

            $sql = 'UPDATE complaint_assignments SET status = ? WHERE complaint_id = ?';
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('si', $status, $complaintId);
            $stmt->execute();

            if ($oldStatus !== $status) {
                $historySql = 'INSERT INTO complaint_status_history (complaint_id, old_status, new_status, changed_by, changed_at)
                               VALUES (?, ?, ?, ?, NOW())';
                $historyStmt = $this->conn->prepare($historySql);
                $historyStmt->bind_param('issi', $complaintId, $oldStatus, $status, $updatedBy);
                $historyStmt->execute();
            }

            $this->addUpdate($complaintId, $updatedBy, $updateText);
            $this->conn->commit();
        } catch (\Throwable $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function getComplaintByReference(string $referenceNumber): ?array
    {
        $sql = 'SELECT c.*, s.school_name
                FROM complaints c
                JOIN schools s ON c.school_id = s.school_id
                WHERE c.reference_number = ?';
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $referenceNumber);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows !== 1) {
            return null;
        }

        return $result->fetch_assoc();
    }

    private function normalizeDate(string $date): ?string
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return null;
        }

        [$year, $month, $day] = array_map('intval', explode('-', $date));
        if (!checkdate($month, $day, $year)) {
            return null;
        }

        return $date;
    }
}
