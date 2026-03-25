-- Final unified database for APNM6 complaint portal
-- Import this file once in MySQL to create the complete project database.

CREATE DATABASE IF NOT EXISTS apnm6_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE apnm6_db;

CREATE TABLE IF NOT EXISTS schools (
    school_id INT AUTO_INCREMENT PRIMARY KEY,
    school_code VARCHAR(20) NOT NULL UNIQUE,
    school_name VARCHAR(150) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('program_chair', 'deputy_registrar', 'campus_director', 'hostel_authority') NOT NULL,
    school_id INT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_school FOREIGN KEY (school_id) REFERENCES schools(school_id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS complaints (
    complaint_id INT AUTO_INCREMENT PRIMARY KEY,
    reference_number VARCHAR(32) NOT NULL UNIQUE,
    school_id INT NOT NULL,
    complaint_type ENUM('academic', 'hostel') NOT NULL,
    complaint_subtype VARCHAR(50) NULL,
    escalation_level ENUM('program_chair', 'deputy_registrar', 'campus_director') NOT NULL,
    complaint_details TEXT NOT NULL,
    is_anonymous TINYINT(1) NOT NULL DEFAULT 1,
    status ENUM('pending', 'in_progress', 'resolved') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_complaints_school FOREIGN KEY (school_id) REFERENCES schools(school_id)
);

CREATE TABLE IF NOT EXISTS complaint_assignments (
    assignment_id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    assigned_to INT NOT NULL,
    role ENUM('program_chair', 'deputy_registrar', 'campus_director', 'hostel_authority') NOT NULL,
    status ENUM('pending', 'in_progress', 'resolved') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_assignments_complaint FOREIGN KEY (complaint_id) REFERENCES complaints(complaint_id) ON DELETE CASCADE,
    CONSTRAINT fk_assignments_user FOREIGN KEY (assigned_to) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY uq_complaint_assignee_role (complaint_id, assigned_to, role)
);

CREATE TABLE IF NOT EXISTS complaint_updates (
    update_id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    update_text TEXT NOT NULL,
    updated_by INT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_updates_complaint FOREIGN KEY (complaint_id) REFERENCES complaints(complaint_id) ON DELETE CASCADE,
    CONSTRAINT fk_updates_user FOREIGN KEY (updated_by) REFERENCES users(user_id)
);

CREATE INDEX idx_complaints_ref ON complaints(reference_number);
CREATE INDEX idx_complaints_school ON complaints(school_id);
CREATE INDEX idx_complaints_status ON complaints(status);
CREATE INDEX idx_assignments_assignee ON complaint_assignments(assigned_to, role, status);
CREATE INDEX idx_updates_complaint ON complaint_updates(complaint_id, updated_at);

INSERT INTO schools (school_code, school_name)
SELECT 'STME', 'STME (School of Technology Management & Engineering)'
WHERE NOT EXISTS (SELECT 1 FROM schools WHERE school_code = 'STME');

INSERT INTO schools (school_code, school_name)
SELECT 'SBM', 'SBM (School of Business Management)'
WHERE NOT EXISTS (SELECT 1 FROM schools WHERE school_code = 'SBM');

INSERT INTO schools (school_code, school_name)
SELECT 'SOL', 'SOL (School of Law)'
WHERE NOT EXISTS (SELECT 1 FROM schools WHERE school_code = 'SOL');

INSERT INTO schools (school_code, school_name)
SELECT 'SPTM', 'SPTM (School of Pharmacy & Technology Management)'
WHERE NOT EXISTS (SELECT 1 FROM schools WHERE school_code = 'SPTM');

INSERT INTO users (name, email, password, role, school_id)
SELECT 'Campus Director', 'director@nmims.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'campus_director', NULL
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'director@nmims.edu');

INSERT INTO users (name, email, password, role, school_id)
SELECT 'Deputy Registrar', 'deputy.registrar@nmims.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'deputy_registrar', NULL
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'deputy.registrar@nmims.edu');

INSERT INTO users (name, email, password, role, school_id)
SELECT 'STME Program Chair', 'stme.chair@nmims.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'program_chair', s.school_id
FROM schools s
WHERE s.school_code = 'STME'
  AND NOT EXISTS (SELECT 1 FROM users WHERE email = 'stme.chair@nmims.edu');

