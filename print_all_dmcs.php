<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$term = isset($_GET['term']) ? trim($_GET['term']) : '';

if ($class_id <= 0 || empty($term)) {
    die("Invalid Request. Class ID and Term are required.");
}

// Fetch All Students in Class
$s_stmt = $conn->prepare("SELECT s.id, s.full_name, g.guardian_name, c.name as class_name 
                          FROM students s 
                          LEFT JOIN guardians g ON s.guardian_id = g.id 
                          LEFT JOIN classes c ON s.class_id = c.id 
                          WHERE s.class_id = ? 
                          AND s.deleted_at IS NULL
                          ORDER BY s.full_name");
$s_stmt->bind_param("i", $class_id);
$s_stmt->execute();
$students_res = $s_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print All DMCs</title>
    <link rel="stylesheet" href="reports.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()">Print All DMCs</button>
    </div>

    <?php while($student = $students_res->fetch_assoc()): ?>
        <?php
        // Fetch Grades for this student
        $g_stmt = $conn->prepare("SELECT s.name as subject, gr.score 
                                  FROM grades gr 
                                  JOIN subjects s ON gr.subject_id = s.id 
                                  WHERE gr.student_id = ? AND gr.term = ?");
        $g_stmt->bind_param("is", $student['id'], $term);
        $g_stmt->execute();
        $grades_res = $g_stmt->get_result();
        
        $total_marks = 0;
        $obtained_marks = 0;
        ?>
        
        <div class="dmc-page">
            <div class="header-section">
                <h1>Ideal Model School</h1>
                <p>Detailed Marks Certificate</p>
                <p>Term: <?= htmlspecialchars($term) ?></p>
            </div>

            <div class="student-info">
                <div class="info-group"><label>Student Name:</label><span><?= htmlspecialchars($student['full_name']) ?></span></div>
                <div class="info-group"><label>Father Name:</label><span><?= htmlspecialchars($student['guardian_name']) ?></span></div>
                <div class="info-group"><label>Class:</label><span><?= htmlspecialchars($student['class_name']) ?></span></div>
                <div class="info-group"><label>Roll No:</label><span><?= $student['id'] ?></span></div>
            </div>

            <table class="marks-table">
                <thead><tr><th>Subject</th><th>Total</th><th>Obtained</th><th>Remarks</th></tr></thead>
                <tbody>
                    <?php while($g = $grades_res->fetch_assoc()): 
                        $total_marks += 100; $obtained_marks += $g['score']; ?>
                        <tr><td><?= htmlspecialchars($g['subject']) ?></td><td>100</td><td><?= $g['score'] ?></td><td><?= $g['score'] >= 40 ? 'Pass' : 'Fail' ?></td></tr>
                    <?php endwhile; ?>
                    <tr class="total-row"><td>Total</td><td><?= $total_marks ?></td><td><?= $obtained_marks ?></td><td><?= $total_marks > 0 ? round(($obtained_marks/$total_marks)*100, 2).'%' : '-' ?></td></tr>
                </tbody>
            </table>

            <div class="footer-section"><div class="signature">Class Teacher</div><div class="signature">Principal</div></div>
        </div>
    <?php endwhile; ?>

</body>
</html>