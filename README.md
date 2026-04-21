# 🎓 School Management System

A comprehensive, web-based application designed to streamline the administrative and academic operations of a school. It provides dedicated portals for administrators, teachers, and students, ensuring efficient management of school-related activities from student enrollment to financial tracking.

## ✨ Core Features

- **Role-Based Access Control:** Secure, dedicated portals for Administrators, Teachers, and Students with distinct functionalities.
- **Interactive Admin Dashboard:** A central hub for admins with key metrics, data visualizations, and quick access to all management modules.
- **Complete Student Lifecycle Management:** From registration and attendance tracking to grade management and class promotion.
- **Comprehensive Financial Management:** Streamlined fee invoice generation, online and cash payment collection, expense tracking, and teacher salary management.
- **Academic & User Management:** Easy creation and assignment of classes, subjects, and teachers, plus secure user account management.
- **Robust Security:** Built with security best practices including password hashing, prepared statements, and brute-force protection.

---

## 🚀 Technology Stack

- **Backend:** PHP
- **Database:** MySQL / MariaDB
- **Frontend:** HTML, CSS, JavaScript
- **Charting Library:** Chart.js for data visualization

---

## 🗂️ System Modules & Functionality

The system is primarily divided into three user roles:

### 👨‍💼 Admin Portal

The administrator has full control over the entire system. Key functionalities include:

#### Dashboard & Analytics
- **Overview:** View real-time statistics for total students, active teachers, classes, and daily attendance percentage.
- **Attendance Trends:** Visualize student attendance over the last 7 days with a line chart.
- **Performance Metrics:** See an overview of student grade distribution (Excellent, Good, Average, Fail) in a doughnut chart.

#### Student Management
- **Student Registration:** Add new students with detailed information, including guardian details.
- **View Student Lists:** Access a complete directory of all students.
- **Class Promotion:** Promote all students from one class to another at the end of an academic session.

#### Teacher & Staff Management
- **Add Teachers:** Register new teachers with their personal details, salary, and subject specializations.
- **Teachers Directory:** View a list of all teachers, their contact information, and assigned subjects.
- **Teacher Assignments:**
    - Assign teachers to specific subjects within a class.
    - Designate a "Class Master" responsible for a class's attendance.

#### Academic Management
- **Class & Subject Management:**
    - Create, edit, and delete classes (e.g., "Play Group", "1st Grade").
    - Create, edit, and delete subjects (e.g., "Physics", "Computer Science").
    - Assign subjects to classes and specify optional book names.
- **Attendance:** Mark or update daily attendance for any class.
- **Grades:** Enter, update, and view student grades by class, subject, and term (e.g., "Midterm").

#### Financial Management
- **Fee Management:**
    - **Generate Invoices:** Create bulk fee invoices for an entire class with a specific title, amount, and due date.
    - **View & Edit Invoices:** Search, view, and edit invoices on a bulk or individual student basis.
    - **Verify Payments:** Approve or reject online payments submitted by students/parents, with access to transaction proof.
    - **Cash Collection:** Record fee payments made in cash.
- **Expense Tracking:** (Database Ready) Record various school expenses like utilities, rent, and maintenance.
- **Salary Payments:** (Database Ready) Manage and record salary payments to teachers.

#### User & Security Management
- **Create User Accounts:** Generate login credentials for new students and teachers.
- **Password Manager:** Securely search for a user and reset their password.
- **Account Activation:** Deactivate or reactivate user accounts.

### 👩‍🏫 Teacher Portal (Inferred Functionality)

- View assigned classes and subjects.
- Mark attendance for their designated "master" class.
- Enter and update grades for the subjects they teach.
- View student profiles for their classes.
- Assign and review homework.
- Communicate with students and parents via the messaging system.

### 👦 Student Portal (Inferred Functionality)

- View personal attendance records and overall percentage.
- Check grades and exam results for each subject.
- View and pay outstanding fee invoices, including uploading proof of payment.
- Access homework assignments and announcements.
- Communicate with teachers.

---

## 🔐 Security Features

- **Password Hashing:** All user passwords are securely hashed using PHP's `password_hash()` (Bcrypt) function.
- **SQL Injection Prevention:** All database queries are executed using prepared statements (`mysqli_prepare`) to prevent SQL injection attacks.
- **Cross-Site Scripting (XSS) Prevention:** All dynamic output is escaped using `htmlspecialchars()` to mitigate XSS vulnerabilities.
- **CSRF Protection:** Forms are protected with CSRF tokens to prevent unauthorized actions.
- **Brute-Force Protection:** The login system tracks failed attempts and temporarily locks accounts after multiple failures.
- **Secure Session Management:** Session cookies are configured with `httponly` and `samesite` flags.

---

## 📦 Installation & Setup

1.  **Database Setup:**
    - Create a new database in phpMyAdmin (or your preferred MySQL client) named `school_management`.
    - Import the `school_management (2).sql` file into the newly created database. This will set up all the necessary tables and sample data.

2.  **Configuration:**
    - Open the `config.php` file.
    - Verify that the database credentials (`$host`, `$db_name`, `$username`, `$password`) match your local environment. The default XAMPP setup is usually correct.

3.  **Web Server:**
    - Place the entire `School-Management-System` folder into your web server's root directory (e.g., `C:\AarizKhan\Xamp\htdocs\`).

4.  **Access the System:**
    - Open your web browser and navigate to `http://localhost/School-Management-System/`.
    - You will be redirected to `login.php`.

5.  **Default Login:**
    - **Role:** Admin
    - **Username:** `admin`
    - **Password:** `admin123`

---

## 🔮 Potential Future Enhancements

- **Parent Portal:** A dedicated login for guardians to track their child's progress.
- **Library Management:** A module to manage book inventory, issue/return books, and track fines.
- **Transportation Module:** Manage bus routes, student transport allocation, and fees.
- **Exam Scheduling:** Create and publish exam timetables.
- **Automated Notifications:** Send email or SMS alerts for fees, attendance, and announcements.