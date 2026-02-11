# School Management System (CMS)
### Project Documentation & Technical Report

**Developer:** [Your Name]  
**Date:** January 2026  
**Repository:** [GitHub Link](https://github.com/Aariz-star/School-Management-System)

---

## 1. Executive Summary
The **Ideal Model School Management System** is a comprehensive web-based application designed to streamline the administrative and academic operations of an educational institution. It provides a centralized dashboard for managing students, teachers, classes, subjects, examinations, and financial records.

The system is built with a focus on **data integrity**, **scalability**, and **user experience**, featuring a modern, responsive dark-mode interface that works seamlessly on both desktop and mobile devices.

---

## 2. Key Features

### 🎓 Student Management
*   **Registration:** Full profile creation including admission dates and contact details.
*   **Guardian Linking:** Normalized database structure linking students to guardians (parents) to avoid data redundancy.
*   **Directory:** Searchable list of all students with "Edit" and "Delete" capabilities.

### 👨‍🏫 Teacher & HR Management
*   **Staff Profiles:** Manage teacher details, qualifications, and contact info.
*   **Subject Assignment:** Assign specific subjects and classes to teachers for specific academic years.
*   **Payroll:** Track salaries, remaining payments, and bonuses/deductions.
*   **Attendance:** Daily check-in/check-out tracking for staff.

### 📚 Academic Administration
*   **Class & Subject Management:** Dynamic creation of classes and subjects.
*   **Curriculum Mapping:** Link specific subjects to classes (e.g., "Physics" for "Class 10").
*   **Grading System:** Entry form for student marks based on terms (Midterm, Final) and specific subjects.

### 💰 Financial Module
*   **Fee Collection:** Track student fee payments (Paid/Pending status).
*   **Expense Tracking:** Record school operational expenses (Rent, Utilities, etc.).
*   **Profit/Loss Analysis:** (Database ready) Calculate financial health by comparing Fees vs. Salaries + Expenses.

---

## 3. Technical Architecture

### Frontend (User Interface)
*   **HTML5 & CSS3:** Custom-built responsive design.
*   **Theme:** Modern "Dark Mode" aesthetic with neon blue accents (`#00d4ff`).
*   **Responsiveness:** Mobile-first approach using CSS Grid and Flexbox. Tables convert to "Card View" on mobile for better readability.
*   **JavaScript:** Vanilla JS for sidebar navigation, modal interactions, and asynchronous delete operations.

### Backend (Server-Side)
*   **Language:** PHP (Native).
*   **Architecture:** Modular design (separate logic files for `student_register.php`, `add_teacher.php`, etc.).
*   **Security:** Prepared Statements (`mysqli_prepare`) to prevent SQL Injection.

### Database
*   **System:** MySQL / MariaDB.
*   **Structure:** Relational Database (RDBMS) with 14+ normalized tables.
*   **Key Relations:** Foreign Key constraints used to ensure data consistency (e.g., deleting a Class automatically unlinks its Subjects).

---

## 4. Database Schema Overview
The database is designed for high scalability, moving beyond a simple flat-file structure to a fully normalized schema.

### Core Entities
1.  **`students`**: Stores personal info, linked to `guardians` and `classes`.
2.  **`guardians`**: Parent details (stored once, linked to multiple siblings).
3.  **`teachers`**: Staff profiles and salary base info.
4.  **`classes`**: Grade levels (e.g., "Class 1", "Class 10").
5.  **`subjects`**: Global subject list (e.g., "Math", "Science").

### Operational Tables
6.  **`class_subjects`**: Junction table defining which subjects are taught in which class.
7.  **`teacher_assignments`**: Links Teacher + Class + Subject + Year.
8.  **`attendance`**: Daily student attendance records.
9.  **`grades`**: Academic performance linked to Exams and Terms.

### Financial Tables
10. **`fees`**: Student payment records.
11. **`salary_payments`**: History of payments made to teachers.
12. **`school_expenses`**: General operational costs.

---

## 5. User Interface Highlights

### Dashboard
The dashboard features a hidden sidebar navigation that toggles on click, maximizing screen real estate. It provides quick access to all major modules.

### Mobile Optimization
*   **Navigation:** Sidebar slides in from the left on mobile.
*   **Forms:** Input grids stack vertically on smaller screens.
*   **Tables:** Standard rows transform into detailed cards on mobile devices, ensuring no horizontal scrolling is required.

---

## 6. Future Roadmap
The system is built to support future modules without major restructuring:

1.  **Parent Portal:** Allow guardians to log in using their email to view their child's grades and attendance.
2.  **Library Management:** Track book issues and returns linked to the `students` table.
3.  **Automated Reports:** Generate PDF report cards and salary slips automatically.

---

## 7. Installation Guide

1.  **Environment:** Install XAMPP or WAMP server.

---

*Generated by Ideal Model School Management System*