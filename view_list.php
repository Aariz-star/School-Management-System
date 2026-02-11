<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
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
    <header class="header" style="padding: 2rem;">
        <div class="header-overlay" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1><?= $view === 'teachers' ? 'Teachers Directory' : 'Registered Students' ?></h1>
                <p><?= $view === 'teachers' ? 'Staff Management' : 'Full Directory' ?></p>
            </div>
            <a href="index.php" class="menu-toggle" style="text-decoration: none; margin-top: 0;">Back to Dashboard</a>
        </div>
    </header>

    <div class="container" style="margin-top: 2rem;">
        <?php if ($view === 'students'): ?>
            <!-- ──────────────────────────────────────────────── -->
            <!-- STUDENTS TABLE                                   -->
            <!-- ──────────────────────────────────────────────── -->
            <?php
            // Filter Logic
            $selected_class = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
            ?>
            
            <form method="get" style="margin-bottom: 1.5rem; background: rgba(255,255,255,0.05); padding: 1rem; border-radius: 8px; display: flex; align-items: center; gap: 1rem;">
                <input type="hidden" name="view" value="students">
                <label style="color: #00d4ff; font-weight: bold;">Filter by Class:</label>
                <select name="class_id" onchange="this.form.submit()" style="padding: 0.5rem; border-radius: 4px; border: 1px solid #333; background: #1a1a1a; color: #e0e0e0;">
                    <option value="">Show All Students</option>
                    <?php
                    $class_res = $conn->query("SELECT id, name FROM classes ORDER BY name");
                    while($c = $class_res->fetch_assoc()) {
                        $selected = ($c['id'] == $selected_class) ? 'selected' : '';
                        echo "<option value='{$c['id']}' $selected>{$c['name']}</option>";
                    }
                    ?>
                </select>
            </form>

            <?php
            $sql = "SELECT s.id, s.full_name, s.email, s.contact_number,
                           g.guardian_name, g.contact_number AS guardian_contact,
                           DATE_FORMAT(s.admission_date, '%d-%m-%Y') AS adm_date,
                           c.name AS class_name
                    FROM students s
                    LEFT JOIN classes c ON s.class_id = c.id
                    LEFT JOIN guardians g ON s.guardian_id = g.id
                    ";
            
            if ($selected_class > 0) {
                $sql .= " WHERE s.class_id = $selected_class ";
                $sql .= " ORDER BY s.id DESC";
            } else {
                // Performance: Limit default view to 100 recent students if no class selected
                $sql .= " ORDER BY s.id DESC LIMIT 100";
            }
            
            $result = $conn->query($sql);
            ?>
            
            <?php if ($result && $result->num_rows > 0): ?>
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
                        <td data-label="ID"><?= $row['id'] ?></td>
                        <td data-label="Name"><?= htmlspecialchars($row['full_name']) ?></td>
                        <td data-label="Class"><?= htmlspecialchars($row['class_name'] ?? '-') ?></td>
                        <td data-label="Guardian"><?= htmlspecialchars($row['guardian_name'] ?? 'N/A') ?></td>
                        <td data-label="Contact"><?= htmlspecialchars($row['guardian_contact'] ?? 'N/A') ?></td>
                        <td data-label="Email"><?= htmlspecialchars($row['email']) ?></td>
                        <td data-label="Admission"><?= $row['adm_date'] ?></td>
                        <td data-label="Actions">
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

        <?php else: ?>
            <!-- ──────────────────────────────────────────────── -->
            <!-- TEACHERS TABLE                                   -->
            <!-- ──────────────────────────────────────────────── -->
            <?php
            $sql = "SELECT t.id, t.name, t.father_name, t.salary, t.phone, t.email, t.remaining_payment
                    FROM teachers t
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
        <?php endif; ?>
    </div>

    <script src="script.js"></script>
</body>
</html>