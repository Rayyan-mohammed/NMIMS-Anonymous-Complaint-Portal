<?php

namespace App\Http\Controllers\Admin;

use App\Services\ComplaintService;
use App\Support\AuthGuard;

class DashboardController
{
    private ComplaintService $complaintService;

    public function __construct(private \mysqli $conn)
    {
        $this->complaintService = new ComplaintService($conn);
    }

    public function getData(array $session, array $queryParams = []): array
    {
        $redirect = AuthGuard::requireRole($session, ['program_chair', 'deputy_registrar', 'hostel_authority', 'campus_director']);
        if ($redirect !== null) {
            return ['redirect' => $redirect];
        }

        $userId = (int) $session['user_id'];
        $role = (string) ($session['role'] ?? '');
        $schoolId = isset($session['school_id']) ? (int) $session['school_id'] : null;

        $filters = [
            'status' => (string) ($queryParams['status'] ?? ''),
            'type' => (string) ($queryParams['type'] ?? ''),
            'role' => (string) ($queryParams['role'] ?? ''),
            'school' => isset($queryParams['school']) ? (int) $queryParams['school'] : 0,
            'from_date' => $this->normalizeDate((string) ($queryParams['from_date'] ?? '')),
            'to_date' => $this->normalizeDate((string) ($queryParams['to_date'] ?? '')),
            'search' => trim((string) ($queryParams['search'] ?? '')),
        ];

        $page = max(1, (int) ($queryParams['page'] ?? 1));
        $perPageRaw = (int) ($queryParams['per_page'] ?? 10);
        $perPage = in_array($perPageRaw, [10, 25, 50], true) ? $perPageRaw : 10;
        $isCsvExport = ((string) ($queryParams['export'] ?? '')) === 'csv';

        if ($isCsvExport) {
            return [
                'redirect' => null,
                'mode' => 'csv',
                'rows' => $this->complaintService->getAssignedComplaintsCsvRows($userId, $role, $schoolId, $filters),
            ];
        }

        $pageData = $this->complaintService->getAssignedComplaintsPage($userId, $role, $schoolId, $filters, $page, $perPage);

        return [
            'redirect' => null,
            'user_id' => $userId,
            'role' => $role,
            'school_id' => $schoolId,
            'school_name' => $this->complaintService->getSchoolName($schoolId),
            'filters' => $filters,
            'schools' => $this->complaintService->getAllSchools(),
            'mode' => 'html',
            'complaints' => $pageData['rows'],
            'pagination' => [
                'page' => $pageData['page'],
                'per_page' => $pageData['per_page'],
                'total' => $pageData['total'],
                'total_pages' => $pageData['total_pages'],
            ],
        ];
    }

    private function normalizeDate(string $date): string
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return '';
        }

        [$year, $month, $day] = array_map('intval', explode('-', $date));
        if (!checkdate($month, $day, $year)) {
            return '';
        }

        return $date;
    }
}
