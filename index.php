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
    <link rel="stylesheet" href="styles.css">
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
    </header>

    <!-- Navigation Tabs -->
    <nav class="nav-container">
        <button class="nav-btn active"  onclick="showForm('student')">Student Registration</button>
        <button class="nav-btn"         onclick="showForm('add_teacher')">Add Teacher</button>
        <button class="nav-btn"         onclick="showForm('add_class')">Add Class</button>
        <button class="nav-btn"         onclick="showForm('teacher')">Teacher Assignments</button>
        <button class="nav-btn"         onclick="showForm('attendance')">Attendance</button>
        <button class="nav-btn"         onclick="showForm('grade')">Grade Entry</button>
        <button class="nav-btn"         onclick="showForm('fee')">Fee Management</button>
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
                <input type="text" name="guardian_name" placeholder="Parent/Guardian Name" required>
                <input type="tel"  name="contact_number" placeholder="Contact Number" required>
                <input type="email" name="email" placeholder="Email Address" required>
                <select name="class_id" required>
                    <option value="">Select Class / Grade</option>
                    <?php
                    $res = $conn->query("SELECT id, name FROM classes ORDER BY name");
                    while($row = $res->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['name']}</option>";
                    }
                    ?>
                </select>
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
        <form id="add_class" class="form-content" action="add_classes.php" method="post">
            <h2>Add Class / Grade</h2>
            <div class="form-grid">
                <input type="text" name="name" placeholder="Class Name (e.g. Play Group, 1st, 2nd)" required>
            </div>
            <button class="submit-btn" type="submit">Add Class</button>
        </form>

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
        $sql = "SELECT s.id, s.full_name, s.email, s.guardian_name, s.contact_number,
                       DATE_FORMAT(s.admission_date, '%d-%m-%Y') AS adm_date,
                       c.name AS class_name
                FROM students s
                LEFT JOIN classes c ON s.class_id = c.id
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
                    <th>Guardian</th>
                    <th>Contact</th>
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
                    <td><?= htmlspecialchars($row['guardian_name']) ?></td>
                    <td><?= htmlspecialchars($row['contact_number']) ?></td>
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