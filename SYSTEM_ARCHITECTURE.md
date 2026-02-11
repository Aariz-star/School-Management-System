# System Architecture - Student Management System

## 1. High-Level Overview

The CMS is a monolithic web application built using the **LAMP stack** (Linux, Apache, MySQL, PHP). It follows a **Page-Controller** pattern where each PHP file acts as both a view (HTML) and a controller (Logic), though logic is often separated into handler files (e.g., `student_register.php`).

### Architecture Diagram
```
[Client Browser]  <-->  [Apache Web Server]  <-->  [PHP Interpreter]  <-->  [MySQL Database]
      |                        |                        |
  HTML/CSS/JS              .htaccess                Logic &             Data Storage
                           Routing                 Validation
```

## 2. Technology Stack

| Component | Technology | Description |
|-----------|------------|-------------|
| **Frontend** | HTML5, CSS3 | Semantic markup, Flexbox/Grid layout, Custom Black/Cyan Theme |
| **Scripting** | JavaScript (ES6) | DOM manipulation, AJAX for grades/subjects, Form toggling |
| **Backend** | PHP 7.4+ | Server-side logic, Session management, Database interaction |
| **Database** | MySQL 5.7+ | Relational data storage |
| **Server** | Apache/Nginx | Web server |

## 3. Directory Structure & File Organization

The project is organized into a flat structure with logical grouping by naming convention.

```
CMS/
├── 📂 Configuration & Assets
│   ├── config.php                  # Database connection settings
│   ├── styles.css                  # Global stylesheet (Black theme)
│   ├── script.js                   # Global JavaScript (Form switching, AJAX)
│   └── .htaccess                   # HTTPS enforcement & Security headers
│
├── 📂 Dashboards (Views)
│   ├── index.php                   # Admin Dashboard (Main Entry)
│   ├── teacher_dashboard.php       # Teacher Dashboard
│   ├── student_dashboard.php       # Student Dashboard
│   ├── login.php                   # User Authentication Page
│   └── logout.php                  # Session destruction
│
├── 📂 Teacher Module Components
│   ├── teacher_dashboard_logic.php # Logic separation for Teacher Dashboard
│   ├── teacher_dashboard.css       # Specific styles for Teacher Dashboard
│   ├── teacher_dashboard.js        # Specific scripts for Teacher Dashboard
│   ├── teachers_list.php           # Admin view: List of all teachers
│   └── teacher_edit.php            # Admin view: Edit teacher details
│
├── 📂 Backend Handlers (Controllers)
│   ├── student_register.php        # Handle student registration
│   ├── student_edit.php            # Handle student updates
│   ├── student_delete.php          # Handle student deletion (AJAX)
│   ├── add_teacher.php             # Handle teacher registration
│   ├── teacher_delete.php          # Handle teacher deletion (AJAX)
│   ├── teacher_assign.php          # Handle subject-teacher assignment
│   ├── add_classes.php             # Handle creating classes
│   ├── delete_class.php            # Handle deleting classes
│   ├── add_subject.php             # Handle creating subjects
│   ├── delete_subject.php          # Handle deleting subjects
│   ├── add_class_subject.php       # Link subjects to classes
│   ├── delete_class_subject.php    # Unlink subjects from classes
│   ├── assign_class_master.php     # Assign teacher as class master
│   ├── attendance_record.php       # Handle attendance submission
│   ├── grade_entry.php             # Handle grade submission/updates
│   ├── fee_management.php          # Handle fee records
│   └── create_user.php             # Utility to create login users
│
├── 📂 AJAX & Data Fetchers
│   ├── fetch_grades.php            # Returns HTML table of grades
│   ├── fetch_grades_inline.php     # PHP include for initial grade load
│   ├── fetch_subjects.php          # Returns <option> list of subjects
│   └── view_list.php               # Generic list viewer (Students/Teachers)
│
└── 📂 Reports & Output
    ├── view_dmc.php                # Detailed Marks Certificate generation
    └── print_all_dmcs.php          # Bulk printing utility
```