INSERT INTO users (name, email, password, role, school_id)
SELECT 'SBM Program Chair', 'sbm.chair@nmims.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'program_chair', s.school_id
FROM schools s
WHERE s.school_code = 'SBM'
  AND NOT EXISTS (SELECT 1 FROM users WHERE email = 'sbm.chair@nmims.edu');

INSERT INTO users (name, email, password, role, school_id)
SELECT 'SOL Program Chair', 'sol.chair@nmims.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'program_chair', s.school_id
FROM schools s
WHERE s.school_code = 'SOL'
  AND NOT EXISTS (SELECT 1 FROM users WHERE email = 'sol.chair@nmims.edu');

INSERT INTO users (name, email, password, role, school_id)
SELECT 'SPTM Program Chair', 'sptm.chair@nmims.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'program_chair', s.school_id
FROM schools s
WHERE s.school_code = 'SPTM'
  AND NOT EXISTS (SELECT 1 FROM users WHERE email = 'sptm.chair@nmims.edu');

INSERT INTO users (name, email, password, role, school_id)
SELECT 'Hostel Warden', 'hostel.warden@nmims.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hostel_authority', NULL
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'hostel.warden@nmims.edu');

-- Legacy data migration from old separate databases
-- Note: campus_director and hostel_rector legacy tables had no school column,
-- so they are mapped to STME for unified reporting compatibility.

INSERT INTO complaints (
    reference_number,
    school_id,
    complaint_type,
    complaint_subtype,
    escalation_level,
    complaint_details,
    is_anonymous,
    status,
    created_at
)
SELECT
    'CAM1743931769598',
    (SELECT school_id FROM schools WHERE school_code = 'STME'),
    'academic',
    'other',
    'campus_director',
    'asdfg\r\n',
    1,
    'in_progress',
    '2025-04-06 14:59:29'
WHERE NOT EXISTS (SELECT 1 FROM complaints WHERE reference_number = 'CAM1743931769598');

INSERT INTO complaints (reference_number, school_id, complaint_type, complaint_subtype, escalation_level, complaint_details, is_anonymous, status, created_at)
SELECT 'CAM1743944321168', (SELECT school_id FROM schools WHERE school_code = 'STME'), 'academic', 'other', 'campus_director', 'lkg', 1, 'in_progress', '2025-04-06 18:28:41'
WHERE NOT EXISTS (SELECT 1 FROM complaints WHERE reference_number = 'CAM1743944321168');

INSERT INTO complaints (reference_number, school_id, complaint_type, complaint_subtype, escalation_level, complaint_details, is_anonymous, status, created_at)
SELECT 'HOS1743946598918', (SELECT school_id FROM schools WHERE school_code = 'STME'), 'hostel', 'food', 'campus_director', 'food is waste\r\n', 1, 'pending', '2025-04-06 19:06:38'
WHERE NOT EXISTS (SELECT 1 FROM complaints WHERE reference_number = 'HOS1743946598918');

INSERT INTO complaints (reference_number, school_id, complaint_type, complaint_subtype, escalation_level, complaint_details, is_anonymous, status, created_at)
SELECT 'HRM1743688521181', (SELECT school_id FROM schools WHERE school_code = 'STME'), 'hostel', 'other', 'campus_director', 'Sample complaint', 1, 'pending', '2025-04-03 15:55:21'
WHERE NOT EXISTS (SELECT 1 FROM complaints WHERE reference_number = 'HRM1743688521181');

INSERT INTO complaints (reference_number, school_id, complaint_type, complaint_subtype, escalation_level, complaint_details, is_anonymous, status, created_at)
SELECT 'SBM1743943799722', (SELECT school_id FROM schools WHERE school_code = 'SBM'), 'academic', 'other', 'program_chair', 'zxcv', 1, 'pending', '2025-04-06 18:19:59'
WHERE NOT EXISTS (SELECT 1 FROM complaints WHERE reference_number = 'SBM1743943799722');

INSERT INTO complaints (reference_number, school_id, complaint_type, complaint_subtype, escalation_level, complaint_details, is_anonymous, status, created_at)
SELECT 'SOL1743943265148', (SELECT school_id FROM schools WHERE school_code = 'SOL'), 'academic', 'other', 'deputy_registrar', 'poiuyt', 1, 'resolved', '2025-04-06 18:11:05'
WHERE NOT EXISTS (SELECT 1 FROM complaints WHERE reference_number = 'SOL1743943265148');

