<?php
header('Content-Type: application/json');

$host = "localhost";
$user = "root";
$password = "";
$databases = ["campus_director", "stme", "sbm", "sol", "sptm"];
$tables = ["campus_director_complaints", "program_chair_complaints", "deputy_registrar_complaints"];

$statusCounts = [
    'Pending' => 0,
    'In Progress' => 0,
    'Resolved' => 0
];

$schoolCounts = [
    'Campus Director' => 0,
    'STME' => 0,
    'SBM' => 0,
    'SOL' => 0,
    'SPTM' => 0
];

foreach ($databases as $database) {
    $conn = new mysqli($host, $user, $password, $database);
    if ($conn->connect_error) continue;

    foreach ($tables as $table) {
        // Check if table exists
        $tableExists = $conn->query("SHOW TABLES LIKE '$table'");
        if ($tableExists->num_rows == 0) continue;

        // Get status counts
        $sql = "SELECT status, COUNT(*) as count FROM $table GROUP BY status";
        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                if (isset($statusCounts[$row['status']])) {
                    $statusCounts[$row['status']] += $row['count'];
                }
            }
        }

        // Get total complaints per school
        $totalSql = "SELECT COUNT(*) as total FROM $table";
        $totalResult = $conn->query($totalSql);
        if ($totalResult) {
            $total = $totalResult->fetch_assoc()['total'];
            $schoolName = $database === 'campus_director' ? 'Campus Director' : strtoupper($database);
            $schoolCounts[$schoolName] += $total;
        }
    }
    $conn->close();
}

echo json_encode([
    'statusCounts' => array_values($statusCounts),
    'schools' => array_keys($schoolCounts),
    'schoolCounts' => array_values($schoolCounts)
]);
?> 