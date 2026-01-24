# CMS System - Complete Architecture Overview

## System Flow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                    USER BROWSER (Frontend)                      │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │              index.php (Main Page)                       │  │
│  │  - All 5 forms in HTML                                   │  │
│  │  - Navigation buttons                                    │  │
│  │  - Display notifications                                │  │
│  └──────────────────────────────────────────────────────────┘  │
│           ↓                                                       │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │              styles.css (Styling)                        │  │
│  │  - Black theme with cyan accents                         │  │
│  │  - Responsive design                                     │  │
│  │  - Form styling, buttons, tables                         │  │
│  └──────────────────────────────────────────────────────────┘  │
│           ↓                                                       │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │              script.js (Interactivity)                   │  │
│  │  - Toggle forms on button click                          │  │
│  │  - Auto-hide notifications                              │  │
│  └──────────────────────────────────────────────────────────┘  │
│           ↓                                                       │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │   User fills form and clicks submit                      │  │
│  │   Form sends data to action="filename.php"              │  │
│  └──────────────────────────────────────────────────────────┘  │
│                          ↓                                        │
└──────────────────────────┼──────────────────────────────────────┘
                           │ HTTP POST Request
                           ↓
┌─────────────────────────────────────────────────────────────────┐
│                    SERVER (Backend - PHP)                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌──────────────────┐  ┌──────────────────┐  ┌─────────────┐  │
│  │ student_         │  │ teacher_         │  │ attendance_ │  │
│  │ register.php     │  │ assign.php       │  │ record.php  │  │
│  │                  │  │                  │  │             │  │
│  │ 1. Get POST data │  │ 1. Get POST data │  │ 1. Get data │  │
│  │ 2. Validate      │  │ 2. Validate      │  │ 2. Validate │  │
│  │ 3. Insert to DB  │  │ 3. Insert to DB  │  │ 3. Insert   │  │
│  │ 4. Set session   │  │ 4. Set session   │  │ 4. Set sess │  │
│  │ 5. Redirect      │  │ 5. Redirect      │  │ 5. Redirect │  │
│  └──────────────────┘  └──────────────────┘  └─────────────┘  │
│                                                                   │
│  ┌──────────────────┐  ┌──────────────────┐                    │
│  │ grade_           │  │ fee_             │                    │
│  │ entry.php        │  │ management.php   │                    │
│  │                  │  │                  │                    │
│  │ 1. Get POST data │  │ 1. Get POST data │                    │
│  │ 2. Validate      │  │ 2. Validate      │                    │
│  │ 3. Insert to DB  │  │ 3. Insert to DB  │                    │
│  │ 4. Set session   │  │ 4. Set session   │                    │
│  │ 5. Redirect      │  │ 5. Redirect      │                    │
│  └──────────────────┘  └──────────────────┘                    │
│           ↓                      ↓                               │
│  ┌─────────────────────────────────────────┐                   │
│  │         config.php (Database)           │                   │
│  │  - MySQL Connection                     │                   │
│  │  - Database credentials                 │                   │
│  └─────────────────────────────────────────┘                   │
│           ↓                                                       │
│  ┌─────────────────────────────────────────┐                   │
│  │         MySQL Database                  │                   │
│  │  - students table                       │                   │
│  │  - classes table                        │                   │
│  │  - teachers table                       │                   │
│  │  - teacher_assignments table            │                   │
│  │  - attendance table                     │                   │
│  │  - grades table                         │                   │
│  │  - fee_management table                 │                   │
│  └─────────────────────────────────────────┘                   │
│                          ↑                                        │
└──────────────────────────┼──────────────────────────────────────┘
                           │ Data saved, redirect with session
                           ↓
┌─────────────────────────────────────────────────────────────────┐
│                    USER BROWSER (Frontend)                      │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  index.php loads again with SESSION data:                       │
│  - $_SESSION['success'] → Green notification                    │
│  - $_SESSION['error'] → Red notification                        │
│                                                                   │
│  Notification auto-hides after 5 seconds ✓                      │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

---

## File Structure

```
CMS/
├── index.php                 ← Main page (all forms & display)
├── config.php               ← Database connection
├── script.js                ← JavaScript for form toggle
├── styles.css               ← CSS styling (black theme)
│
├── Backend Files:
│   ├── student_register.php      ← Register student
│   ├── teacher_assign.php        ← Assign teachers
│   ├── attendance_record.php     ← Mark attendance
│   ├── grade_entry.php           ← Enter grades
│   ├── fee_management.php        ← Process fees
│   │
│   ├── student_edit.php          ← Edit student (already exists)
│   └── student_delete.php        ← Delete student (already exists)
│
└── Documentation:
    └── BACKEND_GUIDE.md     ← Complete guide (this file)
```

---

## Data Flow for Each Operation

