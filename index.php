<?php
// index.php
session_start();

// Authentication Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Redirect based on role
if ($_SESSION['role'] === 'teacher') {
    header("Location: teacher_dashboard.php");
    exit;
} elseif ($_SESSION['role'] === 'student') {
    header("Location: student_dashboard.php");
    exit;
}

include 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ideal Model School - Management System</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body>

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
            <div class="welcome-msg">Welcome, <?= htmlspecialchars($_SESSION['role']) ?></div>
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
        <button class="nav-btn active"  onclick="showForm('student')">Student Registration</button>
        <button class="nav-btn"         onclick="showForm('add_teacher')">Add Teacher</button>
        <button class="nav-btn"         onclick="showForm('add_class')">Classes & Subjects</button>
        <button class="nav-btn"         onclick="showForm('teacher')">Teacher Assignments</button>
        <button class="nav-btn"         onclick="showForm('fee')">Fee Management</button>
        <button class="nav-btn"         onclick="showForm('attendance')">Attendance</button>
        <button class="nav-btn"         onclick="showForm('grade')">Grades</button>

        <!-- LOGOUT -->
        <a href="logout.php" class="nav-btn logout-btn">Logout</a>
    </nav>

    <!-- Forms Container -->
    <div class="container">

        <!-- ──────────────────────────────────────────────── -->
        <!-- ADMIN FORMS                                      -->
        <!-- Student Registration Form                        -->
        <!-- ──────────────────────────────────────────────── -->
        <form id="student" class="form-content active" action="student_register.php" method="post">
            <h2>Student Registration</h2>
            <div class="form-grid">
                <input type="text" name="full_name" placeholder="Full Name" required>
                <div>
                    <label style="color: #00d4ff; font-size: 0.85rem; margin-bottom: 5px; display: block;">Date of Admission</label>
                    <input type="date" name="admission_date" required style="width: 100%;">
                </div>
                <div>
                    <label style="color: #00d4ff; font-size: 0.85rem; margin-bottom: 5px; display: block;">Date of Birth</label>
                    <input type="date" name="dob" required style="width: 100%;">
                </div>
                <input type="email" name="email" placeholder="Student Email (Optional)">
                <input type="tel"  name="contact_number" placeholder="Student Contact (Optional)">
                <select name="class_id" required>
                    <option value="">Select Class / Grade</option>
                    <?php
                    $res = $conn->query("SELECT id, name FROM classes ORDER BY name");
                    while($row = $res->fetch_assoc()) {
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
                        $res = $conn->query("SELECT id, name FROM subjects ORDER BY name");
                        if ($res && $res->num_rows > 0) {
                            while($row = $res->fetch_assoc()) {
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
        <!-- Add Class Form                                   -->
        <!-- ──────────────────────────────────────────────── -->
        <div id="add_class" class="form-content">
            <h2>Manage Classes & Subjects</h2>
            
            <!-- Sub Navigation Buttons -->
            <div class="sub-nav-grid">
                <button type="button" class="btn btn-reset" onclick="toggleSubSection('sub_subjects')">Manage Subjects</button>
                <button type="button" class="btn btn-reset" onclick="toggleSubSection('sub_create_class')">Create New Class</button>
                <button type="button" class="btn btn-reset" onclick="toggleSubSection('sub_class_subjects')">Class Subjects</button>
                <button type="button" class="btn btn-reset" onclick="toggleSubSection('sub_delete_class')">Delete Class</button>
            </div>

            <!-- ──────────────────────────────────────────────── -->
            <!-- SUBJECTS MANAGEMENT SECTION                      -->
            <!-- ──────────────────────────────────────────────── -->
            <div id="sub_subjects" class="sub-section" style="display:none;">
                <h3 class="section-heading">Subject Management</h3>
                
                <!-- Add Subject -->
                <form action="add_subject.php" method="post" class="form-grid-inline" style="margin-bottom: 1.5rem;">
                    <input type="text" name="subject_name" placeholder="New Subject Name (e.g. Computer Science)" required>
                    <button class="submit-btn" type="submit">Add Subject</button>
                </form>

                <!-- Delete Subject -->
                <form action="delete_subject.php" method="post" class="form-grid-inline" onsubmit="return confirm('⚠️ WARNING: Delete this subject?\n\nThis will remove it from ALL classes and teachers.');">
                    <select name="subject_id" required>
                        <option value="">Select Subject to Delete</option>
                        <?php
                            $res = $conn->query("SELECT id, name FROM subjects ORDER BY name");
                            while($row = $res->fetch_assoc()) {
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
                <div class="form-grid">
                    <input type="text" name="name" placeholder="Class Name (e.g. Play Group, 1st, 2nd)" required>
                    
                    <div class="form-full-width">
                        <label class="subjects-label">Select Subjects taught in this Class:</label>
                        <div class="subjects-grid">
                            <?php
                            $res = $conn->query("SELECT id, name FROM subjects ORDER BY name");
                            if ($res && $res->num_rows > 0) {
                                while($row = $res->fetch_assoc()) {
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
                <form action="add_class_subject.php" method="post">
                <h4 class="sub-heading highlight-text" style="margin-top: 0;">Add Subject to Existing Class</h4>
                <div class="form-grid">
                    <select name="class_id" required>
                        <option value="">Select Class</option>
                        <?php
                        $res = $conn->query("SELECT id, name FROM classes ORDER BY name");
                        while($row = $res->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                        }
                        ?>
                    </select>
                    <select name="subject_id" required>
                        <option value="">Select Subject</option>
                        <?php
                        $res = $conn->query("SELECT id, name FROM subjects ORDER BY name");
                        while($row = $res->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                        }
                        ?>
                    </select>
                    <input type="text" name="book_name" placeholder="Book Name (Optional)">
                </div>
                <button class="submit-btn" type="submit">Add/Update Subject in Class</button>
                </form>

                <!-- Remove Subject from Class -->
                <form action="delete_class_subject.php" method="post" class="section-divider">
                    <h4 class="warning-heading">Remove Subject from Class</h4>
                    <div class="form-grid">
                        <select name="class_id" required>
                            <option value="">Select Class</option>
                            <?php
                            $res = $conn->query("SELECT id, name FROM classes ORDER BY name");
                            while($row = $res->fetch_assoc()) {
                                echo "<option value='{$row['id']}'>{$row['name']}</option>";
                            }
                            ?>
                        </select>
                        <select name="subject_id" required>
                            <option value="">Select Subject to Remove</option>
                            <?php
                            $res = $conn->query("SELECT id, name FROM subjects ORDER BY name");
                            while($row = $res->fetch_assoc()) {
                                echo "<option value='{$row['id']}'>{$row['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button class="submit-btn btn-danger" type="submit">Unlink Subject</button>
                </form>
            </div>

            <!-- Drop Class Section -->
            <div id="sub_delete_class" class="sub-section" style="display:none;">
                <h3 class="section-heading">Delete Class</h3>
                <form action="delete_class.php" method="post" onsubmit="return confirm('⚠️ WARNING: Are you sure you want to delete this class?\n\nThis will delete the class record permanently.');">
                <h4 class="warning-heading" style="margin-top: 0;">Drop Entire Class</h4>
                <div class="form-grid">
                    <select name="class_id" required class="warning-select">
                        <option value="">Select Class to Drop</option>
                        <?php
                        $res = $conn->query("SELECT id, name FROM classes ORDER BY name");
                        while($row = $res->fetch_assoc()) {
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
            
            <!-- 1. Subject Teacher Assignment -->
            <form action="teacher_assign.php" method="post" class="section-divider-top">
                <h3 class="section-heading">Assign Subject Teacher</h3>
                <div class="form-grid">
                    <select name="teacher_id" required>
                        <option value="">Select Teacher</option>
                        <?php
                        $res = $conn->query("SELECT id, name FROM teachers ORDER BY name");
                        while($row = $res->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                        }
                        ?>
                    </select>
                    <select name="subject_id" required>
                        <option value="">Select Subject</option>
                        <?php
                        $res = $conn->query("SELECT id, name FROM subjects ORDER BY name");
                        while($row = $res->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                        }
                        ?>
                    </select>
                    <select name="class_id" required>
                        <option value="">Select Class</option>
                        <?php
                        $res = $conn->query("SELECT id, name FROM classes ORDER BY name");
                        while($row = $res->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                        }
                        ?>
                    </select>
                    <input type="text" name="academic_year" placeholder="Academic Year (e.g. 2024-2025)" required>
                </div>
                <button class="submit-btn" type="submit">Assign Subject Teacher</button>
            </form>

            <!-- 2. Class Master Assignment -->
            <form action="assign_class_master.php" method="post">
                <h3 class="section-heading">Assign Class Master (Attendance)</h3>
                <p class="helper-text">The Class Master is responsible for marking attendance for this class.</p>
                <div class="form-grid">
                    <select name="class_id" required>
                        <option value="">Select Class</option>
                        <?php
                        $res = $conn->query("SELECT id, name FROM classes ORDER BY name");
                        while($row = $res->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                        }
                        ?>
                    </select>
                    <select name="teacher_id" required>
                        <option value="">Select Teacher (Class Master)</option>
                        <?php
                        $res = $conn->query("SELECT id, name FROM teachers ORDER BY name");
                        while($row = $res->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <button class="submit-btn" type="submit">Assign Class Master</button>
            </form>

            <!-- Display Current Class Masters -->
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

        <!-- ──────────────────────────────────────────────── -->
        <!-- Attendance Form                                  -->
        <!-- ──────────────────────────────────────────────── -->
        <form id="attendance" class="form-content" action="attendance_record.php" method="post">
            <h2>Class Attendance</h2>
            
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
                
                    <div style="margin-bottom: 1.5rem; background: rgba(255,255,255,0.05); padding: 1rem; border-radius: 8px;">
                        <label style="color: #00d4ff; display: block; margin-bottom: 0.5rem;">Select Class to Mark Attendance:</label>
                        <select onchange="window.location.href='index.php?attendance_class_id='+this.value">
                            <option value="">-- Select Class --</option>
                            <?php
                            $c_res = $conn->query("SELECT id, name FROM classes ORDER BY name");
                            while($c = $c_res->fetch_assoc()) {
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
                                <th>Student Name</th>
                                <th>Father Name</th>
                                <th>Attendance %</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch students with attendance stats
                            $sql = "SELECT s.id, s.full_name, g.guardian_name,
                                    (SELECT COUNT(*) FROM attendance WHERE student_id = s.id) as total_days,
                                    (SELECT COUNT(*) FROM attendance WHERE student_id = s.id AND status = 'Present') as present_days,
                                    a.status as today_status
                                    FROM students s
                                    LEFT JOIN guardians g ON s.guardian_id = g.id
                                    LEFT JOIN attendance a ON s.id = a.student_id AND a.date = '$attendance_date'
                                    WHERE s.class_id = $target_class_id
                                    ORDER BY s.full_name";
                            
                            $res = $conn->query($sql);
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
                                echo "<tr><td colspan='5' style='text-align:center; padding: 2rem;'>No students found in this class.</td></tr>";
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
            $c_res = $conn->query("SELECT id, name FROM classes ORDER BY name");
            while($c = $c_res->fetch_assoc()) {
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
                        <thead><tr><th>Roll No</th><th>Student Name</th><th>Father Name</th><th>Score (0-100)</th></tr></thead>
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
                        <thead><tr><th>Roll No</th><th>Student Name</th><th>Father Name</th><th>Score (0-100)</th></tr></thead>
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
                            <label class="filter-label">Subject:</label>
                            <select id="view_subject_select" onchange="loadGrades('view', 'view')">
                                <option value="">-- Select Subject --</option>
                                <?= $subject_options ?>
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
                    <thead><tr><th>Roll No</th><th>Student Name</th><th>Father Name</th><th>Score</th><th>DMC</th></tr></thead>
                    <tbody id="view_grade_table_body">
                        <!-- View mode not loaded inline to avoid complexity, use AJAX or rely on fetch_grades_inline if adapted -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ──────────────────────────────────────────────── -->
        <!-- Fee Management Form                              -->
        <!-- ──────────────────────────────────────────────── -->
        <form id="fee" class="form-content" action="fee_management.php" method="post">
            <h2>Fee Management</h2>
            <div class="form-grid">
                
                <?php $fee_class_id = isset($_GET['fee_class_id']) ? (int)$_GET['fee_class_id'] : 0; ?>
                
                <div class="form-full-width fee-filter-container">
                    <label class="fee-label">Select Class:</label>
                    <select onchange="filterFeeByClass(this.value)">
                        <option value="">-- Filter by Class --</option>
                        <?php
                        $c_res = $conn->query("SELECT id, name FROM classes ORDER BY name");
                        while($c = $c_res->fetch_assoc()) {
                            $sel = ($c['id'] == $fee_class_id) ? 'selected' : '';
                            echo "<option value='{$c['id']}' $sel>{$c['name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <select name="student_id" required>
                    <option value="">Select Student</option>
                    <?php
                    if ($fee_class_id > 0) {
                        $res = $conn->query("SELECT id, full_name FROM students WHERE class_id = $fee_class_id ORDER BY full_name");
                        while($row = $res->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['full_name']}</option>";
                        }
                    } else {
                        echo "<option value='' disabled>Select a class first</option>";
                    }
                    ?>
                </select>
                <input type="number" name="amount" placeholder="Fee Amount" min="0" step="0.01" required>
                <input type="date" name="due_date" required>
                <select name="status" required>
                    <option value="">Payment Status</option>
                    <option value="Paid">Paid</option>
                    <option value="Pending">Pending</option>
                </select>
            </div>
            <button class="submit-btn" type="submit">Process Fee</button>
        </form>

    </div> <!-- end .container -->

    <script src="script.js?v=<?php echo time(); ?>"></script>
</body>
</html>

<?php $conn->close(); ?>