INSERT INTO complaints (reference_number, school_id, complaint_type, complaint_subtype, escalation_level, complaint_details, is_anonymous, status, created_at)
SELECT 'SOL1743943356397', (SELECT school_id FROM schools WHERE school_code = 'SOL'), 'academic', 'other', 'deputy_registrar', 'mnbv', 1, 'resolved', '2025-04-06 18:12:36'
WHERE NOT EXISTS (SELECT 1 FROM complaints WHERE reference_number = 'SOL1743943356397');

INSERT INTO complaints (reference_number, school_id, complaint_type, complaint_subtype, escalation_level, complaint_details, is_anonymous, status, created_at)
SELECT 'SPT1744011883926', (SELECT school_id FROM schools WHERE school_code = 'SPTM'), 'academic', 'other', 'deputy_registrar', 'asdfg', 1, 'pending', '2025-04-07 13:14:43'
WHERE NOT EXISTS (SELECT 1 FROM complaints WHERE reference_number = 'SPT1744011883926');

INSERT INTO complaints (reference_number, school_id, complaint_type, complaint_subtype, escalation_level, complaint_details, is_anonymous, status, created_at)
SELECT 'SPT1744088289124', (SELECT school_id FROM schools WHERE school_code = 'SPTM'), 'academic', 'other', 'program_chair', 'zeeess', 1, 'in_progress', '2025-04-08 10:28:09'
WHERE NOT EXISTS (SELECT 1 FROM complaints WHERE reference_number = 'SPT1744088289124');

INSERT INTO complaints (reference_number, school_id, complaint_type, complaint_subtype, escalation_level, complaint_details, is_anonymous, status, created_at)
SELECT 'STM1744010193606', (SELECT school_id FROM schools WHERE school_code = 'STME'), 'academic', 'other', 'program_chair', '/.,&#39;;', 1, 'resolved', '2025-04-07 12:46:33'
WHERE NOT EXISTS (SELECT 1 FROM complaints WHERE reference_number = 'STM1744010193606');

INSERT INTO complaints (reference_number, school_id, complaint_type, complaint_subtype, escalation_level, complaint_details, is_anonymous, status, created_at)
SELECT 'STM1744011642478', (SELECT school_id FROM schools WHERE school_code = 'STME'), 'academic', 'other', 'program_chair', 'postpone the exams now', 1, 'in_progress', '2025-04-07 13:10:42'
WHERE NOT EXISTS (SELECT 1 FROM complaints WHERE reference_number = 'STM1744011642478');

INSERT INTO complaints (reference_number, school_id, complaint_type, complaint_subtype, escalation_level, complaint_details, is_anonymous, status, created_at)
SELECT 'STM1749043474918', (SELECT school_id FROM schools WHERE school_code = 'STME'), 'academic', 'other', 'program_chair', 'hey', 1, 'in_progress', '2025-06-04 18:54:34'
WHERE NOT EXISTS (SELECT 1 FROM complaints WHERE reference_number = 'STM1749043474918');

INSERT INTO complaints (reference_number, school_id, complaint_type, complaint_subtype, escalation_level, complaint_details, is_anonymous, status, created_at)
SELECT 'STM1764621842862', (SELECT school_id FROM schools WHERE school_code = 'STME'), 'academic', 'other', 'program_chair', 'i got less ica, due to hey\r\n', 1, 'pending', '2025-12-02 02:14:02'
WHERE NOT EXISTS (SELECT 1 FROM complaints WHERE reference_number = 'STM1764621842862');

-- Assign complaints to users by legacy destination role
INSERT IGNORE INTO complaint_assignments (complaint_id, assigned_to, role, status)
SELECT c.complaint_id, u.user_id, 'campus_director', c.status
FROM complaints c
JOIN users u ON u.role = 'campus_director'
WHERE c.reference_number IN ('CAM1743931769598', 'CAM1743944321168', 'HOS1743946598918', 'HRM1743688521181');

INSERT IGNORE INTO complaint_assignments (complaint_id, assigned_to, role, status)
SELECT c.complaint_id, u.user_id, 'deputy_registrar', c.status
FROM complaints c
JOIN users u ON u.role = 'deputy_registrar'
WHERE c.reference_number IN ('SOL1743943265148', 'SOL1743943356397', 'SPT1744011883926');

