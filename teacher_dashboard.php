<?php
require_once 'teacher_dashboard_logic.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Ideal Model School</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="teacher_dashboard.css?v=<?php echo time(); ?>">
</head>
<body>

    <!-- Notification Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="notification notification-success" id="notification">
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="notification notification-error" id="notification">
            <?php echo nl2br(htmlspecialchars($_SESSION['error'])); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Header -->
    <header class="header">
        <div class="header-overlay">
            <h1>Ideal Model School</h1>
            <p>Teacher Portal</p>
            <div class="welcome-msg">Welcome, <?php echo htmlspecialchars($_SESSION['role']); ?></div>
            <button class="menu-toggle" onclick="toggleSidebar()">Dashboard</button>
        </div>
    </header>

    <!-- Sidebar Navigation -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <nav class="nav-container" id="sidebar">
        <div class="sidebar-header">
            <h3>Menu</h3>
            <button class="close-btn" onclick="toggleSidebar()">&times;</button>
        </div>
        
        <button class="nav-btn active" onclick="showForm('dashboard')">Home Dashboard</button>
        <button class="nav-btn" onclick="showForm('classes')">My Classes</button>
        <button class="nav-btn" onclick="showForm('homework')">Homework</button>
        <button class="nav-btn" onclick="showForm('attendance')">Attendance</button>
        <button class="nav-btn" onclick="showForm('grade')">Grades</button>
        <button class="nav-btn" onclick="showForm('timetable')">Timetable</button>
        <a href="logout.php" class="nav-btn logout-btn">Logout</a>
    </nav>

    <!-- Forms Container -->
    <div class="container">

        <!-- TEACHER DASHBOARD (Landing Page) -->
        <div id="dashboard" class="form-content active">
            <h2>Home Dashboard</h2>
            <?php
                if ($teacher_info) {
                    echo "<h3 class='student-heading'>Welcome back, " . htmlspecialchars($teacher_info['name']) . "</h3>";
                }
            ?>

            <div class="dashboard-grid">
                <!-- Stats Card -->
                <div class="dashboard-card">
                    <div class="card-header"><h3>My Classes</h3></div>
                    <div class="stat-number" style="color: #00d4ff;">
                        <?= $assignments ? $assignments->num_rows : 0 ?>
                    </div>
                    <div class="stat-label">Active Assignments</div>
                </div>

                <!-- Announcements Card -->
                <div class="dashboard-card">
                    <div class="card-header"><h3>Announcements</h3></div>
                    <?php if ($announcements_res && $announcements_res->num_rows > 0): ?>
                        <?php while($ann = $announcements_res->fetch_assoc()): ?>
                            <div class="list-item">
                                <span class="item-title"><?= htmlspecialchars($ann['title']) ?></span>
                                <span class="item-meta"><?= $ann['publish_date'] ?></span>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="empty-state">No new announcements.</p>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div class="dashboard-card">
                    <div class="card-header"><h3>Quick Actions</h3></div>
                    <div class="list-item" onclick="showForm('attendance')" style="cursor:pointer;">
                        <span class="item-title">Mark Attendance</span>
                        <span class="item-meta">→</span>
                    </div>
                    <div class="list-item" onclick="showForm('grade')" style="cursor:pointer;">
                        <span class="item-title">Enter Grades</span>
                        <span class="item-meta">→</span>
                    </div>
                    <div class="list-item" onclick="showForm('homework')" style="cursor:pointer;">
                        <span class="item-title">Post Homework</span>
                        <span class="item-meta">→</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- MY CLASSES -->
        <div id="classes" class="form-content">
            <h2>My Classes & Subjects</h2>
            <?php
            if ($assignments && $assignments->num_rows > 0) {
                $assignments->data_seek(0); // Reset pointer
                echo "<table class='students-table dashboard-table'><thead><tr><th>Class</th><th>Subject</th><th>Academic Year</th></tr></thead><tbody>";
                while($row = $assignments->fetch_assoc()) {
                    echo "<tr><td data-label='Class'>" . htmlspecialchars($row['class_name']) . "</td><td data-label='Subject'>" . htmlspecialchars($row['subject_name']) . "</td><td data-label='Year'>" . htmlspecialchars($row['academic_year']) . "</td></tr>";
                }
                echo "</tbody></table>";
            } else {
                echo "<p class='no-assignments'>No active class assignments found.</p>";
            }
            ?>
        </div>

        <!-- Attendance Form -->
        <form id="attendance" class="form-content" action="attendance_record.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <h2>Class Attendance</h2>
            <?php
            if ($target_class_id > 0) {
                ?>
                <div class="attendance-header">
                    <h3>Class: <span class="highlight-text"><?= htmlspecialchars($target_class_name) ?></span></h3>
                    <input type="hidden" name="class_id" value="<?= $target_class_id ?>">
                    <input type="date" name="date" value="<?= htmlspecialchars($attendance_date) ?>" required class="date-input-auto" onchange="updateAttendanceDate(<?= $target_class_id ?>, this.value)">
                </div>
                <table class="students-table table-no-margin">
                    <thead><tr><th>Roll No</th><th>Student Name</th><th>Attendance %</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php
                        $counter = 1;
                        $has_existing_data = false;
                        if ($attendance_students) while($row = $attendance_students->fetch_assoc()) {
                            $class_num = preg_replace('/[^0-9]/', '', $target_class_name);
                            $prefix = ($class_num !== '') ? $class_num : $target_class_id;
                            $roll_no = $prefix . str_pad($counter++, 2, '0', STR_PAD_LEFT);
                            $percent = ($row['total_days'] > 0) ? round(($row['present_days'] / $row['total_days']) * 100) : 0;
                            $status = $row['today_status'];
                            if ($status) $has_existing_data = true;
                            $chk_p = ($status === 'Present' || !$status) ? 'checked' : '';
                            $chk_a = ($status === 'Absent') ? 'checked' : '';
                            echo "<tr><td data-label='Roll No'><strong>$roll_no</strong></td><td data-label='Name'>" . htmlspecialchars($row['full_name']) . "</td>
                                  <td data-label='Attendance %'>$percent%</td>
                                  <td data-label='Status' class='status-cell'>
                                    <label class='radio-label-present'><input type='radio' name='attendance[{$row['id']}]' value='Present' $chk_p> <b>P</b></label>
                                    <label class='radio-label-absent'><input type='radio' name='attendance[{$row['id']}]' value='Absent' $chk_a> <b>A</b></label>
                                  </td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <button class="submit-btn grade-submit-btn" type="submit"><?= $has_existing_data ? 'Update Attendance' : 'Submit Attendance' ?></button>
            <?php } else { echo "<p class='access-denied'>You are not assigned as a Class Master.</p>"; } ?>
        </form>

        <!-- Grade Entry Form -->
        <div id="grade" class="form-content">
            <h2>Grades</h2>
            <?php
            $class_options = "";
            while($c = $grade_classes_res->fetch_assoc()) {
                $sel = ($c['id'] == $g_class_id) ? 'selected' : '';
                $class_options .= "<option value='{$c['id']}' $sel>{$c['name']}</option>";
            }

            $subject_options = "";
            if ($grade_subjects_res) {
                while($s = $grade_subjects_res->fetch_assoc()) {
                     $sel = ($s['id'] == $g_subject_id) ? 'selected' : '';
                     $subject_options .= "<option value='{$s['id']}' $sel>{$s['name']}</option>";
                }
            }
            ?>
            <div class="sub-nav-grid">
                <button type="button" class="btn btn-reset" onclick="toggleSubSection('sub_enter_grades')">Enter Grades</button>
                <button type="button" class="btn btn-reset" onclick="toggleSubSection('sub_update_grades')">Update Grades</button>
                <button type="button" class="btn btn-reset" onclick="toggleSubSection('sub_view_grades')">View Grades</button>
            </div>

            <!-- Enter Grades -->
            <div id="sub_enter_grades" class="sub-section" style="display: <?= $active_grade_section == 'enter' ? 'block' : 'none' ?>;">
                <h3>Enter Grades</h3>
                <form action="grade_entry.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="grade_section" value="enter">
                    <div class="filter-ui-container">
                        <div class="form-grid" style="margin-bottom: 1rem;">
                            <div><label class="filter-label">Class:</label><select id="enter_class_select" onchange="updateSubjects(this.value, 'enter')"><option value="">-- Select Class --</option><?= $class_options ?></select></div>
                            <div><label class="filter-label">Subject:</label><select id="enter_subject_select" onchange="loadGrades('enter', 'edit')"><option value="">-- Select Subject --</option><?= $subject_options ?></select></div>
                            <div><label class="filter-label">Term:</label><input type="text" id="enter_term_input" value="<?= htmlspecialchars($g_term) ?>" onblur="loadGrades('enter', 'edit')"></div>
                        </div>
                        <button type="button" class="btn btn-reset btn-full-width" onclick="loadGrades('enter', 'edit')">Load Student List</button>
                    </div>
                    <input type="hidden" id="enter_h_class_id" name="class_id" value="<?= $g_class_id ?>">
                    <input type="hidden" id="enter_h_subject_id" name="subject_id" value="<?= $g_subject_id ?>">
                    <input type="hidden" id="enter_h_term" name="term" value="<?= htmlspecialchars($g_term) ?>">
                    <table class="students-table table-no-margin"><thead><tr><th>Roll No</th><th>Name</th><th>Father Name</th><th>Score</th></tr></thead><tbody id="enter_grade_table_body"><?php if ($g_class_id > 0 && $active_grade_section == 'enter') include 'fetch_grades_inline.php'; ?></tbody></table>
                    <button id="enter_grade_submit_btn" class="submit-btn grade-submit-btn" type="submit" style="display: <?= ($g_class_id > 0 && $active_grade_section == 'enter') ? 'block' : 'none' ?>;">Save Grades</button>
                </form>
            </div>

            <!-- Update Grades -->
            <div id="sub_update_grades" class="sub-section" style="display: <?= $active_grade_section == 'update' ? 'block' : 'none' ?>;">
                <h3>Update Grades</h3>
                <form action="grade_entry.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="grade_section" value="update">
                    <div class="filter-ui-container">
                        <div class="form-grid" style="margin-bottom: 1rem;">
                            <div><label class="filter-label">Class:</label><select id="update_class_select" onchange="updateSubjects(this.value, 'update')"><option value="">-- Select Class --</option><?= $class_options ?></select></div>
                            <div><label class="filter-label">Subject:</label><select id="update_subject_select" onchange="loadGrades('update', 'edit')"><option value="">-- Select Subject --</option><?= $subject_options ?></select></div>
                            <div><label class="filter-label">Term:</label><input type="text" id="update_term_input" value="<?= htmlspecialchars($g_term) ?>" onblur="loadGrades('update', 'edit')"></div>
                        </div>
                        <button type="button" class="btn btn-reset btn-full-width" onclick="loadGrades('update', 'edit')">Load Student List</button>
                    </div>
                    <input type="hidden" id="update_h_class_id" name="class_id" value="<?= $g_class_id ?>">
                    <input type="hidden" id="update_h_subject_id" name="subject_id" value="<?= $g_subject_id ?>">
                    <input type="hidden" id="update_h_term" name="term" value="<?= htmlspecialchars($g_term) ?>">
                    <table class="students-table table-no-margin"><thead><tr><th>Roll No</th><th>Name</th><th>Father Name</th><th>Score</th></tr></thead><tbody id="update_grade_table_body"><?php if ($g_class_id > 0 && $active_grade_section == 'update') include 'fetch_grades_inline.php'; ?></tbody></table>
                    <button id="update_grade_submit_btn" class="submit-btn grade-submit-btn" type="submit" style="display: <?= ($g_class_id > 0 && $active_grade_section == 'update') ? 'block' : 'none' ?>;">Update Grades</button>
                </form>
            </div>

            <!-- View Grades -->
            <div id="sub_view_grades" class="sub-section" style="display: <?= $active_grade_section == 'view' ? 'block' : 'none' ?>;">
                <h3>View Grades</h3>
                <div class="filter-ui-container">
                    <div class="form-grid" style="margin-bottom: 1rem;">
                        <div><label class="filter-label">Class:</label><select id="view_class_select" onchange="updateSubjects(this.value, 'view')"><option value="">-- Select Class --</option><?= $class_options ?></select></div>
                        <div><label class="filter-label">Term:</label><input type="text" id="view_term_input" value="<?= htmlspecialchars($g_term) ?>" onblur="loadGrades('view', 'view')"></div>
                    </div>
                    <button type="button" class="btn btn-reset btn-full-width" onclick="loadGrades('view', 'view')">View Grades</button>
                </div>
                <table class="students-table table-no-margin"><thead><tr><th>Roll No</th><th>Name</th><th>Father Name</th><th>Percentage</th><th>DMC</th></tr></thead><tbody id="view_grade_table_body"></tbody></table>
            </div>
        </div>

        <!-- HOMEWORK -->
        <div id="homework" class="form-content">
            <h2>Manage Homework</h2>
            
            <!-- Post Homework Form -->
            <div class="dashboard-card" style="margin-bottom: 2rem;">
                <div class="card-header"><h3>Post New Homework</h3></div>
                <form action="post_homework.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <div class="form-grid">
                        <select name="class_id" required onchange="updateSubjects(this.value, 'hw')">
                            <option value="">Select Class</option>
                            <?php
                            if ($assignments && $assignments->num_rows > 0) {
                                $assignments->data_seek(0);
                                $unique_classes = [];
                                while($row = $assignments->fetch_assoc()) {
                                    if (!in_array($row['class_id'], $unique_classes)) {
                                        echo "<option value='{$row['class_id']}'>{$row['class_name']}</option>";
                                        $unique_classes[] = $row['class_id'];
                                    }
                                }
                            }
                            ?>
                        </select>
                        <select name="subject_id" id="hw_subject_select" required>
                            <option value="">Select Subject</option>
                            <!-- Populated via AJAX -->
                        </select>
                        <input type="text" name="title" placeholder="Homework Title" required>
                        <input type="date" name="due_date" required>
                    </div>
                    <textarea name="description" placeholder="Homework Description / Instructions" rows="3" style="width:100%; margin-top:1rem; background:rgba(255,255,255,0.05); border:1px solid #333; color:#fff; padding:0.5rem;" required></textarea>
                    <button type="submit" class="submit-btn" style="margin-top:1rem;">Post Homework</button>
                </form>
            </div>

            <!-- Recent Homework List -->
            <h3>Recently Posted</h3>
            <?php if ($homework_res && $homework_res->num_rows > 0): ?>
                <table class="students-table">
                    <thead><tr><th>Class</th><th>Subject</th><th>Title</th><th>Due Date</th></tr></thead>
                    <tbody>
                        <?php while($hw = $homework_res->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($hw['class_name']) ?></td>
                                <td><?= htmlspecialchars($hw['subject_name']) ?></td>
                                <td><?= htmlspecialchars($hw['title']) ?></td>
                                <td><?= $hw['due_date'] ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty-state">No homework posted yet.</p>
            <?php endif; ?>
        </div>

        <!-- TIMETABLE -->
        <div id="timetable" class="form-content">
            <h2>My Teaching Schedule</h2>
            <?php if ($timetable_res && $timetable_res->num_rows > 0): ?>
                <table class="students-table">
                    <thead><tr><th>Day</th><th>Time</th><th>Class</th><th>Subject</th></tr></thead>
                    <tbody>
                        <?php while($tt = $timetable_res->fetch_assoc()): ?>
                            <tr>
                                <td><?= $tt['day_of_week'] ?></td>
                                <td><?= date('h:i A', strtotime($tt['start_time'])) ?> - <?= date('h:i A', strtotime($tt['end_time'])) ?></td>
                                <td><?= htmlspecialchars($tt['class_name']) ?></td>
                                <td><?= htmlspecialchars($tt['subject_name']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty-state">No timetable available.</p>
            <?php endif; ?>
        </div>
    </div>
    <script src="script.js?v=<?php echo time(); ?>"></script>
    <script src="teacher_dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>