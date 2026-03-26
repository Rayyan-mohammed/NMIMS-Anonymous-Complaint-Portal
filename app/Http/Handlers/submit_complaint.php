<?php
session_start();
require_once dirname(__DIR__, 2) . '/Config/database.php';
require_once dirname(__DIR__, 2) . '/bootstrap.php';

use App\Support\Logger;
use App\Support\Csrf;
use App\Support\Validator;

header('Content-Type: application/json');

// Verify CSRF token
if (!Csrf::validateToken($_SESSION, 'complaint_submit', (string) ($_POST['csrf_token'] ?? ''))) {
    Logger::warning('Complaint submission blocked by CSRF validation', ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request token. Please refresh and try again.'
    ]);
    exit();
}

// Get form data
$school_id = Validator::intInRange($_POST['school'] ?? null, 1, 1000000);
$student_name = null;
$sap_id = null;
$study_year = null;
$complaint_type = Validator::enumValue($_POST['complaintType'] ?? null, ['academic', 'hostel']);
$complaint_subtype = $complaint_type === 'academic'
    ? ($_POST['academicSubType'] ?? null)
    : ($_POST['hostelSubType'] ?? null);
$complaint_details = Validator::text($_POST['complaintDetails'] ?? null, 10, 5000);
$is_anonymous = isset($_POST['anonymousCheck']) ? 1 : 0;
$escalation = Validator::enumValue($_POST['escalation'] ?? null, ['programChair', 'deputyRegistrar', 'campusDirector']);

if ($school_id === null) {
    echo json_encode([
        'success' => false,
        'message' => 'Please select a valid school.'
    ]);
    exit();
}

if ($complaint_type === null) {
    echo json_encode([
        'success' => false,
        'message' => 'Please select complaint type.'
    ]);
    exit();
}

if (!is_string($complaint_subtype) || trim($complaint_subtype) === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Please select a complaint issue type.'
    ]);
    exit();
}

if ($complaint_details === null) {
    echo json_encode([
        'success' => false,
        'message' => 'Complaint details must be between 10 and 5000 characters.'
    ]);
    exit();
}

if ($escalation === null) {
    echo json_encode([
        'success' => false,
        'message' => 'Please select authority to submit to.'
    ]);
    exit();
}

if ($is_anonymous === 0) {
    $student_name = Validator::text($_POST['studentName'] ?? null, 1, 120);
    $sap_id = Validator::text($_POST['sapId'] ?? null, 1, 30);
    $study_year = Validator::intInRange($_POST['studyYear'] ?? null, 1, 15);

    if ($student_name === null || $sap_id === null || $study_year === null) {
        echo json_encode([
            'success' => false,
            'message' => 'Name, SAP ID, and year are required when not anonymous.'
        ]);
        exit();
    }
}

$schoolInfoSql = "SELECT max_degree_year FROM schools WHERE school_id = ?";
$schoolStmt = $conn->prepare($schoolInfoSql);
$schoolStmt->bind_param("i", $school_id);
$schoolStmt->execute();
$schoolResult = $schoolStmt->get_result();

if ($schoolResult->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Selected school is invalid.'
    ]);
    exit();
}

$schoolRow = $schoolResult->fetch_assoc();
$max_degree_year = (int) $schoolRow['max_degree_year'];

if ($study_year !== null && ($study_year < 1 || $study_year > $max_degree_year)) {
    echo json_encode([
        'success' => false,
        'message' => 'Selected year is invalid for the selected school.'
    ]);
    exit();
}

// Generate a unique reference number
$reference_number = 'COMP-' . strtoupper(uniqid());

// Start transaction
$conn->begin_transaction();

try {
    // Insert complaint
        $escalation_level = $escalation === 'programChair' ? 'program_chair' : ($escalation === 'deputyRegistrar' ? 'deputy_registrar' : 'campus_director');

        $sql = "INSERT INTO complaints (
            reference_number,
            school_id,
            student_name,
            sap_id,
            study_year,
            complaint_type,
            complaint_subtype,
            escalation_level,
            complaint_details,
            is_anonymous,
            status,
            created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
    $stmt = $conn->prepare($sql);
        $stmt->bind_param("sississssi", $reference_number, $school_id, $student_name, $sap_id, $study_year, $complaint_type, $complaint_subtype, $escalation_level, $complaint_details, $is_anonymous);
    $stmt->execute();
    $complaint_id = $conn->insert_id;

    // Handle primary escalation based on role
    switch ($escalation) {
        case 'deputyRegistrar':
            $sql = "INSERT INTO complaint_assignments (complaint_id, assigned_to, role) 
                    SELECT ?, user_id, 'deputy_registrar' 
                    FROM users 
                    WHERE role = 'deputy_registrar'";
            $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $complaint_id);
            $stmt->execute();
            break;
            
        case 'campusDirector':
            $sql = "INSERT INTO complaint_assignments (complaint_id, assigned_to, role) 
                    SELECT ?, user_id, 'campus_director' 
                    FROM users 
                    WHERE role = 'campus_director'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $complaint_id);
            $stmt->execute();
            break;

        case 'programChair':
            $sql = "INSERT IGNORE INTO complaint_assignments (complaint_id, assigned_to, role)
                SELECT ?, user_id, 'program_chair'
                FROM users
                WHERE role = 'program_chair' AND school_id = ? AND is_active = 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $complaint_id, $school_id);
            $stmt->execute();
            break;
    }

        // Optionally add program chair recipients when the primary escalation is deputy registrar or campus director.
        if (($escalation === 'deputyRegistrar' || $escalation === 'campusDirector') && isset($_POST['programChair'])) {
        $program_chair_ids = is_array($_POST['programChair']) ? $_POST['programChair'] : [$_POST['programChair']];
        $sql = "INSERT IGNORE INTO complaint_assignments (complaint_id, assigned_to, role) VALUES (?, ?, 'program_chair')";
        $stmt = $conn->prepare($sql);

        foreach ($program_chair_ids as $program_chair_id) {
            $program_chair_id = (int) $program_chair_id;
            $stmt->bind_param("ii", $complaint_id, $program_chair_id);
            $stmt->execute();
        }
    }

    // For hostel complaints, assign selected hostel authorities,
    // or auto-assign all active hostel authorities if none are selected.
    if ($complaint_type === 'hostel') {
        if (isset($_POST['hostelAuthority']) && is_array($_POST['hostelAuthority']) && count($_POST['hostelAuthority']) > 0) {
            $sql = "INSERT IGNORE INTO complaint_assignments (complaint_id, assigned_to, role) VALUES (?, ?, 'hostel_authority')";
            $stmt = $conn->prepare($sql);

            foreach ($_POST['hostelAuthority'] as $authority_id) {
                $authority_id = (int) $authority_id;
                $stmt->bind_param("ii", $complaint_id, $authority_id);
                $stmt->execute();
            }
        } else {
            $sql = "INSERT IGNORE INTO complaint_assignments (complaint_id, assigned_to, role)
                    SELECT ?, user_id, 'hostel_authority'
                    FROM users
                    WHERE role = 'hostel_authority' AND is_active = 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $complaint_id);
            $stmt->execute();
        }
    }

    // Commit transaction
    $conn->commit();

    // Return success response
    $response = [
        'success' => true,
        'message' => 'Complaint submitted successfully',
        'reference_number' => $reference_number
    ];
    echo json_encode($response);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    Logger::error('Complaint submission failed', ['error' => $e->getMessage()]);
    
    // Return error response
    $response = [
        'success' => false,
        'message' => 'Error submitting complaint: ' . $e->getMessage()
    ];
    echo json_encode($response);
}
?> 