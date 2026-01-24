-- SQL Commands to Set Up Teacher Management Module
-- Run these commands in your MySQL database

-- 1. Add new columns to teachers table
ALTER TABLE teachers ADD COLUMN (
    father_name VARCHAR(100) DEFAULT '',
    salary DECIMAL(10, 2) DEFAULT 0,
    phone VARCHAR(20) DEFAULT '',
    email VARCHAR(100) DEFAULT '',
    remaining_payment DECIMAL(10, 2) DEFAULT 0
);

-- 2. Create junction table for multiple subjects per teacher
CREATE TABLE IF NOT EXISTS teacher_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    UNIQUE KEY unique_teacher_subject (teacher_id, subject_id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- 3. Verify the schema changes
-- Check teachers table structure
DESCRIBE teachers;

-- Check teacher_subjects table
DESCRIBE teacher_subjects;

-- 4. (Optional) Check if subjects table exists and has data
SELECT * FROM subjects;

-- 5. (Optional) View existing teachers
SELECT id, name, father_name, salary, phone, email, remaining_payment FROM teachers;