INSERT IGNORE INTO complaint_assignments (complaint_id, assigned_to, role, status)
SELECT c.complaint_id, u.user_id, 'program_chair', c.status
FROM complaints c
JOIN users u ON u.role = 'program_chair' AND u.school_id = c.school_id
WHERE c.reference_number IN ('SBM1743943799722', 'SPT1744088289124', 'STM1744010193606', 'STM1744011642478', 'STM1749043474918', 'STM1764621842862');

-- Legacy comments migrated as updates (only non-empty comments)
INSERT INTO complaint_updates (complaint_id, update_text, updated_by, updated_at)
SELECT c.complaint_id, 'lets watch the match', u.user_id, '2025-04-06 18:28:41'
FROM complaints c
JOIN users u ON u.role = 'campus_director'
WHERE c.reference_number = 'CAM1743944321168'
  AND NOT EXISTS (
      SELECT 1 FROM complaint_updates cu
      WHERE cu.complaint_id = c.complaint_id AND cu.update_text = 'lets watch the match'
  );

INSERT INTO complaint_updates (complaint_id, update_text, updated_by, updated_at)
SELECT c.complaint_id, 'not correct', u.user_id, '2025-04-06 18:19:59'
FROM complaints c
JOIN users u ON u.role = 'program_chair' AND u.school_id = c.school_id
WHERE c.reference_number = 'SBM1743943799722'
  AND NOT EXISTS (
      SELECT 1 FROM complaint_updates cu
      WHERE cu.complaint_id = c.complaint_id AND cu.update_text = 'not correct'
  );

INSERT INTO complaint_updates (complaint_id, update_text, updated_by, updated_at)
SELECT c.complaint_id, 'thanks to bring this to our notice', u.user_id, '2025-04-06 18:12:36'
FROM complaints c
JOIN users u ON u.role = 'deputy_registrar'
WHERE c.reference_number = 'SOL1743943356397'
  AND NOT EXISTS (
      SELECT 1 FROM complaint_updates cu
      WHERE cu.complaint_id = c.complaint_id AND cu.update_text = 'thanks to bring this to our notice'
  );

INSERT INTO complaint_updates (complaint_id, update_text, updated_by, updated_at)
SELECT c.complaint_id, 'its okkkk', u.user_id, '2025-04-08 10:28:09'
FROM complaints c
JOIN users u ON u.role = 'program_chair' AND u.school_id = c.school_id
WHERE c.reference_number = 'SPT1744088289124'
  AND NOT EXISTS (
      SELECT 1 FROM complaint_updates cu
      WHERE cu.complaint_id = c.complaint_id AND cu.update_text = 'its okkkk'
  );

INSERT INTO complaint_updates (complaint_id, update_text, updated_by, updated_at)
SELECT c.complaint_id, 'we will look into this...', u.user_id, '2025-04-07 12:46:33'
FROM complaints c
JOIN users u ON u.role = 'program_chair' AND u.school_id = c.school_id
WHERE c.reference_number = 'STM1744010193606'
  AND NOT EXISTS (
      SELECT 1 FROM complaint_updates cu
      WHERE cu.complaint_id = c.complaint_id AND cu.update_text = 'we will look into this...'
  );

INSERT INTO complaint_updates (complaint_id, update_text, updated_by, updated_at)
SELECT c.complaint_id, 'ok lets see', u.user_id, '2025-04-07 13:10:42'
FROM complaints c
JOIN users u ON u.role = 'program_chair' AND u.school_id = c.school_id
WHERE c.reference_number = 'STM1744011642478'
  AND NOT EXISTS (
      SELECT 1 FROM complaint_updates cu
      WHERE cu.complaint_id = c.complaint_id AND cu.update_text = 'ok lets see'
  );

INSERT INTO complaint_updates (complaint_id, update_text, updated_by, updated_at)
SELECT c.complaint_id, 'we will look into this', u.user_id, '2025-06-04 18:54:34'
FROM complaints c
JOIN users u ON u.role = 'program_chair' AND u.school_id = c.school_id
WHERE c.reference_number = 'STM1749043474918'
  AND NOT EXISTS (
      SELECT 1 FROM complaint_updates cu
      WHERE cu.complaint_id = c.complaint_id AND cu.update_text = 'we will look into this'
  );
