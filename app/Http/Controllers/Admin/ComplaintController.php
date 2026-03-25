<?php

namespace App\Http\Controllers\Admin;

use App\Support\AuthGuard;
use App\Support\Csrf;
use App\Support\Validator;
use App\Services\ComplaintService;

class ComplaintController
{
    private ComplaintService $complaintService;

    public function __construct(private \mysqli $conn)
    {
        $this->complaintService = new ComplaintService($conn);
    }

    public function getComplaintPageData(array $session, ?int $complaintId): array
    {
        $redirect = AuthGuard::requireRole($session, ['program_chair', 'deputy_registrar', 'hostel_authority', 'campus_director']);
        if ($redirect !== null) {
            return ['redirect' => $redirect];
        }

        if (empty($complaintId)) {
            return ['redirect' => 'dashboard.php'];
        }

        $userId = (int) $session['user_id'];
        $role = (string) ($session['role'] ?? '');
        $schoolId = isset($session['school_id']) ? (int) $session['school_id'] : null;

        $complaint = $this->complaintService->getComplaintForUser($complaintId, $userId, $role, $schoolId);
        if ($complaint === null) {
            return ['redirect' => 'dashboard.php'];
        }

        return [
            'redirect' => null,
            'complaint' => $complaint,
            'updates' => $this->complaintService->getComplaintUpdates($complaintId),
            'status_history' => $this->complaintService->getComplaintStatusHistory($complaintId),
        ];
    }

    public function handleStatusUpdate(array $session, int $complaintId, array $postData): string
    {
        $result = $this->handleStatusUpdateAction($session, $complaintId, $postData);
        if (!$result['success']) {
            return 'dashboard.php';
        }

        return 'view_complaint.php?id=' . $complaintId;
    }

    public function handleStatusUpdateAction(array $session, int $complaintId, array $postData): array
    {
        $redirect = AuthGuard::requireRole($session, ['program_chair', 'deputy_registrar', 'hostel_authority', 'campus_director']);
        if ($redirect !== null) {
            return [
                'success' => false,
                'message' => 'Unauthorized request.',
                'redirect' => $redirect,
            ];
        }

        if (!Csrf::validateToken($session, 'admin_actions', (string) ($postData['csrf_token'] ?? ''))) {
            return [
                'success' => false,
                'message' => 'Invalid security token.',
                'redirect' => 'dashboard.php',
            ];
        }

        $status = Validator::enumValue($postData['status'] ?? null, ['pending', 'in_progress', 'resolved'], 'pending');
        $providedText = trim((string) ($postData['update_text'] ?? ''));
        $defaultText = 'Status updated to ' . str_replace('_', ' ', $status) . '.';
        $updateText = Validator::text($providedText === '' ? $defaultText : $providedText, 2, 2000);
        $userId = (int) $session['user_id'];

        if ($complaintId <= 0 || $updateText === null) {
            return [
                'success' => false,
                'message' => 'Invalid complaint status request.',
                'redirect' => 'dashboard.php',
            ];
        }

        $pageData = $this->getComplaintPageData($session, $complaintId);
        if (($pageData['redirect'] ?? null) !== null) {
            return [
                'success' => false,
                'message' => 'Complaint not found or not accessible.',
                'redirect' => (string) $pageData['redirect'],
            ];
        }

        $this->complaintService->updateStatusAndAddUpdate($complaintId, $status, $updateText, $userId);
        return [
            'success' => true,
            'message' => 'Complaint status updated successfully.',
            'status' => $status,
            'redirect' => 'view_complaint.php?id=' . $complaintId,
        ];
    }

    public function handleAddUpdate(array $session, array $postData): string
    {
        $result = $this->handleAddUpdateAction($session, $postData);
        if (!$result['success']) {
            return 'dashboard.php';
        }

        return (string) $result['redirect'];
    }

    public function handleAddUpdateAction(array $session, array $postData): array
    {
        $redirect = AuthGuard::requireRole($session, ['program_chair', 'deputy_registrar', 'hostel_authority', 'campus_director']);
        if ($redirect !== null) {
            return [
                'success' => false,
                'message' => 'Unauthorized request.',
                'redirect' => $redirect,
            ];
        }

        if (!Csrf::validateToken($session, 'admin_actions', (string) ($postData['csrf_token'] ?? ''))) {
            return [
                'success' => false,
                'message' => 'Invalid security token.',
                'redirect' => 'dashboard.php',
            ];
        }

        $complaintId = (int) ($postData['complaint_id'] ?? 0);
        $updateText = trim((string) ($postData['update_text'] ?? ''));

        if ($complaintId <= 0 || $updateText === '') {
            return [
                'success' => false,
                'message' => 'Complaint update cannot be empty.',
                'redirect' => 'dashboard.php',
            ];
        }

        $pageData = $this->getComplaintPageData($session, $complaintId);
        if (($pageData['redirect'] ?? null) !== null) {
            return [
                'success' => false,
                'message' => 'Complaint not found or not accessible.',
                'redirect' => (string) $pageData['redirect'],
            ];
        }

        $this->complaintService->addUpdate($complaintId, (int) $session['user_id'], $updateText);
        return [
            'success' => true,
            'message' => 'Update added successfully.',
            'redirect' => 'view_complaint.php?id=' . $complaintId,
        ];
    }
}
