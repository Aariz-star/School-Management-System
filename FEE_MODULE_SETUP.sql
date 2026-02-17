-- Fee Management System Tables

CREATE TABLE IF NOT EXISTS fee_invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    title VARCHAR(100) NOT NULL, -- e.g. "Tuition Fee - Oct 2023"
    amount DECIMAL(10,2) NOT NULL,
    due_date DATE NOT NULL,
    status ENUM('Unpaid', 'Partially Paid', 'Paid', 'Overdue') DEFAULT 'Unpaid',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS fee_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    method ENUM('Cash', 'Bank Transfer') NOT NULL,
    reference_no VARCHAR(100), -- Transaction ID for online
    proof_image VARCHAR(255), -- Path to screenshot
    status ENUM('Pending', 'Verified', 'Rejected') DEFAULT 'Pending',
    payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    collected_by INT, -- User ID of admin/staff who verified/collected
    FOREIGN KEY (invoice_id) REFERENCES fee_invoices(id) ON DELETE CASCADE
);