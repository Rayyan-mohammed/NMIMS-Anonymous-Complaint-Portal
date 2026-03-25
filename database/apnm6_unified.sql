-- APNM6 unified database schema (structure only)
-- No seed/dummy data is included in this file.

CREATE DATABASE IF NOT EXISTS apnm6_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE apnm6_db;

CREATE TABLE IF NOT EXISTS schools (
    school_id INT AUTO_INCREMENT PRIMARY KEY,
    school_code VARCHAR(20) NOT NULL UNIQUE,
    school_name VARCHAR(150) NOT NULL,
    max_degree_year TINYINT UNSIGNED NOT NULL,
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
    CONSTRAINT fk_users_school
        FOREIGN KEY (school_id)
        REFERENCES schools(school_id)
        ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS complaints (
    complaint_id INT AUTO_INCREMENT PRIMARY KEY,
    reference_number VARCHAR(32) NOT NULL UNIQUE,
    school_id INT NOT NULL,
    student_name VARCHAR(120) NULL,
    sap_id VARCHAR(30) NULL,
    study_year TINYINT UNSIGNED NULL,
    complaint_type ENUM('academic', 'hostel') NOT NULL,
    complaint_subtype VARCHAR(50) NULL,
    escalation_level ENUM('program_chair', 'deputy_registrar', 'campus_director') NOT NULL,
    complaint_details TEXT NOT NULL,
    is_anonymous TINYINT(1) NOT NULL DEFAULT 1,
    status ENUM('pending', 'in_progress', 'resolved') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_complaints_school
        FOREIGN KEY (school_id)
        REFERENCES schools(school_id)
);

CREATE TABLE IF NOT EXISTS complaint_assignments (
    assignment_id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    assigned_to INT NOT NULL,
    role ENUM('program_chair', 'deputy_registrar', 'campus_director', 'hostel_authority') NOT NULL,
    status ENUM('pending', 'in_progress', 'resolved') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_assignments_complaint
        FOREIGN KEY (complaint_id)
        REFERENCES complaints(complaint_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_assignments_user
        FOREIGN KEY (assigned_to)
        REFERENCES users(user_id)
        ON DELETE CASCADE,
    UNIQUE KEY uq_complaint_assignee_role (complaint_id, assigned_to, role)
);

CREATE TABLE IF NOT EXISTS complaint_updates (
    update_id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    update_text TEXT NOT NULL,
    updated_by INT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_updates_complaint
        FOREIGN KEY (complaint_id)
        REFERENCES complaints(complaint_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_updates_user
        FOREIGN KEY (updated_by)
        REFERENCES users(user_id)
);

CREATE TABLE IF NOT EXISTS complaint_status_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    old_status ENUM('pending', 'in_progress', 'resolved') NOT NULL,
    new_status ENUM('pending', 'in_progress', 'resolved') NOT NULL,
    changed_by INT NOT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_status_history_complaint
        FOREIGN KEY (complaint_id)
        REFERENCES complaints(complaint_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_status_history_user
        FOREIGN KEY (changed_by)
        REFERENCES users(user_id)
);

CREATE INDEX idx_complaints_ref ON complaints(reference_number);
CREATE INDEX idx_complaints_sap ON complaints(sap_id);
CREATE INDEX idx_complaints_school ON complaints(school_id);
CREATE INDEX idx_complaints_status ON complaints(status);
CREATE INDEX idx_assignments_assignee ON complaint_assignments(assigned_to, role, status);
CREATE INDEX idx_updates_complaint ON complaint_updates(complaint_id, updated_at);
CREATE INDEX idx_status_history_complaint ON complaint_status_history(complaint_id, changed_at);

-- Essential master data (only required receiver accounts)
INSERT INTO schools (school_code, school_name, max_degree_year)
SELECT 'STME', 'STME (School of Technology Management & Engineering)', 4
WHERE NOT EXISTS (SELECT 1 FROM schools WHERE school_code = 'STME');

INSERT INTO schools (school_code, school_name, max_degree_year)
SELECT 'SBM', 'SBM (School of Business Management)', 2
WHERE NOT EXISTS (SELECT 1 FROM schools WHERE school_code = 'SBM');

INSERT INTO schools (school_code, school_name, max_degree_year)
SELECT 'SOL', 'SOL (School of Law)', 5
WHERE NOT EXISTS (SELECT 1 FROM schools WHERE school_code = 'SOL');

INSERT INTO schools (school_code, school_name, max_degree_year)
SELECT 'SPTM', 'SPTM (School of Pharmacy & Technology Management)', 5
WHERE NOT EXISTS (SELECT 1 FROM schools WHERE school_code = 'SPTM');

INSERT INTO schools (school_code, school_name, max_degree_year)
SELECT 'SOC', 'SOC (School of Commerce)', 2
WHERE NOT EXISTS (SELECT 1 FROM schools WHERE school_code = 'SOC');

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
SELECT 'SOC Program Chair', 'soc.chair@nmims.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'program_chair', s.school_id
FROM schools s
WHERE s.school_code = 'SOC'
    AND NOT EXISTS (SELECT 1 FROM users WHERE email = 'soc.chair@nmims.edu');

INSERT INTO users (name, email, password, role, school_id)
SELECT 'Hostel Warden', 'hostel.warden@nmims.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hostel_authority', NULL
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'hostel.warden@nmims.edu');
