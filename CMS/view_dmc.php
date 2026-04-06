<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
$term = isset($_GET['term']) ? trim($_GET['term']) : '';

if ($_SESSION['role'] === 'student' && $_SESSION['related_id'] != $student_id) {
    die("Access Denied: You can only view your own DMC.");
}

if ($student_id <= 0 || empty($term)) {
    die("Invalid Request. Student ID and Term are required.");
}

// Fetch Student Info
$stmt = $conn->prepare("SELECT s.full_name, s.contact_number, g.guardian_name, c.name as class_name 
                        FROM students s 
                        LEFT JOIN guardians g ON s.guardian_id = g.id 
                        LEFT JOIN classes c ON s.class_id = c.id 
                        WHERE s.id = ? AND s.deleted_at IS NULL");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) die("Student not found.");

// Fetch Grades
$g_stmt = $conn->prepare("SELECT s.name as subject, gr.score 
                          FROM grades gr 
                          JOIN subjects s ON gr.subject_id = s.id 
                          WHERE gr.student_id = ? AND gr.term = ?");
$g_stmt->bind_param("is", $student_id, $term);
$g_stmt->execute();
$grades_res = $g_stmt->get_result();

$total_marks = 0;
$obtained_marks = 0;
$subject_count = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DMC - <?= htmlspecialchars($student['full_name']) ?></title>
    <link rel="stylesheet" href="reports.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="dmc-container">
        <div class="header-section">
            <h1>Ideal Model School</h1>
            <p>Detailed Marks Certificate</p>
            <p>Term: <?= htmlspecialchars($term) ?></p>
        </div>

        <div class="student-info">
            <div class="info-group"><label>Student Name:</label><span><?= htmlspecialchars($student['full_name']) ?></span></div>
            <div class="info-group"><label>Father Name:</label><span><?= htmlspecialchars($student['guardian_name']) ?></span></div>
            <div class="info-group"><label>Class:</label><span><?= htmlspecialchars($student['class_name']) ?></span></div>
            <div class="info-group"><label>Roll No:</label><span><?= $student_id ?></span></div>
        </div>

        <table class="marks-table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Total Marks</th>
                    <th>Obtained Marks</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($grades_res->num_rows > 0) {
                    while($g = $grades_res->fetch_assoc()) {
                        $subject_total = 100; // Assuming 100 per subject
                        $total_marks += $subject_total;
                        $obtained_marks += $g['score'];
                        $subject_count++;
                        
                        $remarks = $g['score'] >= 40 ? 'Pass' : 'Fail';
                        
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($g['subject']) . "</td>";
                        echo "<td>$subject_total</td>";
                        echo "<td>{$g['score']}</td>";
                        echo "<td>$remarks</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align:center'>No grades found for this term.</td></tr>";
                }
                ?>
                <tr class="total-row">
                    <td>Total</td>
                    <td><?= $total_marks ?></td>
                    <td><?= $obtained_marks ?></td>
                    <td>
                        <?php 
                        if ($total_marks > 0) {
                            $perc = round(($obtained_marks / $total_marks) * 100, 2);
                            echo $perc . "%";
                        } else {
                            echo "-";
                        }
                        ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="footer-section">
            <div class="signature">Class Teacher</div>
            <div class="signature">Principal</div>
        </div>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <button class="print-btn" onclick="window.print()">Print DMC</button>
        <?php endif; ?>
    </div>
</body>
</html>