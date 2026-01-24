-- ==========================================
-- DATABASE SCALING SCRIPT
-- Run this in your MySQL Database (phpMyAdmin)
-- ==========================================

-- 1. Create GUARDIANS Table (Normalization)
CREATE TABLE IF NOT EXISTS guardians (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guardian_name VARCHAR(100) NOT NULL,
    contact_number VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    relationship_to_student VARCHAR(50)
);

-- Link Students to Guardians
-- (We add the column first. Later we will update PHP to use it)
ALTER TABLE students ADD COLUMN IF NOT EXISTS guardian_id INT;
ALTER TABLE students ADD CONSTRAINT fk_student_guardian FOREIGN KEY (guardian_id) REFERENCES guardians(id);

-- 2. Create EXAMS Table (Centralized Exam Management)
CREATE TABLE IF NOT EXISTS exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_name VARCHAR(100) NOT NULL, -- e.g. "Finals 2025"
    exam_type ENUM('Monthly', 'Midterm', 'Final', 'Quiz') NOT NULL,
    start_date DATE,
    total_marks INT DEFAULT 100,
    passing_marks INT DEFAULT 40
);

-- Link Grades to Exams
ALTER TABLE grades ADD COLUMN IF NOT EXISTS exam_id INT;
ALTER TABLE grades ADD CONSTRAINT fk_grade_exam FOREIGN KEY (exam_id) REFERENCES exams(id);

-- 3. Create FINANCIAL Tables

-- School Expenses (Rent, Utilities, etc.)
CREATE TABLE IF NOT EXISTS school_expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expense_category ENUM('Utilities', 'Rent', 'Maintenance', 'Stationery', 'Other') NOT NULL,
    description TEXT,
    amount DECIMAL(10,2) NOT NULL,
    expense_date DATE NOT NULL
);

-- Teacher Salary History (Tracks every payment made)
CREATE TABLE IF NOT EXISTS salary_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL,
    bonus_or_deduction DECIMAL(10,2) DEFAULT 0,
    payment_date DATE NOT NULL,
    payment_month VARCHAR(20), -- e.g. "January 2025"
    status ENUM('Paid', 'Partial', 'Pending') DEFAULT 'Paid',
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
);

-- 4. Teacher Attendance (Daily Check-in)
CREATE TABLE IF NOT EXISTS teacher_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('Present', 'Absent', 'Late', 'Leave') NOT NULL,
    check_in_time TIME,
    check_out_time TIME,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
);