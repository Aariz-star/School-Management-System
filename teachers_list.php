<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit; }
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
include 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teachers Directory - Ideal Model School</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body>

    <!-- Header -->
    <header class="header" style="padding: 2rem;">
        <div class="header-overlay" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1>Teachers Directory</h1>
                <p>Staff Management</p>
            </div>
            <a href="index.php" class="menu-toggle" style="text-decoration: none; margin-top: 0;">Back to Dashboard</a>
        </div>
    </header>

    <div class="container" style="margin-top: 2rem;">
        <?php
        $sql = "SELECT t.id, t.name, t.father_name, t.salary, t.phone, t.email, t.remaining_payment
                FROM teachers t
                WHERE t.deleted_at IS NULL
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
                                      AND s.deleted_at IS NULL
                                      ORDER BY s.name";
                        $subject_result = $conn->query($subject_sql);
                        $subjects = [];
                        while ($subj = $subject_result->fetch_assoc()) $subjects[] = htmlspecialchars($subj['name']);
                        echo !empty($subjects) ? implode(", ", $subjects) : "<span class='no-subjects-text'>No subjects assigned</span>";
                        ?>
                    </td>
                    <td data-label="Actions">
                        <a href="teacher_edit.php?id=<?= $row['id'] ?>" class="action-btn edit">Edit</a>
                        <button type="button" data-id="<?= $row['id'] ?>" data-type="teacher" data-csrf="<?= $_SESSION['csrf_token'] ?>" class="action-btn delete delete-btn">Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="no-data-msg">No teachers registered yet.</p>
        <?php endif; ?>
    </div>

    <script src="script.js"></script>
</body>
</html>