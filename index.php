<?php
// index.php`
// Enforce secure session settings to match login.php
$cookieParams = session_get_cookie_params();
$is_https = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
session_set_cookie_params([
    'lifetime' => $cookieParams['lifetime'],
    'path' => $cookieParams['path'],
    'domain' => $cookieParams['domain'],
    'secure' => $is_https,
    'httponly' => true, // Prevent JavaScript access to session cookie
    'samesite' => 'Lax'
]);
session_start();

// Prevent browser caching (Security measure for Back/Forward buttons)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

// Authentication Check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    // If either user_id or role is missing, clear everything via logout
    // This safely fixes corrupted sessions that cause blank pages
    header("Location: logout.php");
    exit;
}

// Session Timeout (30 minutes)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit;
}
$_SESSION['last_activity'] = time();

// Redirect based on role
if (isset($_SESSION['role']) && $_SESSION['role'] === 'teacher') {
    header("Location: teacher_dashboard.php");
    exit;
} elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'student') {
    header("Location: student_dashboard.php");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get return_to parameter to remember which section user was on
$return_to_section = isset($_GET['return_to']) ? trim($_GET['return_to']) : 'dashboard_view';
// Sanitize to prevent injection
$return_to_section = preg_replace('/[^a-zA-Z0-9_-]/', '', $return_to_section);
if (empty($return_to_section)) {
    $return_to_section = 'dashboard_view';
}

include 'config.php';
require_once 'admin_dashboard_logic.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ideal Model School - Management System</title>
    <link rel="icon" href="logo.jpg" type="image/jpeg">
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="admin_dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="animations.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="loader.css?v=<?php echo time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>

    <!-- Page Pre-Loader -->
    <div class="loader-wrapper">
        <div class="loading">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>

    <!-- Notification Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="notification notification-success" id="notification">
            <?php 
            echo htmlspecialchars($_SESSION['success']);
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="notification notification-error" id="notification">
            <?php 
            echo nl2br(htmlspecialchars($_SESSION['error']));
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Header -->
    <header class="header">
        <div class="header-overlay">
            <h1>Ideal Model School</h1>
            <p>Student Management System</p>
            <div class="welcome-msg">Welcome, <?= htmlspecialchars($_SESSION['username'] ?? $_SESSION['role'] ?? 'User') ?></div>
            <button class="menu-toggle" onclick="toggleSidebar()">Dashboard</button>
        </div>
    </header>

    <!-- Sidebar Navigation -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <nav class="nav-container" id="sidebar">
        <div class="sidebar-header">
            <h3>Dashboard Menu</h3>
            <button class="close-btn" onclick="toggleSidebar()">&times;</button>
        </div>
        
        <!-- ADMIN & PRINCIPAL MENU -->
        <button class="nav-btn active"  onclick="showForm('dashboard_view')">Dashboard Overview</button>
        <button class="nav-btn"         onclick="showForm('student')">Student Registration</button>
        <a href="view_list.php?view=students" class="nav-btn">View Students</a>
        <button class="nav-btn"         onclick="showForm('add_teacher')">Add Teacher</button>
        <button class="nav-btn"         onclick="showForm('teachers_list'); loadTeachersList();">Teachers Directory</button>
        <button class="nav-btn"         onclick="showForm('add_class')">Classes & Subjects</button>
        <button class="nav-btn"         onclick="showForm('teacher')">Teacher Assignments</button>
        <button class="nav-btn"         onclick="showForm('fee')">Fee Management</button>
        <button class="nav-btn"         onclick="showForm('attendance')">Attendance</button>
        <button class="nav-btn"         onclick="showForm('grade')">Grades</button>
        <a href="create_user.php" class="nav-btn">Create User Account</a>
        <a href="change_password.php" class="nav-btn">Change Password</a>
        <button class="nav-btn"         onclick="showForm('password_manager')">Password Manager</button>

        <!-- LOGOUT -->
        <a href="logout.php" class="nav-btn logout-btn">Logout</a>
    </nav>

    <!-- Forms Container -->
    <div class="container">

        <!-- ──────────────────────────────────────────────── -->
        <!-- DASHBOARD OVERVIEW (New)                         -->
        <!-- ──────────────────────────────────────────────── -->
        <div id="dashboard_view" class="form-content active">
            <h2>School Overview</h2>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👨‍🎓</div>
                    <div class="stat-info">
                        <h3><?= $total_students ?></h3>
                        <p>Total Students</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👨‍🏫</div>
                    <div class="stat-info">
                        <h3><?= $total_teachers ?></h3>
                        <p>Active Teachers</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📚</div>
                    <div class="stat-info">
                        <h3><?= $total_classes ?></h3>
                        <p>Total Classes</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-info">
                        <h3><?= $today_attendance_percent ?>%</h3>
                        <p>Attendance Today</p>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-wrapper">
                <div class="chart-box">
                    <h3>Attendance Trends (Last 7 Days)</h3>
                    <div class="chart-container">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                </div>
                <div class="chart-box">
                    <h3>Student Performance (Grades)</h3>
                    <div class="chart-container">
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- ──────────────────────────────────────────────── -->
        <!-- ADMIN FORMS                                      -->
        <!-- Student Registration Form                        -->
        <!-- ──────────────────────────────────────────────── -->
        <form id="student" class="form-content" action="student_register.php" method="post">
            <h2>Student Registration</h2>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="return_to" value="student">
            <div class="form-grid">
                <input type="text" name="full_name" placeholder="Full Name" required>
                <div>
                    <label class="cyan-label">Date of Admission</label>
                    <input type="date" name="admission_date" required class="w-100">
                </div>
                <div>
                    <label class="cyan-label">Date of Birth</label>
                    <input type="date" name="dob" required class="w-100">
                </div>
                <input type="email" name="email" placeholder="Student Email (Optional)">
                <input type="tel"  name="contact_number" placeholder="Student Contact (Optional)">
                <select name="class_id" required>
                    <option value="">Select Class / Grade</option>
                    <?php
                    foreach($classes_list as $row) {
                        echo "<option value='{$row['id']}'>{$row['name']}</option>";
                    }
                    ?>
                </select>
                
                <!-- Guardian Details Section -->
                <input type="text" name="guardian_name" placeholder="Guardian Name" required>
                <input type="tel" name="guardian_contact" placeholder="Guardian Contact" required>
                <input type="email" name="guardian_email" placeholder="Guardian Email">
                <input type="text" name="relationship" placeholder="Relationship (e.g. Father)" required>
                <textarea name="guardian_address" placeholder="Guardian Address" rows="1" class="guardian-address"></textarea>
            </div>
            <button class="submit-btn" type="submit">Register Student</button>
        </form>

        <!-- ──────────────────────────────────────────────── -->
        <!-- Add Teacher Form                                 -->
        <!-- ──────────────────────────────────────────────── -->
        <form id="add_teacher" class="form-content" action="add_teacher.php" method="post">
            <h2>Add Teacher</h2>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="return_to" value="add_teacher">
            <div class="form-grid">
                <input type="text" name="name" placeholder="Teacher Name" required>
                <input type="text" name="father_name" placeholder="Father's Name" required>
                <input type="tel" name="phone" placeholder="Phone Number" required>
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="number" name="salary" placeholder="Salary" min="0" step="0.01" required>
                <input type="number" name="remaining_payment" placeholder="Remaining Payment" min="0" step="0.01" required>
                <div class="form-full-width">
                    <label class="subjects-label">Select Subjects:</label>
                    <div class="subjects-grid">
                        <?php
                        if (!empty($subjects_list)) {
                            foreach($subjects_list as $row) {
                                echo "<label class='subject-checkbox-label'>";
                                echo "<input type='checkbox' name='subjects[]' value='{$row['id']}'>";
                                echo htmlspecialchars($row['name']);
                                echo "</label>";
                            }
                        } else {
                            echo "<p class='no-subjects-msg'>No subjects found. Please add subjects first.</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
            <button class="submit-btn" type="submit">Register Teacher</button>
        </form>

        <!-- ──────────────────────────────────────────────── -->
        <!-- Teachers Directory (Loaded via AJAX)             -->
        <!-- ──────────────────────────────────────────────── -->
        <div id="teachers_list" class="form-content">
            <h2>Teachers Directory</h2>
            <div class="filter-box">
                <button class="btn btn-reset" onclick="loadTeachersList()">↻ Refresh List</button>
                <a href="recycle_bin.php?type=teachers" class="btn btn-reset" style="background: rgba(255, 68, 68, 0.1); color: #ff4444; border-color: #ff4444;">🗑️ Recycle Bin</a>
            </div>
            
            <div class="table-responsive">
                <table class="students-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name / Father</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th style="text-align:right;">Salary</th>
                            <th style="text-align:right;">Balance</th>
                            <th>Subjects</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="teachers_table_body">
                        <!-- Rows will be loaded here via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ──────────────────────────────────────────────── -->
        <!-- Add Class Form                                   -->
        <!-- ──────────────────────────────────────────────── -->
        <div id="add_class" class="form-content">
            <h2>Manage Classes & Subjects</h2>
            
            <!-- Sub Navigation Buttons -->
            <div class="sub-nav-grid">
                <button type="button" class="btn btn-reset" onclick="toggleSubSection('sub_subjects')">Manage Subjects</button>
                <button type="button" class="btn btn-reset" onclick="toggleSubSection('sub_create_class')">Create New Class</button>
                <button type="button" class="btn btn-reset" onclick="toggleSubSection('sub_class_subjects')">Class Subjects</button>
                <button type="button" class="btn btn-reset" onclick="toggleSubSection('sub_promote_class')">Promote Students</button>
                <button type="button" class="btn btn-reset" onclick="toggleSubSection('sub_delete_class')">Delete Class</button>
            </div>

            <!-- ──────────────────────────────────────────────── -->
            <!-- SUBJECTS MANAGEMENT SECTION                      -->
            <!-- ──────────────────────────────────────────────── -->
            <div id="sub_subjects" class="sub-section" style="display:none;">
                <h3 class="section-heading">Subject Management</h3>
                
                <!-- Add Subject -->
                <form action="add_subject.php" method="post" class="form-grid-inline filter-box">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="text" name="subject_name" placeholder="New Subject Name (e.g. Computer Science)" required>
                    <button class="submit-btn" type="submit">Add Subject</button>
                </form>

                <!-- Delete Subject -->
                <form action="delete_subject.php" method="post" class="form-grid-inline" onsubmit="return confirm('⚠️ WARNING: Delete this subject?\n\nThis will remove it from ALL classes and teachers.');">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <select name="subject_id" required>
                        <option value="">Select Subject to Delete</option>
                        <?php
                            foreach($subjects_list as $row) {
                                echo "<option value='{$row['id']}'>{$row['name']}</option>";
                            }
                        ?>
                    </select>
                    <button class="submit-btn btn-danger" type="submit">Delete Subject</button>
                </form>
            </div>

            <!-- ──────────────────────────────────────────────── -->
            <!-- CLASS MANAGEMENT SECTION                         -->
            <!-- ──────────────────────────────────────────────── -->
            <!-- Add Class Section -->
            <div id="sub_create_class" class="sub-section" style="display:none;">
                <h3 class="section-heading">Create New Class</h3>
                <form action="add_classes.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="return_to" value="add_class">
                <div class="form-grid">
                    <input type="text" name="name" placeholder="Class Name (e.g. Play Group, 1st, 2nd)" required>
                    
                    <div class="form-full-width">
                        <label class="subjects-label">Select Subjects taught in this Class:</label>
                        <div class="subjects-grid">
                            <?php
                            if (!empty($subjects_list)) {
                                foreach($subjects_list as $row) {
                                    echo "<div class='subject-item'>";
                                    echo "<label class='subject-checkbox-label'><input type='checkbox' name='subjects[]' value='{$row['id']}' onchange='toggleBookInput(this)'>";
                                    echo htmlspecialchars($row['name']);
                                    echo "</label>";
                                    echo "<input type='text' name='book_names[{$row['id']}]' class='book-input' placeholder='Book Name (Optional)'></div>";
                                }
                            } else {
                                echo "<p class='no-subjects-msg'>No subjects found.</p>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <button class="submit-btn" type="submit">Add Class</button>
                </form>
            </div>

            <!-- Add Subject to Existing Class -->
            <div id="sub_class_subjects" class="sub-section" style="display:none;">
                <h3 class="section-heading">Manage Class Subjects</h3>
                <p class="helper-text">View, add, and remove subjects from classes with their assigned teachers and book names.</p>
                
                <!-- Class Filter -->
                <div class="class-subjects-filter-container">
                    <label class="class-subjects-filter-label">Select Class:</label>
                    <select id="class_subjects_filter" onchange="loadClassSubjectsTable(this.value)" class="class-subjects-filter-select">
                        <option value="">-- Select a Class --</option>
                        <?php
                        foreach($classes_list as $row) {
                            echo "<option value='{$row['id']}'>" . htmlspecialchars($row['name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Subjects Table Container -->
                <div id="class_subjects_container" class="class-subjects-container">
                    <div id="class_subjects_table_wrapper"></div>
                    
                    <!-- Add Subject Form -->
                    <div class="class-subjects-add-form">
                        <h4 class="sub-heading highlight-text">Add Subject to This Class</h4>
                        <form action="add_class_subject.php" method="post" class="add-subject-form-grid">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" id="add_subject_class_id" name="class_id" value="">
                            <input type="hidden" name="return_to" value="add_class">
                            
                            <div>
                                <label class="filter-label">Subject Name:</label>
                                <select name="subject_id" required class="add-subject-form-select">
                                    <option value="">Select Subject</option>
                                    <?php
                                    foreach($subjects_list as $row) {
                                        echo "<option value='{$row['id']}'>" . htmlspecialchars($row['name']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div>
                                <label class="filter-label">Book Name:</label>
                                <input type="text" name="book_name" placeholder="Book Name (Optional)" class="add-subject-form-input">
                            </div>
                            
                            <button type="submit" class="submit-btn">Add Subject</button>
                        </form>
                    </div>
                </div>
                
                <!-- No Class Selected Message -->
                <div id="no_class_selected_msg" class="no-class-selected-msg">
                    <p>Please select a class to view and manage its subjects.</p>
                </div>
            </div>

            <!-- Promote Class Section -->
            <div id="sub_promote_class" class="sub-section" style="display:none;">
                <h3 class="section-heading">Promote Students to Next Class</h3>
                <p class="helper-text">Select students to promote from one class to another.</p>
                
                <form id="promote_form" action="promote_students.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <div class="form-grid">
                        <div>
                            <label class="filter-label">Current Class:</label>
                            <select id="promote_current_class" name="current_class_id" required onchange="loadPromoteStudents()">
                                <option value="">-- Select Current Class --</option>
                                <?php
                                foreach($classes_list as $row) {
                                    echo "<option value='{$row['id']}'>{$row['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label class="filter-label">Promote To (Target Class):</label>
                            <select name="target_class_id" required>
                                <option value="">-- Select Target Class --</option>
                                <?php
                                foreach($classes_list as $row) {
                                    echo "<option value='{$row['id']}'>{$row['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div id="promote_student_list_container" style="margin-top: 1.5rem;">
                        <!-- Table loaded via AJAX -->
                    </div>

                    <button id="promote_btn" class="submit-btn" type="submit" style="display:none;">Promote Selected Students</button>
                </form>
            </div>

            <!-- Drop Class Section -->
            <div id="sub_delete_class" class="sub-section" style="display:none;">
                <h3 class="section-heading">Delete Class</h3>
                <form action="delete_class.php" method="post" onsubmit="return confirm('⚠️ WARNING: Are you sure you want to delete this class?\n\nThis will delete the class record permanently.');">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <h4 class="warning-heading" style="margin-top: 0;">Drop Entire Class</h4>
                <div class="form-grid">
                    <select name="class_id" required class="warning-select">
                        <option value="">Select Class to Drop</option>
                        <?php
                        foreach($classes_list as $row) {
                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <button class="submit-btn btn-danger" type="submit">Drop Class</button>
                </form>
            </div>

        </div>

        <!-- ──────────────────────────────────────────────── -->
        <!-- Teacher Assignment Form                          -->
        <!-- ──────────────────────────────────────────────── -->
        <div id="teacher" class="form-content">
            <h2>Teacher Assignments & Roles</h2>
            
            <!-- Sub Navigation -->
            <div class="sub-nav-grid">
                <button type="button" class="btn btn-reset" onclick="toggleSubSection('sub_assign_subject')">Assign Subject Teacher</button>
                <button type="button" class="btn btn-reset" onclick="toggleSubSection('sub_subject_list')">Subject Assignments List</button>
                <button type="button" class="btn btn-reset" onclick="toggleSubSection('sub_assign_master')">Assign Class Master</button>
                <button type="button" class="btn btn-reset" onclick="toggleSubSection('sub_master_list')">Class Master List</button>
            </div>
            
            <!-- 1. Subject Teacher Assignment -->
            <div id="sub_assign_subject" class="sub-section">
            <form action="teacher_assign.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <h3 class="section-heading">Assign Subject Teacher</h3>
                <div class="form-grid">
                    <select name="teacher_id" required>
                        <option value="">Select Teacher</option>
                        <?php
                        foreach($teachers_list as $row) {
                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                        }
                        ?>
                    </select>
                    <select name="subject_id" required>
                        <option value="">Select Subject</option>
                        <?php
                        foreach($subjects_list as $row) {
                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                        }
                        ?>
                    </select>
                    <select name="class_id" required>
                        <option value="">Select Class</option>
                        <?php
                        foreach($classes_list as $row) {
                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                        }
                        ?>
                    </select>
                    <input type="text" name="academic_year" placeholder="Academic Year (e.g. 2024-2025)" required>
                </div>
                <button class="submit-btn" type="submit">Assign Subject Teacher</button>
            </form>
            </div>

            <!-- Current Subject Assignments List -->
            <div id="sub_subject_list" class="sub-section hidden">
            <div class="current-assignments-section">
                <h4 class="sub-heading">Current Subject Assignments</h4>
                <table class="students-table">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Subject</th>
                            <th>Teacher</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $assign_sql = "SELECT ta.id, c.name as class_name, s.name as subject_name, t.name as teacher_name 
                                       FROM teacher_assignments ta
                                       JOIN classes c ON ta.class_id = c.id
                                       JOIN subjects s ON ta.subject_id = s.id
                                       JOIN teachers t ON ta.teacher_id = t.id
                                       ORDER BY c.name, s.name";
                        $assign_res = $conn->query($assign_sql);
                        if ($assign_res && $assign_res->num_rows > 0) {
                            while($row = $assign_res->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['class_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['subject_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['teacher_name']) . "</td>";
                                echo "<td>
                                        <form action='delete_teacher_assignment.php' method='post' onsubmit='return confirm(\"Are you sure you want to remove this assignment?\");' class='inline-form'>
                                            <input type='hidden' name='csrf_token' value='" . $_SESSION['csrf_token'] . "'>
                                            <input type='hidden' name='assignment_id' value='" . $row['id'] . "'>
                                            <button type='submit' class='action-btn delete action-btn-delete'>Remove</button>
                                        </form>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center-gray'>No active assignments found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            </div>

            <!-- 2. Class Master Assignment -->
            <div id="sub_assign_master" class="sub-section" style="display:none;">
            <form action="assign_class_master.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <h3 class="section-heading">Assign Class Master (Attendance)</h3>
                <p class="helper-text">The Class Master is responsible for marking attendance for this class.</p>
                <div class="form-grid">
                    <select name="class_id" required>
                        <option value="">Select Class</option>
                        <?php
                        foreach($classes_list as $row) {
                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                        }
                        ?>
                    </select>
                    <select name="teacher_id" required>
                        <option value="">Select Teacher (Class Master)</option>
                        <?php
                        foreach($teachers_list as $row) {
                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <button class="submit-btn" type="submit">Assign Class Master</button>
            </form>
            </div>

            <!-- Display Current Class Masters -->
            <div id="sub_master_list" class="sub-section" style="display:none;">
            <div class="current-masters-section">
                <h4 class="sub-heading">Current Class Masters</h4>
                <table class="students-table current-masters-table">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Class Master</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $cm_sql = "SELECT c.name as class_name, t.name as teacher_name 
                                   FROM classes c 
                                   LEFT JOIN teachers t ON c.class_master_id = t.id 
                                   WHERE c.deleted_at IS NULL
                                   ORDER BY c.name";
                        $cm_res = $conn->query($cm_sql);
                        if ($cm_res && $cm_res->num_rows > 0) {
                            while($cm = $cm_res->fetch_assoc()) {
                                $t_name = $cm['teacher_name'] ? htmlspecialchars($cm['teacher_name']) : '<span class="not-assigned">Not Assigned</span>';
                                echo "<tr><td>" . htmlspecialchars($cm['class_name']) . "</td><td>" . $t_name . "</td></tr>";
                            }
                        } else {
                            echo "<tr><td colspan='2'>No classes found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            </div>
        </div>

        <!-- ──────────────────────────────────────────────── -->
        <!-- Attendance Form                                  -->
        <!-- ──────────────────────────────────────────────── -->
        <form id="attendance" class="form-content" action="attendance_record.php" method="post">
            <h2>Class Attendance</h2>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="return_to" value="attendance">
            
            <?php
            // Logic: Determine which class to show
            $can_mark_attendance = false;
            $target_class_id = 0;
            $target_class_name = "";

            $can_mark_attendance = true;
            // Admin selects class via GET parameter
            if (isset($_GET['attendance_class_id'])) {
                $target_class_id = (int)$_GET['attendance_class_id'];
                // Fetch class name
                $c_res = $conn->query("SELECT name FROM classes WHERE id = $target_class_id");
                if ($c_row = $c_res->fetch_assoc()) $target_class_name = $c_row['name'];
            }
            ?>

            <?php if ($can_mark_attendance): ?>
                
                <!-- Admin Class Selector -->
                <?php $attendance_date = isset($_GET['attendance_date']) ? $_GET['attendance_date'] : date('Y-m-d'); ?>
                
                    <div class="filter-box">
                        <label class="cyan-label">Select Class to Mark Attendance:</label>
                        <select onchange="window.location.href='index.php?attendance_class_id='+this.value">
                            <option value="">-- Select Class --</option>
                            <?php
                            foreach($classes_list as $c) {
                                $sel = ($c['id'] == $target_class_id) ? 'selected' : '';
                                echo "<option value='{$c['id']}' $sel>{$c['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                <?php if ($target_class_id > 0): ?>
                    <div class="attendance-header">
                        <h3>Class: <span class="highlight-text"><?= htmlspecialchars($target_class_name) ?></span></h3>
                        <input type="hidden" name="class_id" value="<?= $target_class_id ?>">
                        <input type="date" name="date" value="<?= htmlspecialchars($attendance_date) ?>" required class="date-input-auto" onchange="updateAttendanceDate(<?= $target_class_id ?>, this.value)">
                    </div>

                    <table class="students-table table-no-margin">
                        <thead>
                            <tr>
                                <th>Roll No</th>
                                <th>Std ID</th>
                                <th>Student Name</th>
                                <th>Father Name</th>
                                <th>Attendance %</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch students with attendance stats
                            $sql = "SELECT s.id, s.std_id, s.full_name, g.guardian_name,
                                    (SELECT COUNT(*) FROM attendance WHERE student_id = s.id) as total_days,
                                    (SELECT COUNT(*) FROM attendance WHERE student_id = s.id AND status = 'Present') as present_days,
                                    a.status as today_status
                                    FROM students s
                                    LEFT JOIN guardians g ON s.guardian_id = g.id
                                    LEFT JOIN attendance a ON s.id = a.student_id AND a.date = ?
                                    WHERE s.class_id = ?
                                    AND s.deleted_at IS NULL
                                    ORDER BY s.full_name";
                            
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("si", $attendance_date, $target_class_id);
                            $stmt->execute();
                            $res = $stmt->get_result();
                            $counter = 1;
                            $has_existing_data = false;
                            
                            if ($res && $res->num_rows > 0) {
                                while($row = $res->fetch_assoc()) {
                                    // Generate Roll Number based on Class Name number if available, else Class ID
                                    $class_num = preg_replace('/[^0-9]/', '', $target_class_name);
                                    $prefix = ($class_num !== '') ? $class_num : $target_class_id;
                                    $roll_no = $prefix . str_pad($counter++, 2, '0', STR_PAD_LEFT);
                                    
                                    // Calculate Percentage
                                    $percent = ($row['total_days'] > 0) ? round(($row['present_days'] / $row['total_days']) * 100) : 0;
                                    $percent_color = ($percent < 75) ? '#ff6b6b' : '#2ecc71';
                                    
                                    // Determine Radio Status
                                    $status = $row['today_status'];
                                    if ($status) $has_existing_data = true;
                                    $chk_p = ($status === 'Present' || !$status) ? 'checked' : '';
                                    $chk_a = ($status === 'Absent') ? 'checked' : '';

                                    echo "<tr>";
                                    echo "<td data-label='Roll No'><strong>$roll_no</strong></td>";
                                    echo "<td data-label='Std ID' style='color: #00d4ff; font-weight: bold;'>" . htmlspecialchars($row['std_id'] ?? 'N/A') . "</td>";
                                    echo "<td data-label='Name'>" . htmlspecialchars($row['full_name']) . "</td>";
                                    echo "<td data-label='Father Name'>" . htmlspecialchars($row['guardian_name'] ?? '-') . "</td>";
                                    echo "<td data-label='Attendance %'><span style='color:$percent_color; font-weight:bold;'>$percent%</span> <span style='font-size:0.8em; color:#999;'>({$row['present_days']}/{$row['total_days']})</span></td>";
                                    echo "<td data-label='Status' class='status-cell'>
                                            <label class='radio-label-present'><input type='radio' name='attendance[{$row['id']}]' value='Present' $chk_p> <b>P</b></label>
                                            <label class='radio-label-absent'><input type='radio' name='attendance[{$row['id']}]' value='Absent' $chk_a> <b>A</b></label>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center-gray'>No students found in this class.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                    
                    <?php if ($res && $res->num_rows > 0): ?>
                        <button class="submit-btn grade-submit-btn" type="submit"><?= $has_existing_data ? 'Update Attendance' : 'Submit Attendance' ?></button>
                    <?php endif; ?>

                <?php else: ?>
                    <p class="admin-msg">Please select a class above to load the student list.</p>
                <?php endif; ?>
            <?php endif; ?>
        </form>

        <!-- ──────────────────────────────────────────────── -->
        <!-- Grade Entry Form                                 -->
        <!-- ──────────────────────────────────────────────── -->
        <div id="grade" class="form-content">
            <h2>Grades</h2>
            
            <?php
            // 1. Get Filters
            $g_class_id = isset($_GET['grade_class_id']) ? (int)$_GET['grade_class_id'] : 0;
            $g_subject_id = isset($_GET['grade_subject_id']) ? (int)$_GET['grade_subject_id'] : 0;
            $g_term = isset($_GET['grade_term']) ? trim($_GET['grade_term']) : '';
            $active_grade_section = isset($_GET['grade_section']) ? $_GET['grade_section'] : 'enter';
            
            // 2. Build Class Dropdown Options
            $class_options = "";
            foreach($classes_list as $c) {
                $sel = ($c['id'] == $g_class_id) ? 'selected' : '';
                $class_options .= "<option value='{$c['id']}' $sel>{$c['name']}</option>";
            }

            // 3. Build Subject Dropdown Options
            $subject_options = "";
            if ($g_class_id > 0) {
                     // Show subjects assigned to this class
                     $s_res = $conn->query("SELECT s.id, s.name 
                                            FROM subjects s 
                                            JOIN class_subjects cs ON s.id = cs.subject_id 
                                            WHERE cs.class_id = $g_class_id 
                                            AND s.deleted_at IS NULL
                                            ORDER BY s.name");
                     while($s = $s_res->fetch_assoc()) {
                         $sel = ($s['id'] == $g_subject_id) ? 'selected' : '';
                         $subject_options .= "<option value='{$s['id']}' $sel>{$s['name']}</option>";
                     }
            }
            ?>

            <!-- Sub Navigation -->
            <div class="sub-nav-grid">
                <button type="button" class="btn btn-reset" onclick="toggleSubSection('sub_enter_grades')">Enter Grades</button>
                <button type="button" class="btn btn-reset" onclick="toggleSubSection('sub_update_grades')">Update Grades</button>
                <button type="button" class="btn btn-reset" onclick="toggleSubSection('sub_view_grades')">View Grades</button>
            </div>

            <!-- ──────────────────────────────────────────────── -->
            <!-- ENTER GRADES SECTION                             -->
            <!-- ──────────────────────────────────────────────── -->
            <div id="sub_enter_grades" class="sub-section" style="display: <?= $active_grade_section == 'enter' ? 'block' : 'none' ?>;">
                <h3>Enter Grades</h3>
                <form action="grade_entry.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="grade_section" value="enter">
                    <div class="filter-ui-container">
                        <div class="form-grid" style="margin-bottom: 1rem;">
                            <div>
                                <label class="filter-label">Class:</label>
                                <select id="enter_class_select" onchange="filterGradeByClass(this.value, 'enter')">
                                    <option value="">-- Select Class --</option>
                                    <?= $class_options ?>
                                </select>
                            </div>
                            <div>
                                <label class="filter-label">Subject:</label>
                                <select id="enter_subject_select" onchange="loadGrades('enter', 'edit')">
                                    <option value="">-- Select Subject --</option>
                                    <?= $subject_options ?>
                                </select>
                            </div>
                            <div>
                                <label class="filter-label">Term / Exam:</label>
                                <input type="text" id="enter_term_input" value="<?= htmlspecialchars($g_term) ?>" placeholder="e.g. Midterm" onblur="loadGrades('enter', 'edit')">
                            </div>
                        </div>
                        <button type="button" class="btn btn-reset btn-full-width" onclick="loadGrades('enter', 'edit')">Load Student List</button>
                    </div>

                    <input type="hidden" id="enter_h_class_id" name="class_id" value="<?= $g_class_id ?>">
                    <input type="hidden" id="enter_h_subject_id" name="subject_id" value="<?= $g_subject_id ?>">
                    <input type="hidden" id="enter_h_term" name="term" value="<?= htmlspecialchars($g_term) ?>">

                    <table class="students-table table-no-margin">
                        <thead><tr><th>Roll No</th><th>Std ID</th><th>Student Name</th><th>Father Name</th><th>Score (0-100)</th></tr></thead>
                        <tbody id="enter_grade_table_body">
                            <?php if ($g_class_id > 0 && $active_grade_section == 'enter') include 'fetch_grades_inline.php'; ?>
                        </tbody>
                    </table>
                    <button id="enter_grade_submit_btn" class="submit-btn grade-submit-btn" type="submit" style="display: <?= ($g_class_id > 0 && $active_grade_section == 'enter') ? 'block' : 'none' ?>;">Save Grades</button>
                </form>
            </div>

            <!-- ──────────────────────────────────────────────── -->
            <!-- UPDATE GRADES SECTION                            -->
            <!-- ──────────────────────────────────────────────── -->
            <div id="sub_update_grades" class="sub-section" style="display: <?= $active_grade_section == 'update' ? 'block' : 'none' ?>;">
                <h3>Update Grades</h3>
                <form action="grade_entry.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="grade_section" value="update">
                    <div class="filter-ui-container">
                        <div class="form-grid" style="margin-bottom: 1rem;">
                            <div>
                                <label class="filter-label">Class:</label>
                                <select id="update_class_select" onchange="filterGradeByClass(this.value, 'update')">
                                    <option value="">-- Select Class --</option>
                                    <?= $class_options ?>
                                </select>
                            </div>
                            <div>
                                <label class="filter-label">Subject:</label>
                                <select id="update_subject_select" onchange="loadGrades('update', 'edit')">
                                    <option value="">-- Select Subject --</option>
                                    <?= $subject_options ?>
                                </select>
                            </div>
                            <div>
                                <label class="filter-label">Term / Exam:</label>
                                <input type="text" id="update_term_input" value="<?= htmlspecialchars($g_term) ?>" placeholder="e.g. Midterm" onblur="loadGrades('update', 'edit')">
                            </div>
                        </div>
                        <button type="button" class="btn btn-reset btn-full-width" onclick="loadGrades('update', 'edit')">Load Student List</button>
                    </div>

                    <input type="hidden" id="update_h_class_id" name="class_id" value="<?= $g_class_id ?>">
                    <input type="hidden" id="update_h_subject_id" name="subject_id" value="<?= $g_subject_id ?>">
                    <input type="hidden" id="update_h_term" name="term" value="<?= htmlspecialchars($g_term) ?>">

                    <table class="students-table table-no-margin">
                        <thead><tr><th>Roll No</th><th>Std ID</th><th>Student Name</th><th>Father Name</th><th>Score (0-100)</th></tr></thead>
                        <tbody id="update_grade_table_body">
                            <?php if ($g_class_id > 0 && $active_grade_section == 'update') include 'fetch_grades_inline.php'; ?>
                        </tbody>
                    </table>
                    <button id="update_grade_submit_btn" class="submit-btn grade-submit-btn" type="submit" style="display: <?= ($g_class_id > 0 && $active_grade_section == 'update') ? 'block' : 'none' ?>;">Update Grades</button>
                </form>
            </div>

            <!-- ──────────────────────────────────────────────── -->
            <!-- VIEW GRADES SECTION                              -->
            <!-- ──────────────────────────────────────────────── -->
            <div id="sub_view_grades" class="sub-section" style="display: <?= $active_grade_section == 'view' ? 'block' : 'none' ?>;">
                <h3>View Grades</h3>
                <div class="filter-ui-container">
                    <div class="form-grid" style="margin-bottom: 1rem;">
                        <div>
                            <label class="filter-label">Class:</label>
                            <select id="view_class_select" onchange="filterGradeByClass(this.value, 'view')">
                                <option value="">-- Select Class --</option>
                                <?= $class_options ?>
                            </select>
                        </div>
                        <div>
                            <label class="filter-label">Term / Exam:</label>
                            <input type="text" id="view_term_input" value="<?= htmlspecialchars($g_term) ?>" placeholder="e.g. Midterm" onblur="loadGrades('view', 'view')">
                        </div>
                    </div>
                    <button type="button" class="btn btn-reset btn-full-width" onclick="loadGrades('view', 'view')">View Grades</button>
                    <button type="button" class="btn btn-reset btn-full-width" style="margin-top: 10px; background: linear-gradient(45deg, #00d4ff, #0056b3); color: white; border: none;" onclick="printAllDMCs()">Print All DMCs</button>
                </div>

                <table class="students-table table-no-margin">
                    <thead><tr><th>Roll No</th><th>Std ID</th><th>Student Name</th><th>Father Name</th><th>Percentage</th><th>DMC</th></tr></thead>
                    <tbody id="view_grade_table_body">
                        <!-- View mode not loaded inline to avoid complexity, use AJAX or rely on fetch_grades_inline if adapted -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ──────────────────────────────────────────────── -->
        <!-- Fee Management Form                              -->
        <!-- ──────────────────────────────────────────────── -->
        <div id="fee" class="form-content">
            <h2>Fee Management</h2>
            
            <!-- Fee Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon stat-icon-warning">💰</div>
                    <div class="stat-info"><h3>Rs. <?= number_format($total_due) ?></h3><p>Total Outstanding</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon stat-icon-success">✅</div>
                    <div class="stat-info"><h3>Rs. <?= number_format($collected_month) ?></h3><p>Collected This Month</p></div>
                </div>
                <div class="stat-card clickable <?= $pending_verifications > 0 ? 'border-danger' : '' ?>" onclick="toggleSubSection('sub_verify_payments')">
                    <div class="stat-icon stat-icon-danger">🔔</div>
                    <div class="stat-info"><h3><?= $pending_verifications ?></h3><p>Pending Verifications</p></div>
                </div>
            </div>

            <!-- Sub Navigation -->
            <div class="sub-nav-grid">
                <button type="button" class="btn btn-reset" onclick="toggleSubSection('sub_generate_invoice')">Generate Invoices</button>
                <button type="button" class="btn btn-reset" onclick="toggleSubSection('sub_view_delete_invoice')">View & Delete Invoices</button>
                <button type="button" class="btn btn-reset" onclick="toggleSubSection('sub_edit_invoice')">Edit Invoices</button>
                <button type="button" class="btn btn-reset" onclick="toggleSubSection('sub_verify_payments')">Verify Online Payments</button>
                <button type="button" class="btn btn-reset" onclick="toggleSubSection('sub_cash_collection')">Cash Collection</button>
            </div>

            <!-- 1. Generate Invoices -->
            <div id="sub_generate_invoice" class="sub-section" style="display:none;">
                <h3>Generate Monthly Invoices</h3>
                <form action="fee_backend.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="action" value="generate_bulk">
                    <div class="form-grid">
                        <select name="class_id" required>
                            <option value="">Select Class</option>
                            <?php
                            foreach($classes_list as $c) {
                                echo "<option value='{$c['id']}'>{$c['name']}</option>";
                            }
                            ?>
                        </select>
                        <input type="text" name="title" placeholder="Invoice Title (e.g. Tuition Fee - Nov 2024)" required>
                        <input type="number" name="amount" placeholder="Amount (Rs.)" required>
                        <input type="date" name="due_date" required>
                    </div>
                    <button class="submit-btn" type="submit">Generate Invoices</button>
                </form>
            </div>

            <!-- 2. View & Delete Invoices -->
            <div id="sub_view_delete_invoice" class="sub-section" style="display:none;">
                <h3>View & Delete Invoices by Class</h3>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: #00d4ff; font-weight: bold;">Select Class:</label>
                    <select id="delete_class_filter" class="class-filter-select" onchange="filterInvoicesByClass()">
                        <option value="">-- Select Class --</option>
                        <?php
                        foreach($classes_list as $c) {
                            echo "<option value='{$c['id']}'>{$c['name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div id="invoices_list" style="display: none;">
                    <div class="invoice-list-header">
                        <h4 class="invoice-header-title">Invoices for Class: <span id="selected_class_name"></span></h4>
                        <div class="invoice-sort-buttons">
                            <button class="btn btn-reset" onclick="sortInvoices('student')">Sort by Student</button>
                            <button class="btn btn-reset" onclick="sortInvoices('amount')">Sort by Amount</button>
                            <button class="btn btn-reset" onclick="sortInvoices('status')">Sort by Status</button>
                        </div>
                    </div>
                    
                    <table class="students-table full-width-table" id="invoices_table">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Invoice No.</th>
                                <th>Title</th>
                                <th>Amount (Rs.)</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="invoices_tbody">
                        </tbody>
                    </table>
                    <p id="no_invoices_msg" class="no-invoices-msg">No invoices found for this class.</p>
                </div>
            </div>

            <!-- 3. Edit Invoices -->
            <div id="sub_edit_invoice" class="sub-section" style="display:none;">
                <h3>Edit Invoices</h3>
                
                <!-- Edit Tabs -->
                <div class="edit-invoice-tabs">
                    <button class="btn btn-reset edit-tab active" data-tab="bulk-edit" onclick="switchEditTab('bulk-edit')">Bulk Edit (All Students)</button>
                    <button class="btn btn-reset edit-tab" data-tab="single-edit" onclick="switchEditTab('single-edit')">Edit Individual Invoice</button>
                </div>

                <!-- Bulk Edit Tab -->
                <div id="bulk-edit-tab" class="edit-content" style="display: block;">
                    <h4 class="edit-section-title">Edit for All Students in Invoice</h4>
                    <p class="edit-section-desc">Select an existing invoice to edit amount/title for all students</p>
                    
                    <form action="fee_backend.php" method="post">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="bulk_edit_invoice">
                        
                        <div class="form-grid">
                            <div>
                                <label class="input-label">Select Invoice:</label>
                                <select name="invoice_id" id="bulk_invoice_select" required onchange="loadBulkInvoiceDetails()" class="form-select-dark">
                                    <option value="">-- Select Invoice --</option>
                                    <?php
                                    $inv_res = $conn->query("SELECT DISTINCT fi.id, fi.invoice_number, fi.title, fi.amount, COUNT(fi.id) as student_count, c.name as class_name
                                                            FROM fee_invoices fi
                                                            JOIN students s ON fi.student_id = s.id
                                                            JOIN classes c ON s.class_id = c.id
                                                            GROUP BY fi.title, fi.amount, fi.id, fi.invoice_number, c.id
                                                            ORDER BY c.name, fi.title DESC");
                                    if ($inv_res && $inv_res->num_rows > 0) {
                                        while($inv = $inv_res->fetch_assoc()) {
                                            echo "<option value='{$inv['id']}' data-count='{$inv['student_count']}'>[{$inv['class_name']}] {$inv['invoice_number']} - {$inv['title']} (Rs. {$inv['amount']}) - {$inv['student_count']} students</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div id="bulk_details" class="details-box">
                            <p class="details-label"><strong>Current Details:</strong></p>
                            <p>Students Affected: <span id="bulk_student_count" class="highlight-count">0</span></p>
                            
                            <div class="form-grid" style="margin-top: 1rem;">
                                <div>
                                    <label class="input-label">New Title:</label>
                                    <input type="text" name="new_title" placeholder="New invoice title" class="form-select-dark">
                                </div>
                                <div>
                                    <label class="input-label">New Amount (Rs.):</label>
                                    <input type="number" name="new_amount" placeholder="New amount" step="0.01" class="form-select-dark">
                                </div>
                            </div>

                            <div class="warning-box">
                                <strong>⚠ Warning:</strong> This will update the amount/title for all <span id="warn_count" style="font-weight: bold;">0</span> students in this invoice.
                            </div>

                            <button type="submit" class="submit-btn btn-warning-update">Update for All Students</button>
                        </div>
                    </form>
                </div>

                <!-- Single Edit Tab -->
                <div id="single-edit-tab" class="edit-content" style="display: none;">
                    <h4 class="edit-section-title">Edit Invoice for Single Student</h4>
                    <p class="edit-section-desc">Edit amount/title for a specific student's invoice</p>
                    
                    <form action="fee_backend.php" method="post">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="single_edit_invoice">
                        
                        <div class="form-grid">
                            <div>
                                <label class="input-label">Select Student:</label>
                                <select name="student_id" id="single_student_select" required onchange="loadStudentInvoices()" class="form-select-dark">
                                    <option value="">-- Select Student --</option>
                                    <?php
                                    $s_res = $conn->query("SELECT DISTINCT s.id, s.full_name, c.name as class_name FROM students s JOIN classes c ON s.class_id = c.id WHERE s.deleted_at IS NULL ORDER BY c.name, s.full_name");
                                    if ($s_res && $s_res->num_rows > 0) {
                                        while($s = $s_res->fetch_assoc()) {
                                            echo "<option value='{$s['id']}'>[{$s['class_name']}] {$s['full_name']}</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div>
                                <label class="input-label">Select Invoice:</label>
                                <select name="invoice_id" id="single_invoice_select" required class="form-select-dark">
                                    <option value="">-- Select Invoice --</option>
                                </select>
                            </div>
                        </div>

                        <div id="single_details" class="details-box">
                            <p class="details-label"><strong>Current Details:</strong></p>
                            <p>Title: <span id="single_invoice_title" class="highlight-count"></span></p>
                            <p>Amount: <span id="single_invoice_amount" class="text-success-bold"></span></p>
                            <p>Status: <span id="single_invoice_status" class="text-warning-bold"></span></p>
                            
                            <div class="form-grid" style="margin-top: 1rem;">
                                <div>
                                    <label class="input-label">New Title:</label>
                                    <input type="text" name="new_title" placeholder="New invoice title" class="form-select-dark">
                                </div>
                                <div>
                                    <label class="input-label">New Amount (Rs.):</label>
                                    <input type="number" name="new_amount" placeholder="New amount" step="0.01" class="form-select-dark">
                                </div>
                            </div>

                            <button type="submit" class="submit-btn btn-update-single">Update Invoice</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 4. Verify Payments -->
            <div id="sub_verify_payments" class="sub-section" style="display:none;">
                <h3>Pending Online Verifications</h3>
                <?php if (!empty($pending_payments_list)): ?>
                    <div class="dashboard-grid">
                        <?php foreach($pending_payments_list as $pay): 
                            // Calculate remaining balance for this invoice
                            // Optimization: Use pre-calculated map from logic file
                            $verified_amount = $verified_amount_map[$pay['invoice_id']] ?? 0;
                            $remaining = (float)$pay['invoice_amount'] - (float)$verified_amount;
                        ?>
                            <div class="dashboard-card">
                                <div class="card-header">
                                    <h3><?= htmlspecialchars($pay['full_name']) ?></h3>
                                    <span class="status-pending-text">Pending</span>
                                </div>
                                
                                <!-- Invoice Number - Prominent Display -->
                                <div class="invoice-number-badge">
                                    <p>Invoice No.</p>
                                    <p><?= htmlspecialchars($pay['invoice_number'] ?? 'N/A') ?></p>
                                </div>
                                
                                <p><strong><?= htmlspecialchars($pay['title']) ?></strong></p>
                                <p class="meta-text">Class: <?= htmlspecialchars($pay['class_name']) ?></p>
                                <p>Payment Amount: Rs. <strong><?= number_format($pay['amount']) ?></strong></p>
                                <p>Invoice Total: Rs. <?= number_format($pay['invoice_amount']) ?></p>
                                <p class="<?= $remaining <= 0 ? 'text-success-bold' : 'text-danger-bold' ?>">
                                    Remaining: Rs. <strong><?= number_format(max(0, $remaining)) ?></strong>
                                </p>
                                <p class="ref-text">Ref: <?= htmlspecialchars($pay['reference_no']) ?></p>
                                <?php if($pay['proof_image']): ?>
                                    <a href="<?= htmlspecialchars($pay['proof_image']) ?>" target="_blank" class="proof-link">View Screenshot 📎</a>
                                <?php endif; ?>
                                <div class="action-buttons-row">
                                    <form action="fee_backend.php" method="post" class="flex-form">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" name="action" value="verify_payment">
                                        <input type="hidden" name="payment_id" value="<?= $pay['id'] ?>">
                                        <input type="hidden" name="status" value="Verified">
                                        <button type="submit" class="submit-btn btn-approve">Approve</button>
                                    </form>
                                    <form action="fee_backend.php" method="post" class="flex-form">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" name="action" value="verify_payment">
                                        <input type="hidden" name="payment_id" value="<?= $pay['id'] ?>">
                                        <input type="hidden" name="status" value="Rejected">
                                        <button type="submit" class="submit-btn btn-reject">Reject</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: // No pending payments ?>
                    <p class="empty-state">No pending verifications.</p>
                <?php endif; ?>
            </div>

            <!-- 5. Cash Collection -->
            <div id="sub_cash_collection" class="sub-section" style="display:none;">
                <h3>Cash Collection</h3>
                <form action="fee_backend.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="action" value="collect_cash">
                    <div class="form-grid">
                        <input type="number" name="student_id" placeholder="Enter Student Roll No / ID" required>
                        <input type="number" name="amount" placeholder="Amount Received" required>
                        <input type="text" name="invoice_id" placeholder="Invoice ID (Optional)">
                    </div>
                    <p class="helper-text-small">* System will auto-allocate to oldest unpaid invoice if Invoice ID is blank.</p>
                    <button class="submit-btn" type="submit">Receive Cash</button>
                </form>
            </div>
        </div>

        <!-- ──────────────────────────────────────────────── -->
        <!-- Password Manager (Admin Reset)                   -->
        <!-- ──────────────────────────────────────────────── -->
        <div id="password_manager" class="form-content">
            <h2>User Password Manager</h2>
            <p class="helper-text">Search for a student or teacher by Username to verify their profile and reset their password.</p>
            
            <div class="filter-box" style="align-items: flex-end;">
                <div style="flex: 1;">
                    <label class="filter-label">User Type:</label>
                    <select id="pm_type_select" class="filter-select">
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                    </select>
                </div>
                <div style="flex: 2;">
                    <label class="filter-label">Search Username:</label>
                    <input type="text" id="pm_search_username" placeholder="Enter Username (e.g. ims-0036)" class="filter-select" style="width: 100%;">
                </div>
                <button class="submit-btn" type="button" onclick="searchUserForReset()" style="margin: 0; width: auto; min-width: 120px;">Search</button>
            </div>

            <!-- Result Container -->
            <div id="pm_result_container" class="pm-result-container">
                <h3 class="sub-heading pm-profile-heading">User Profile</h3>
                
                <div class="pm-flex-wrapper">
                    <!-- Profile Info -->
                    <div class="pm-profile-info">
                        <h2 id="pm_name" class="pm-name-text">User Name</h2>
                        <p id="pm_subtitle" class="pm-subtitle-text">Class/Role</p>
                        
                        <div class="pm-details-grid">
                            <div><strong>Username:</strong> <span id="pm_username_display"></span></div>
                            <div id="pm_detail1"></div>
                            <div id="pm_detail2"></div>
                            <div id="pm_account_status"></div>
                        </div>
                    </div>

                    <!-- Action Form -->
                    <div class="pm-action-form-box">
                        <h4 class="pm-reset-heading">Reset Password</h4>
                        <form id="pm_reset_form">
                            <input type="hidden" id="pm_hidden_id">
                            <input type="hidden" id="pm_hidden_type">
                            <input type="hidden" id="pm_csrf" value="<?= $_SESSION['csrf_token'] ?>">
                            
                            <input type="password" id="pm_new_password" class="pm-password-input" placeholder="New Password" required minlength="6">
                            
                            <button type="submit" class="submit-btn pm-reset-btn">Update Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div> <!-- end .container -->

    <script>
        // Ensure PHP variables are defined to prevent JS errors
        <?php
        $grades_dist = $grades_dist ?? [];
        $chart_data = [
            'dates' => $dates ?? [],
            'present' => $present_counts ?? [],
            'absent' => $absent_counts ?? [],
            'grades' => [
                $grades_dist['Excellent'] ?? 0,
                $grades_dist['Good'] ?? 0,
                $grades_dist['Average'] ?? 0,
                $grades_dist['Fail'] ?? 0
            ]
        ];
        ?>
        // Pass PHP data to JavaScript for Charts
        window.schoolChartData = <?= json_encode($chart_data) ?>;
        
        // Pass section to display
        window.returnToSection = "<?= htmlspecialchars($return_to_section ?? 'dashboard_view') ?>";
        
        // Chart initialization is now handled by animations.js after dashboard is set active
    </script>
    <script>
        // Page loader
        window.addEventListener('load', function() {
            const loader = document.querySelector('.loader-wrapper');
            if (loader) {
                loader.classList.add('hidden');
            }
        });
    </script>
    <script src="script.js?v=<?php echo time(); ?>"></script>
    <script src="animations.js?v=<?php echo time(); ?>"></script>
</body>
</html>

<?php $conn->close(); ?>