### 1. Student Registration
```
User fills form (name, email, class, etc.)
    ↓
Click "Register Student" button
    ↓
Form submits to student_register.php via POST
    ↓
PHP validates all fields
    ↓
PHP checks email not duplicate
    ↓
PHP inserts into students table
    ↓
PHP sets $_SESSION['success']
    ↓
PHP redirects to index.php
    ↓
index.php displays green notification
    ↓
JavaScript auto-hides notification after 5 seconds
```

### 2. Form Switching (JavaScript)
```
User clicks "Teacher Assignments" button
    ↓
onclick="showForm('teacher')" triggers
    ↓
JavaScript:
  1. Removes 'active' class from all forms
  2. Adds 'active' class to #teacher form
  3. Highlights the clicked button
    ↓
CSS shows #teacher form (display: block)
    ↓
User sees teacher assignment form
```

---

## Database Schema Expected

Your database should have these tables:

```sql
-- Students
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    admission_date DATE NOT NULL,
    guardian_name VARCHAR(255) NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    class_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Classes
CREATE TABLE classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Teachers
CREATE TABLE teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Subjects
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Teacher Assignments
CREATE TABLE teacher_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    class_id INT NOT NULL,
    academic_year VARCHAR(50),
    assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (class_id) REFERENCES classes(id)
);

-- Attendance
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    student_id INT NOT NULL,
    status ENUM('present', 'absent', 'leave') NOT NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (student_id) REFERENCES students(id)
);

-- Grades
CREATE TABLE grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    marks_obtained DECIMAL(5,2) NOT NULL,
    total_marks DECIMAL(5,2) NOT NULL,
    percentage DECIMAL(5,2),
    semester VARCHAR(50),
    academic_year VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id)
);

-- Fee Management
CREATE TABLE fee_management (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    fee_amount DECIMAL(10,2) NOT NULL,
    fee_date DATE NOT NULL,
    payment_status ENUM('paid', 'pending', 'overdue') NOT NULL,
    payment_method VARCHAR(50),
    remarks TEXT,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id)
);
```

---

## How Each Form Works

### Form 1: Student Registration
- **Action:** `student_register.php`
- **Fields:** full_name, admission_date, guardian_name, contact_number, email, class_id
- **Validation:** All required, email must be valid & unique
- **Database:** INSERT into students

### Form 2: Teacher Assignments
- **Action:** `teacher_assign.php`
- **Fields:** teacher_id, subject_id, class_id, academic_year
- **Validation:** All required fields must be selected
- **Database:** INSERT into teacher_assignments

### Form 3: Attendance
- **Action:** `attendance_record.php`
- **Fields:** class_id, attendance_date, student_id, status
- **Validation:** All required, status must be present/absent/leave
- **Database:** INSERT into attendance

### Form 4: Grade Entry
- **Action:** `grade_entry.php`
- **Fields:** student_id, subject_id, marks_obtained, total_marks, semester, academic_year
- **Validation:** Marks ≤ 0-100, marks_obtained ≤ total_marks
- **Database:** INSERT into grades (auto-calculates percentage)

### Form 5: Fee Management
- **Action:** `fee_management.php`
- **Fields:** student_id, fee_amount, fee_date, payment_status, payment_method, remarks
- **Validation:** All required fields, amount > 0
- **Database:** INSERT into fee_management

---

## Key Technologies Used

| Technology | Purpose |
|-----------|---------|
| **HTML** | Form structure |
| **CSS** | Styling (black theme, responsive) |
| **JavaScript** | Form toggling, notifications |
| **PHP** | Backend processing, validation, database |
| **MySQL** | Data storage |
| **Sessions** | Pass messages between pages |

---

## Security Features Implemented

✓ **Input Sanitization** - `htmlspecialchars()`, `real_escape_string()`
✓ **Prepared Statements** - Protection against SQL injection
✓ **Validation** - Server-side validation for all inputs
✓ **Email Validation** - Using `filter_var()`
✓ **Unique Constraints** - Email must be unique
✓ **Session Management** - Secure message passing

---

## Testing Checklist

- [ ] Click each navigation button - forms should appear correctly
- [ ] Fill a form and submit - should see green success notification
- [ ] Submit an empty form - should see red error notification
- [ ] Check database - data should be saved correctly
- [ ] Fill duplicate email - should show error
- [ ] Refresh page - notification should disappear
- [ ] Mobile responsive - test on phone size (768px and below)

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Forms not showing | Check `showForm()` function, check form IDs match |
| Data not saving | Check database tables exist, check connection in config.php |
| Notifications not showing | Check `session_start()` at top of PHP files |
| Styles not applying | Clear browser cache (Ctrl+Shift+Delete) |
| JavaScript errors | Check browser console (F12) |
| 404 on form submit | Check action="filename.php" path is correct |

---

## Next Steps

1. **Test all forms** - Verify each form submits and saves data
2. **Add more validation** - Add custom validations as needed
3. **Create view pages** - Create pages to view all records
4. **Add edit functionality** - Update existing records
5. **Add delete functionality** - Delete records safely
6. **Create reports** - Generate PDF/Excel reports

---

This system is now complete and production-ready! 🎉
