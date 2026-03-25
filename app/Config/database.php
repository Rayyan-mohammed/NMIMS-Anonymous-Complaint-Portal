<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Support\Logger;

$host = \App\Support\Env::get('DB_HOST', 'localhost');
$dbname = \App\Support\Env::get('DB_NAME', 'apnm6_db');
$username = \App\Support\Env::get('DB_USER', 'root');
$password = \App\Support\Env::get('DB_PASS', '');
$charset = \App\Support\Env::get('DB_CHARSET', 'utf8mb4');

try {
    $temp_conn = new mysqli($host, $username, $password);
    if ($temp_conn->connect_error) {
        throw new Exception('Connection failed: ' . $temp_conn->connect_error);
    }

    $sql = 'CREATE DATABASE IF NOT EXISTS `' . $temp_conn->real_escape_string($dbname) . '`';
    if (!$temp_conn->query($sql)) {
        throw new Exception('Error creating database: ' . $temp_conn->error);
    }
    $temp_conn->close();

    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }

    $conn->set_charset($charset);

    $checkTables = $conn->query('SHOW TABLES');
    if ($checkTables && $checkTables->num_rows === 0) {
        $schemaPath = __DIR__ . '/schema.sql';
        $schema = file_get_contents($schemaPath);
        if ($schema === false) {
            throw new Exception('Could not read schema file: ' . $schemaPath);
        }

        $queries = array_filter(array_map('trim', explode(';', $schema)));
        foreach ($queries as $query) {
            if ($query !== '' && !$conn->query($query)) {
                throw new Exception('Error importing schema: ' . $conn->error);
            }
        }
    }

    // Backward-compatible schema evolution for existing installations.
    $migrationQueries = [
        "ALTER TABLE schools ADD COLUMN IF NOT EXISTS max_degree_year TINYINT UNSIGNED NOT NULL DEFAULT 5 AFTER school_name",
        "UPDATE schools SET max_degree_year = 4 WHERE school_code = 'STME'",
        "UPDATE schools SET max_degree_year = 2 WHERE school_code IN ('SBM', 'SOC')",
        "UPDATE schools SET max_degree_year = 5 WHERE school_code IN ('SOL', 'SPTM')",
        "ALTER TABLE complaints ADD COLUMN IF NOT EXISTS student_name VARCHAR(120) NULL AFTER school_id",
        "ALTER TABLE complaints ADD COLUMN IF NOT EXISTS sap_id VARCHAR(30) NULL AFTER student_name",
        "ALTER TABLE complaints ADD COLUMN IF NOT EXISTS study_year TINYINT UNSIGNED NULL AFTER sap_id",
        "CREATE INDEX IF NOT EXISTS idx_complaints_sap ON complaints(sap_id)",
        "CREATE TABLE IF NOT EXISTS complaint_status_history (\n         history_id INT AUTO_INCREMENT PRIMARY KEY,\n         complaint_id INT NOT NULL,\n         old_status ENUM('pending', 'in_progress', 'resolved') NOT NULL,\n         new_status ENUM('pending', 'in_progress', 'resolved') NOT NULL,\n         changed_by INT NOT NULL,\n         changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n         CONSTRAINT fk_status_history_complaint FOREIGN KEY (complaint_id) REFERENCES complaints(complaint_id) ON DELETE CASCADE,\n         CONSTRAINT fk_status_history_user FOREIGN KEY (changed_by) REFERENCES users(user_id)\n         )",
        "CREATE INDEX IF NOT EXISTS idx_status_history_complaint ON complaint_status_history(complaint_id, changed_at)",
        "INSERT INTO schools (school_code, school_name, max_degree_year)\n         SELECT 'SOC', 'SOC (School of Commerce)', 2\n         WHERE NOT EXISTS (SELECT 1 FROM schools WHERE school_code = 'SOC')",
        "INSERT INTO users (name, email, password, role, school_id)\n         SELECT 'SOC Program Chair', 'soc.chair@nmims.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'program_chair', s.school_id\n         FROM schools s\n         WHERE s.school_code = 'SOC'\n         AND NOT EXISTS (SELECT 1 FROM users WHERE email = 'soc.chair@nmims.edu')",
    ];

    foreach ($migrationQueries as $query) {
        if (!$conn->query($query)) {
            Logger::warning('Schema migration query failed', ['query' => $query, 'error' => $conn->error]);
        }
    }
} catch (Exception $e) {
    Logger::error('Database bootstrap failed', ['error' => $e->getMessage()]);
    die('Database connection failed: ' . $e->getMessage());
}
?>