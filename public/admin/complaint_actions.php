<?php
session_start();
require_once '../config/database.php';
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Http\Controllers\Admin\ComplaintController;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.',
    ]);
    exit();
}

$controller = new ComplaintController($conn);
$complaintId = (int) ($_POST['complaint_id'] ?? 0);
$action = (string) ($_POST['action'] ?? '');
$updateText = trim((string) ($_POST['update_text'] ?? ''));

switch ($action) {
    case 'mark_in_progress':
        $result = $controller->handleStatusUpdateAction($_SESSION, $complaintId, [
            'csrf_token' => $_POST['csrf_token'] ?? '',
            'status' => 'in_progress',
            'update_text' => $updateText,
        ]);
        break;

    case 'resolve':
        $result = $controller->handleStatusUpdateAction($_SESSION, $complaintId, [
            'csrf_token' => $_POST['csrf_token'] ?? '',
            'status' => 'resolved',
            'update_text' => $updateText,
        ]);
        break;

    case 'add_update':
        $result = $controller->handleAddUpdateAction($_SESSION, [
            'csrf_token' => $_POST['csrf_token'] ?? '',
            'complaint_id' => $complaintId,
            'update_text' => $updateText,
        ]);
        break;

    default:
        $result = [
            'success' => false,
            'message' => 'Invalid action requested.',
            'redirect' => 'dashboard.php',
        ];
        break;
}

if (!($result['success'] ?? false)) {
    http_response_code(422);
}

echo json_encode($result);
exit();
