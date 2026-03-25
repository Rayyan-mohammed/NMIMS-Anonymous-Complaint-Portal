<?php

namespace App\Http\Controllers\Web;

use App\Services\ComplaintService;

class CheckStatusController
{
    private ComplaintService $complaintService;

    public function __construct(private \mysqli $conn)
    {
        $this->complaintService = new ComplaintService($conn);
    }

    public function handle(string $requestMethod, array $postData): array
    {
        $state = [
            'error' => '',
            'complaint' => null,
            'updates' => [],
        ];

        if ($requestMethod !== 'POST') {
            return $state;
        }

        $referenceNumber = trim((string) ($postData['reference_number'] ?? ''));
        if ($referenceNumber === '') {
            $state['error'] = 'Please enter a valid reference number.';
            return $state;
        }

        $complaint = $this->complaintService->getComplaintByReference($referenceNumber);
        if ($complaint === null) {
            $state['error'] = 'Invalid reference number. Please check and try again.';
            return $state;
        }

        $state['complaint'] = $complaint;
        $state['updates'] = $this->complaintService->getComplaintUpdates((int) $complaint['complaint_id']);
        return $state;
    }
}
