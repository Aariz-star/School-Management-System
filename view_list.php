<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit; }
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
include 'config.php';

$view = isset($_GET['view']) && $_GET['view'] === 'teachers' ? 'teachers' : 'students';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ucfirst($view) ?> List - Ideal Model School</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body>

    <!-- Header -->
    <header class="header view-list-header">
        <div class="header-overlay view-list-header-overlay">
            <div>
                <h1><?= $view === 'teachers' ? 'Teachers Directory' : 'Registered Students' ?></h1>
                <p><?= $view === 'teachers' ? 'Staff Management' : 'Full Directory' ?></p>
            </div>
            <a href="index.php" class="menu-toggle" style="text-decoration: none; margin-top: 0;">Back to Dashboard</a>
        </div>
    </header>

    <div class="container view-list-container">
        <?php if ($view === 'students'): ?>
            <!-- ──────────────────────────────────────────────── -->
            <!-- STUDENTS TABLE                                   -->
            <!-- ──────────────────────────────────────────────── -->
            <?php
            // Filter Logic
            $selected_class = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
            $status = isset($_GET['status']) && $_GET['status'] === 'trash' ? 'trash' : 'active';
            ?>
            
            <div class="sub-nav-grid" style="margin-bottom: 1.5rem; display: flex; gap: 1rem;">
                <a href="view_list.php?view=students&class_id=<?= $selected_class ?>&status=active" class="btn btn-reset" style="<?= $status === 'active' ? 'background: #00d4ff; color: #000;' : '' ?>">Active Students</a>
                <a href="view_list.php?view=students&class_id=<?= $selected_class ?>&status=trash" class="btn btn-reset" style="<?= $status === 'trash' ? 'background: #ef4444; color: #fff;' : '' ?>">Recycle Bin 🗑️</a>
            </div>
            
            <form method="get" class="filter-form">
                <input type="hidden" name="view" value="students">
                <input type="hidden" name="status" value="<?= $status ?>">
                <label style="color: #00d4ff; font-weight: bold;">Filter by Class:</label>
                <select name="class_id" onchange="this.form.submit()" class="filter-select">
                    <option value="">-- Select Class --</option>
                    <?php
                    $class_res = $conn->query("SELECT id, name FROM classes WHERE deleted_at IS NULL ORDER BY name");
                    while($c = $class_res->fetch_assoc()) {
                        $selected = ($c['id'] == $selected_class) ? 'selected' : '';
                        echo "<option value='{$c['id']}' $selected>{$c['name']}</option>";
                    }
                    ?>
                </select>
            </form>

            <?php
            $result = null;
            
            if ($selected_class > 0) {
                // Only show students with status='active'. Inactive students are hidden completely.
                $where_clause = ($status === 'trash') ? "s.deleted_at IS NOT NULL AND s.status = 'active'" : "s.deleted_at IS NULL AND s.status = 'active'";
                $sql = "SELECT s.id, s.full_name, s.email, s.contact_number,
                               g.guardian_name, g.contact_number AS guardian_contact,
                               DATE_FORMAT(s.admission_date, '%d-%m-%Y') AS adm_date,
                               c.name AS class_name
                        FROM students s
                        LEFT JOIN classes c ON s.class_id = c.id
                        LEFT JOIN guardians g ON s.guardian_id = g.id
                        WHERE $where_clause AND s.class_id = $selected_class
                        ORDER BY s.full_name";
                $result = $conn->query($sql);
            }
            ?>
            
            <?php if ($result && $result->num_rows > 0): ?>
            <table class="students-table">
                <thead>
                    <tr>
                        <th>Roll No</th>
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
                <?php 
                $counter = 1;
                while($row = $result->fetch_assoc()): 
                    $class_name = $row['class_name'] ?? '';
                    $class_num = preg_replace('/[^0-9]/', '', $class_name);
                    $prefix = ($class_num !== '') ? $class_num : $selected_class;
                    $roll_no = $prefix . str_pad($counter++, 2, '0', STR_PAD_LEFT);
                ?>
                    <tr>
                        <td data-label="Roll No"><?= $roll_no ?></td>
                        <td data-label="Name"><?= htmlspecialchars($row['full_name']) ?></td>
                        <td data-label="Class"><?= htmlspecialchars($row['class_name'] ?? '-') ?></td>
                        <td data-label="Guardian"><?= htmlspecialchars($row['guardian_name'] ?? 'N/A') ?></td>
                        <td data-label="Contact"><?= htmlspecialchars($row['guardian_contact'] ?? 'N/A') ?></td>
                        <td data-label="Email"><?= htmlspecialchars($row['email']) ?></td>
                        <td data-label="Admission"><?= $row['adm_date'] ?></td>
                        <td data-label="Actions">
                            <?php if ($status === 'active'): ?>
                                <a href="student_edit.php?id=<?= $row['id'] ?>" class="action-btn edit">Edit</a>
                                <button type="button" data-id="<?= $row['id'] ?>" data-type="student" data-csrf="<?= $_SESSION['csrf_token'] ?>" class="action-btn delete delete-btn">Delete</button>
                            <?php else: ?>
                                <form action="student_delete.php" method="post" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="class_id" value="<?= $selected_class ?>">
                                    <input type="hidden" name="action" value="restore">
                                    <button type="submit" class="action-btn edit" style="background: #10b981; border:none; cursor:pointer;">Restore</button>
                                </form>
                                <form action="student_delete.php" method="post" style="display:inline;" onsubmit="return confirm('⚠️ PERMANENT DELETE WARNING\n\nThis action cannot be undone. The student record will be permanently removed from the database.\n\nAre you sure you want to proceed?');">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="class_id" value="<?= $selected_class ?>">
                                    <input type="hidden" name="action" value="permanent_delete">
                                    <button type="submit" class="action-btn delete" style="background: #ef4444; border:none; cursor:pointer;">Delete Forever</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            <?php elseif ($selected_class > 0): ?>
                <p class="no-data-msg">No students found in this class.</p>
            <?php else: ?>
                <p class="no-data-msg">Please select a class to view students.</p>
            <?php endif; ?>

        <?php else: ?>
            <!-- ──────────────────────────────────────────────── -->
            <!-- TEACHERS TABLE                                   -->
            <!-- ──────────────────────────────────────────────── -->
            <?php
            $status = isset($_GET['status']) && $_GET['status'] === 'trash' ? 'trash' : 'active';
            ?>
            
            <div class="sub-nav-grid" style="margin-bottom: 1.5rem; display: flex; gap: 1rem;">
                <a href="view_list.php?view=teachers&status=active" class="btn btn-reset" style="<?= $status === 'active' ? 'background: #00d4ff; color: #000;' : '' ?>">Active Teachers</a>
                <a href="view_list.php?view=teachers&status=trash" class="btn btn-reset" style="<?= $status === 'trash' ? 'background: #ef4444; color: #fff;' : '' ?>">Recycle Bin 🗑️</a>
            </div>

            <?php
            $where_clause = ($status === 'trash') ? "t.deleted_at IS NOT NULL" : "t.deleted_at IS NULL";
            $sql = "SELECT t.id, t.name, t.father_name, t.salary, t.phone, t.email, t.remaining_payment
                    FROM teachers t
                    WHERE $where_clause
                    ORDER BY t.id DESC";
            $result = $conn->query($sql);
            ?>
            
            <?php if ($result && $result->num_rows > 0): ?>
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
                        <td data-label="ID"><?= $row['id'] ?></td>
                        <td data-label="Name"><?= htmlspecialchars($row['name']) ?></td>
                        <td data-label="Father Name"><?= htmlspecialchars($row['father_name']) ?></td>
                        <td data-label="Phone"><?= htmlspecialchars($row['phone']) ?></td>
                        <td data-label="Email"><?= htmlspecialchars($row['email']) ?></td>
                        <td data-label="Salary">Rs. <?= number_format($row['salary'], 2) ?></td>
                        <td data-label="Remaining">Rs. <?= number_format($row['remaining_payment'], 2) ?></td>
                        <td data-label="Subjects">
                            <?php
                            $subject_sql = "SELECT s.name FROM subjects s
                                          INNER JOIN teacher_subjects ts ON s.id = ts.subject_id
                                          WHERE ts.teacher_id = {$row['id']}
                                          ORDER BY s.name";
                            $subject_result = $conn->query($subject_sql);
                            $subjects = [];
                            while ($subj = $subject_result->fetch_assoc()) $subjects[] = htmlspecialchars($subj['name']);
                            echo !empty($subjects) ? implode(", ", $subjects) : "<span class='no-subjects-text'>No subjects assigned</span>";
                            ?>
                        </td>
                        <td data-label="Actions">
                            <?php if ($status === 'active'): ?>
                                <a href="teacher_edit.php?id=<?= $row['id'] ?>" class="action-btn edit">Edit</a>
                                <button type="button" data-id="<?= $row['id'] ?>" data-type="teacher" data-csrf="<?= $_SESSION['csrf_token'] ?>" class="action-btn delete delete-btn">Delete</button>
                            <?php else: ?>
                                <form action="teacher_delete.php" method="post" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="action" value="restore">
                                    <button type="submit" class="action-btn edit" style="background: #10b981; border:none; cursor:pointer;">Restore</button>
                                </form>
                                <form action="teacher_delete.php" method="post" style="display:inline;" onsubmit="return confirm('⚠️ PERMANENT DELETE WARNING\n\nThis action cannot be undone. The teacher record will be permanently removed from the database.\n\nAre you sure you want to proceed?');">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="action" value="permanent_delete">
                                    <button type="submit" class="action-btn delete" style="background: #ef4444; border:none; cursor:pointer;">Delete Forever</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p class="no-data-msg">No teachers registered yet.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="script.js"></script>
</body>
</html>