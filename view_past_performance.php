<?php
session_start();
include 'config.php';

// Security Check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'teacher'])) {
    die("Unauthorized access.");
}

$student_id = (int)($_GET['student_id'] ?? 0);
if ($student_id <= 0) {
    die("Invalid student ID.");
}

// 1. Fetch student info
$s_stmt = $conn->prepare("SELECT full_name, std_id FROM students WHERE id = ?");
$s_stmt->bind_param("i", $student_id);
$s_stmt->execute();
$s_res = $s_stmt->get_result();
if ($s_res->num_rows === 0) {
    die("Student not found.");
}
$student = $s_res->fetch_assoc();
$student_name = $student['full_name'] . ($student['std_id'] ? " (" . $student['std_id'] . ")" : "");
$s_stmt->close();

// 2. Fetch longitudinal history grouped by academic year
$sql = "SELECT 
            e.academic_year, 
            c.name AS class_name,
            e.status,
            (SELECT COUNT(*) FROM attendance WHERE student_id = e.student_id AND academic_year = e.academic_year) as total_days,
            (SELECT COUNT(*) FROM attendance WHERE student_id = e.student_id AND academic_year = e.academic_year AND status = 'Present') as present_days,
            (SELECT AVG(score) FROM grades WHERE student_id = e.student_id AND academic_year = e.academic_year AND term = 'Midterm') as mid_avg,
            (SELECT AVG(score) FROM grades WHERE student_id = e.student_id AND academic_year = e.academic_year AND term = 'Final') as final_avg
        FROM student_enrollments e
        JOIN classes c ON e.class_id = c.id
        WHERE e.student_id = ?
        ORDER BY e.academic_year DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="logo.jpg" type="image/jpeg">
    <title>Past Performance - <?= htmlspecialchars($student_name) ?></title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body { background-color: #0f0f0f; color: #e0e0e0; padding: 2rem; font-family: sans-serif; }
        .performance-container { max-width: 900px; margin: 0 auto; background: #1a1a1a; padding: 2rem; border-radius: 8px; box-shadow: 0 0 15px rgba(0, 212, 255, 0.1); border: 1px solid rgba(0, 212, 255, 0.2); }
        h1 { color: #fff; margin-top: 0; }
        .subtitle { color: #00d4ff; margin-bottom: 2rem; font-size: 1.1rem; }
        .print-btn { background: #8b5cf6; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 4px; cursor: pointer; float: right; font-weight: bold; transition: 0.3s; }
        .print-btn:hover { background: #7c3aed; }
        
        /* Print styling so it doesn't print a black page */
        @media print {
            body { background: white; color: black; padding: 0; }
            .performance-container { box-shadow: none; border: none; padding: 0; }
            h1, .subtitle { color: black; }
            .print-btn { display: none; }
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #ccc !important; padding: 8px; text-align: left; color: black !important; }
            th { background-color: #f2f2f2 !important; color: black !important; font-weight: bold; }
            .status-badge { color: black !important; font-weight: bold; }
        }
    </style>
</head>
<body>
    <div class="performance-container">
        <button class="print-btn" onclick="window.print()">🖨️ Print Report</button>
        <h1><?= htmlspecialchars($student_name) ?></h1>
        <p class="subtitle">Historical Performance Overview</p>
        
        <div class="table-responsive">
            <table class="students-table" style="width: 100%;">
                <thead>
                    <tr><th>Academic Year</th><th>Class</th><th>Attendance</th><th>Midterm Avg</th><th>Final Avg</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php
                    if ($res && $res->num_rows > 0) {
                        while ($row = $res->fetch_assoc()) {
                            $att_percent = $row['total_days'] > 0 ? round(($row['present_days'] / $row['total_days']) * 100) : 0;
                            $att_str = $row['total_days'] > 0 ? "{$att_percent}% ({$row['present_days']}/{$row['total_days']})" : "N/A";
                            $mid = $row['mid_avg'] !== null ? round($row['mid_avg'], 1) . "%" : "N/A";
                            $fin = $row['final_avg'] !== null ? round($row['final_avg'], 1) . "%" : "N/A";
                            $status_color = ($row['status'] === 'Promoted') ? '#10b981' : (($row['status'] === 'Failed') ? '#ef4444' : '#00d4ff');
                            echo "<tr><td><strong>" . htmlspecialchars($row['academic_year']) . "</strong></td><td>" . htmlspecialchars($row['class_name']) . "</td><td>{$att_str}</td><td>{$mid}</td><td>{$fin}</td><td><span class='status-badge' style='color: {$status_color}; font-weight:bold;'>" . htmlspecialchars($row['status']) . "</span></td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center; padding:2rem; color:#999;'>No historical enrollment records found yet.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>