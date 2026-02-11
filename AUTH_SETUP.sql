-- Create Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'student') NOT NULL,
    related_id INT DEFAULT NULL, -- Links to teachers.id or students.id
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Default Admin User
-- Username: admin
-- Password: password123 (Hashed)
INSERT INTO users (username, password, role) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE username=username;

-- Note: You should create a script to register teachers/students and link their IDs.