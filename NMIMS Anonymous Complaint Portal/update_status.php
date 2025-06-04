<?php
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate required parameters
    if (!isset($_POST['table']) || !isset($_POST['reference_number']) || !isset($_POST['status']) || !isset($_POST['database'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit();
    }

    $table = $_POST['table'];
    $reference_number = $_POST['reference_number'];
    $status = $_POST['status'];
    $database = $_POST['database'];

    // Basic input validation
    if (empty($table) || empty($reference_number) || empty($status) || empty($database)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit();
    }

    $host = "localhost";
    $user = "root";
    $password = "";
    
    try {
        $conn = new mysqli($host, $user, $password, $database);

        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Prepare and execute the update
        $sql = "UPDATE $table SET status = ?, comment = ? WHERE reference_number = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $comment = isset($_POST['comment']) ? $_POST['comment'] : '';
        $stmt->bind_param("sss", $status, $comment, $reference_number);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        // Check if any rows were actually updated
        if ($stmt->affected_rows > 0) {
            // Verify the update
            $verify_sql = "SELECT status FROM $table WHERE reference_number = ?";
            $verify_stmt = $conn->prepare($verify_sql);
            $verify_stmt->bind_param("s", $reference_number);
            $verify_stmt->execute();
            $result = $verify_stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row && $row['status'] === $status) {
                echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Update verification failed']);
            }
            
            $verify_stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'No records were updated']);
        }

        $stmt->close();
        $conn->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>