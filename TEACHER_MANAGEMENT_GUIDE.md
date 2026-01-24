# Teacher Management Module - Setup Guide

## Overview
This guide explains how to set up and use the new Teacher Management module that includes adding teachers with multiple subjects and displaying all teachers.

## Database Setup

### Step 1: Run SQL Commands
Execute the following SQL commands in your MySQL database:

```sql
-- Add new columns to teachers table
ALTER TABLE teachers ADD COLUMN (
    father_name VARCHAR(100) DEFAULT '',
    salary DECIMAL(10, 2) DEFAULT 0,
    phone VARCHAR(20) DEFAULT '',
    email VARCHAR(100) DEFAULT '',
    remaining_payment DECIMAL(10, 2) DEFAULT 0
);

-- Create junction table for multiple subjects per teacher
CREATE TABLE IF NOT EXISTS teacher_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    UNIQUE KEY unique_teacher_subject (teacher_id, subject_id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);
```

### Step 2: Verify Setup
Run these commands to verify everything is set up correctly:

```sql
-- Check teachers table structure
DESCRIBE teachers;

-- Check teacher_subjects table  
DESCRIBE teacher_subjects;

-- Check available subjects
SELECT * FROM subjects;
```

## Files Created/Modified

### New Backend Files
1. **add_teacher.php** - Handles teacher registration form submission
   - Validates all required fields
   - Checks email format
   - Inserts teacher data into teachers table
   - Links teacher to selected subjects via teacher_subjects table

2. **add_classes.php** - Handles class/grade creation
   - Validates class name
   - Checks for duplicate classes
   - Inserts new class into classes table

### Modified Files
1. **index.php**
   - Added "Add Teacher" form with:
     - Name, Father's Name, Phone, Email, Salary, Remaining Payment
     - Multi-select checkboxes for subjects
   - Added "Add Class" form
   - Added "Teachers Directory" section with toggle button
   - Teachers list displays all teacher details and assigned subjects

2. **script.js**
   - Added `toggleTeachersList()` function for showing/hiding teachers table

## Features

### Add Teacher Form
- **Fields:**
  - Teacher Name (required)
  - Father's Name (required)
  - Phone Number (required)
  - Email Address (required, validated)
  - Salary (number, optional)
  - Remaining Payment (number, optional)
  - Subjects (multiple checkboxes)

- **Functionality:**
  - Validates all fields
  - Inserts teacher into teachers table
  - Creates records in teacher_subjects for each selected subject
  - Shows success/error notification

### Add Class Form
- **Fields:**
  - Class Name (required, e.g., "Play Group", "1st", "2nd")

- **Functionality:**
  - Validates class name
  - Prevents duplicate classes
  - Shows success/error notification

### Teachers Directory
- **Features:**
  - Hidden by default (display: none)
  - Toggle "Show All Teachers" button to reveal/hide table
  - Shows all teacher information:
    - ID, Name, Father's Name
    - Phone, Email
    - Salary, Remaining Payment
    - Assigned Subjects
  - Edit and Delete action buttons for each teacher

## Database Schema

### teachers table (altered)
```
id              INT PRIMARY KEY AUTO_INCREMENT
name            VARCHAR(100)
father_name     VARCHAR(100)        [NEW]
salary          DECIMAL(10,2)       [NEW]
phone           VARCHAR(20)         [NEW]
email           VARCHAR(100)        [NEW]
remaining_payment DECIMAL(10,2)     [NEW]
```

### teacher_subjects table (new)
```
id              INT PRIMARY KEY AUTO_INCREMENT
teacher_id      INT NOT NULL (FK -> teachers.id)
subject_id      INT NOT NULL (FK -> subjects.id)
UNIQUE(teacher_id, subject_id)
```

## Usage Instructions

### To Add a Teacher:
1. Click "Add Teacher" button in navigation
2. Fill in all required fields (Name, Father's Name, Phone, Email)
3. Enter Salary and Remaining Payment
4. Select multiple subjects by checking checkboxes
5. Click "Register Teacher"
6. Success notification will appear

### To Add a Class:
1. Click "Add Class" button in navigation
2. Enter class name (e.g., "Play Group", "1st", "2nd")
3. Click "Add Class"
4. Success notification will appear

### To View Teachers:
1. Scroll to "Teachers Directory" section
2. Click "Show All Teachers" button
3. Table will display all registered teachers with their details
4. Click "Hide Teachers" to hide the table again
5. Use Edit/Delete buttons for individual teacher management

## Error Handling

The system validates:
- ✓ All required fields are filled
- ✓ Email format is valid (XXX@XXX.XXX)
- ✓ Salary and Remaining Payment are valid numbers
- ✓ No duplicate class names
- ✓ Database connection and queries

Errors are displayed in red notification boxes and logged in session.

## Dependencies

- MySQL 5.7+ (for CREATE TABLE IF NOT EXISTS)
- PHP 7.0+
- subjects table must be populated with available subjects
- classes table (already exists)

## Next Steps (Optional)

1. Create **teacher_edit.php** - To edit existing teacher information
2. Create **teacher_delete.php** - To delete teachers
3. Add teacher search/filter functionality
4. Add validation for duplicate teacher names or emails
5. Create teacher attendance tracking module
6. Add subject assignment history/audit log

## Troubleshooting

### Empty Subjects List
- Check if subjects table has data: `SELECT * FROM subjects;`
- Add subjects before adding teachers if needed

### Teacher Not Appearing in List
- Verify teacher_subjects junction table exists: `SHOW TABLES LIKE 'teacher_subjects';`
- Check if teacher data was inserted: `SELECT * FROM teachers;`

### Subjects Not Showing for Teacher
- Check teacher_subjects table: `SELECT * FROM teacher_subjects WHERE teacher_id = X;`
- Verify subjects table has correct IDs: `SELECT id, name FROM subjects;`

### Duplicate Class Error
- Class names are unique, check existing classes: `SELECT * FROM classes;`
- Use different class name or delete duplicate first
