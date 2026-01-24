<?php
// index.php
session_start();
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
        </div>
        <button class="menu-toggle" onclick="toggleSidebar()">Dashboard</button>
    </header>

    <!-- Sidebar Navigation -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <nav class="nav-container" id="sidebar">
        <div class="sidebar-header">
            <h3>Dashboard Menu</h3>
            <button class="close-btn" onclick="toggleSidebar()">&times;</button>
        </div>
        <button class="nav-btn active"  onclick="showForm('student'); toggleSidebar()">Student Registration</button>
        <button class="nav-btn"         onclick="showForm('add_teacher'); toggleSidebar()">Add Teacher</button>
        <button class="nav-btn"         onclick="showForm('add_class'); toggleSidebar()">Classes & Subjects</button>
        <button class="nav-btn"         onclick="showForm('teacher'); toggleSidebar()">Teacher Assignments</button>
        <button class="nav-btn"         onclick="showForm('attendance'); toggleSidebar()">Attendance</button>
        <button class="nav-btn"         onclick="showForm('grade'); toggleSidebar()">Grade Entry</button>
        <button class="nav-btn"         onclick="showForm('fee'); toggleSidebar()">Fee Management</button>
    </nav>

    <!-- Forms Container -->
    <div class="container">

        <!-- ──────────────────────────────────────────────── -->
        <!-- Student Registration Form                        -->
        <!-- ──────────────────────────────────────────────── -->
        <form id="student" class="form-content active" action="student_register.php" method="post">
            <h2>Student Registration</h2>
            <div class="form-grid">
                <input type="text" name="full_name" placeholder="Full Name" required>
                <input type="date" name="admission_date" required>
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
                <textarea name="guardian_address" placeholder="Guardian Address" rows="1" style="min-height: 50px;"></textarea>
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
            
            <!-- ──────────────────────────────────────────────── -->
            <!-- SUBJECTS MANAGEMENT SECTION                      -->
            <!-- ──────────────────────────────────────────────── -->
            <div style="background: rgba(255, 255, 255, 0.03); padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; border: 1px solid rgba(0, 212, 255, 0.1);">
                <h3 style="color: #00d4ff; margin-bottom: 1rem; border-bottom: 1px solid #333; padding-bottom: 0.5rem;">Subject Management</h3>
                
                <!-- Add Subject -->
                <form action="add_subject.php" method="post" style="margin-bottom: 1.5rem;">
                    <div class="form-grid" style="grid-template-columns: 2fr 1fr; gap: 1rem; align-items: end;">
                        <input type="text" name="subject_name" placeholder="New Subject Name (e.g. Computer Science)" required>
                        <button class="submit-btn" type="submit" style="margin-top: 0;">Add Subject</button>
                    </div>
                </form>

                <!-- Delete Subject -->
                <form action="delete_subject.php" method="post" onsubmit="return confirm('⚠️ WARNING: Delete this subject?\n\nThis will remove it from ALL classes and teachers.');">
                    <div class="form-grid" style="grid-template-columns: 2fr 1fr; gap: 1rem; align-items: end;">
                        <select name="subject_id" required>
                            <option value="">Select Subject to Delete</option>
                            <?php
                            $res = $conn->query("SELECT id, name FROM subjects ORDER BY name");
                            while($row = $res->fetch_assoc()) {
                                echo "<option value='{$row['id']}'>{$row['name']}</option>";
                            }
                            ?>
                        </select>
                        <button class="submit-btn" type="submit" style="margin-top: 0; background: linear-gradient(135deg, #ff4444 0%, #cc0000 100%); border-color: #ff4444;">Delete Subject</button>
                    </div>
                </form>
            </div>

            <!-- ──────────────────────────────────────────────── -->
            <!-- CLASS MANAGEMENT SECTION                         -->
            <!-- ──────────────────────────────────────────────── -->
            <!-- Add Class Section -->
            <form action="add_classes.php" method="post" style="margin-bottom: 3rem;">
                <h3 style="color: #00d4ff; margin-bottom: 1rem; border-bottom: 1px solid #333; padding-bottom: 0.5rem;">Class Management</h3>
                <h4 style="color: #e0e0e0; margin-bottom: 0.5rem; font-size: 0.9rem; text-transform: uppercase;">Create New Class</h4>
                <div class="form-grid">
                    <input type="text" name="name" placeholder="Class Name (e.g. Play Group, 1st, 2nd)" required>
                    
                    <div class="form-full-width">
                        <label class="subjects-label">Select Subjects taught in this Class:</label>
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
                                echo "<p class='no-subjects-msg'>No subjects found.</p>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <button class="submit-btn" type="submit">Add Class</button>
            </form>

            <!-- Drop Class & Unlink Subject Section -->
            <form action="delete_class.php" method="post" onsubmit="return confirm('⚠️ WARNING: Are you sure you want to delete this class?\n\nThis will delete the class record permanently.');">
                <h4 style="color: #ff4444; margin-bottom: 0.5rem; font-size: 0.9rem; text-transform: uppercase; margin-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem;">Drop Entire Class</h4>
                <div class="form-grid">
                    <select name="class_id" required style="border-color: #ff4444;">
                        <option value="">Select Class to Drop</option>
                        <?php
                        $res = $conn->query("SELECT id, name FROM classes ORDER BY name");
                        while($row = $res->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <button class="submit-btn" type="submit" style="background: linear-gradient(135deg, #ff4444 0%, #cc0000 100%); border-color: #ff4444;">Drop Class</button>
            </form>

            <!-- Remove Subject from Class -->
            <form action="delete_class_subject.php" method="post" style="margin-top: 2rem;">
                <h4 style="color: #ff4444; margin-bottom: 0.5rem; font-size: 0.9rem; text-transform: uppercase; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem;">Remove Subject from Class</h4>
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
                <button class="submit-btn" type="submit" style="background: linear-gradient(135deg, #ff4444 0%, #cc0000 100%); border-color: #ff4444;">Unlink Subject</button>
            </form>
        </div>

        <!-- ──────────────────────────────────────────────── -->
        <!-- Teacher Assignment Form                          -->
        <!-- ──────────────────────────────────────────────── -->
        <form id="teacher" class="form-content" action="teacher_assign.php" method="post">
            <h2>Teacher Assignments</h2>
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
            <button class="submit-btn" type="submit">Assign Teacher</button>
        </form>

        <!-- ──────────────────────────────────────────────── -->
        <!-- Attendance Form                                  -->
        <!-- ──────────────────────────────────────────────── -->
        <form id="attendance" class="form-content" action="attendance_record.php" method="post">
            <h2>Mark Attendance</h2>
            <div class="form-grid">
                <input type="date" name="date" required>
                <select name="student_id" required>
                    <option value="">Select Student</option>
                    <?php
                    $res = $conn->query("SELECT id, full_name FROM students ORDER BY full_name");
                    while($row = $res->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['full_name']}</option>";
                    }
                    ?>
                </select>
                <select name="status" required>
                    <option value="">Mark Attendance</option>
                    <option value="Present">Present</option>
                    <option value="Absent">Absent</option>
                </select>
            </div>
            <button class="submit-btn" type="submit">Record Attendance</button>
        </form>

        <!-- ──────────────────────────────────────────────── -->
        <!-- Grade Entry Form                                 -->
        <!-- ──────────────────────────────────────────────── -->
        <form id="grade" class="form-content" action="grade_entry.php" method="post">
            <h2>Grade Entry</h2>
            <div class="form-grid">
                <select name="student_id" required>
                    <option value="">Select Student</option>
                    <?php
                    $res = $conn->query("SELECT id, full_name FROM students ORDER BY full_name");
                    while($row = $res->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['full_name']}</option>";
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
                <input type="number" name="score" placeholder="Score (0-100)" min="0" max="100" required>
                <input type="text" name="term" placeholder="Term (e.g. 1st, 2nd)" required>
            </div>
            <button class="submit-btn" type="submit">Enter Grades</button>
        </form>

        <!-- ──────────────────────────────────────────────── -->
        <!-- Fee Management Form                              -->
        <!-- ──────────────────────────────────────────────── -->
        <form id="fee" class="form-content" action="fee_management.php" method="post">
            <h2>Fee Management</h2>
            <div class="form-grid">
                <select name="student_id" required>
                    <option value="">Select Student</option>
                    <?php
                    $res = $conn->query("SELECT id, full_name FROM students ORDER BY full_name");
                    while($row = $res->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['full_name']}</option>";
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

    <!-- Students List -->
    <div class="container students-section">
        <h2>Registered Students</h2>

        <?php
        $sql = "SELECT s.id, s.full_name, s.email, s.contact_number,
                       g.guardian_name, g.contact_number AS guardian_contact,
                       DATE_FORMAT(s.admission_date, '%d-%m-%Y') AS adm_date,
                       c.name AS class_name
                FROM students s
                LEFT JOIN classes c ON s.class_id = c.id
                LEFT JOIN guardians g ON s.guardian_id = g.id
                ORDER BY s.id DESC";

        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0):
        ?>
        <table class="students-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Class</th>
                    <th>Guardian Name</th>
                    <th>Guardian Contact</th>
                    <th>Email</th>
                    <th>Admission</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['class_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['guardian_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($row['guardian_contact'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= $row['adm_date'] ?></td>
                    <td>
                                <a href="student_edit.php?id=<?= $row['id'] ?>" class="action-btn edit">Edit</a>
                                <button type="button" data-id="<?= $row['id'] ?>" data-type="student" class="action-btn delete delete-btn">Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="no-data-msg">No students registered yet.</p>
        <?php endif; ?>

    </div>

    <!-- Teachers List -->
    <div class="container teachers-section">
        <div class="teachers-header">
            <h2>Teachers Directory</h2>
            <button class="nav-btn" onclick="toggleTeachersList()">Show All Teachers</button>
        </div>

        <div id="teachers-list-container" class="teachers-list-hidden">
        <?php
        $sql = "SELECT t.id, t.name, t.father_name, t.salary, t.phone, t.email, t.remaining_payment
                FROM teachers t
                ORDER BY t.id DESC";

        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0):
        ?>
        <table class="students-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Father Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Salary</th>
                    <th>Remaining Payment</th>
                    <th>Subjects</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['father_name']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td>Rs. <?= number_format($row['salary'], 2) ?></td>
                    <td>Rs. <?= number_format($row['remaining_payment'], 2) ?></td>
                    <td>
                        <?php
                        // Get subjects for this teacher
                        $subject_sql = "SELECT s.name FROM subjects s
                                      INNER JOIN teacher_subjects ts ON s.id = ts.subject_id
                                      WHERE ts.teacher_id = {$row['id']}
                                      ORDER BY s.name";
                        $subject_result = $conn->query($subject_sql);
                        
                        if ($subject_result && $subject_result->num_rows > 0) {
                            $subjects = [];
                            while ($subj = $subject_result->fetch_assoc()) {
                                $subjects[] = htmlspecialchars($subj['name']);
                            }
                            echo implode(", ", $subjects);
                        } else {
                            echo "<span class='no-subjects-text'>No subjects assigned</span>";
                        }
                        ?>
                    </td>
                    <td>
                                <a href="teacher_edit.php?id=<?= $row['id'] ?>" class="action-btn edit">Edit</a>
                                <button type="button" data-id="<?= $row['id'] ?>" data-type="teacher" class="action-btn delete delete-btn">Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="no-data-msg">No teachers registered yet.</p>
        <?php endif; ?>
        </div><!-- end teachers-list-container -->

    </div>

    <script src="script.js"></script>
</body>
</html>

<?php $conn->close(); ?>