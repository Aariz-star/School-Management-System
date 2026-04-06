<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['name' => 'Unauthorized', 'html' => '<tr><td colspan="6">Access denied.</td></tr>']);
    exit;
}

$student_id = (int)($_GET['student_id'] ?? 0);
if ($student_id <= 0) {
    echo json_encode(['name' => 'Unknown', 'html' => '<tr><td colspan="6">Invalid student ID.</td></tr>']);
    exit;
}

// 1. Fetch student name & Std ID
$s_stmt = $conn->prepare("SELECT full_name, std_id FROM students WHERE id = ?");
$s_stmt->bind_param("i", $student_id);
$s_stmt->execute();
$s_res = $s_stmt->get_result();
$student_name = "Unknown Student";
if ($row = $s_res->fetch_assoc()) {
    $std_id = $row['std_id'] ? " (" . $row['std_id'] . ")" : "";
    $student_name = $row['full_name'] . $std_id;
}
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

$html = "";
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $att_percent = $row['total_days'] > 0 ? round(($row['present_days'] / $row['total_days']) * 100) : 0;
        $att_str = $row['total_days'] > 0 ? "{$att_percent}% ({$row['present_days']}/{$row['total_days']})" : "N/A";
        $mid = $row['mid_avg'] !== null ? round($row['mid_avg'], 1) . "%" : "N/A";
        $fin = $row['final_avg'] !== null ? round($row['final_avg'], 1) . "%" : "N/A";

        // Status badge colors
        $status_color = ($row['status'] === 'Promoted') ? '#10b981' : (($row['status'] === 'Failed') ? '#ef4444' : '#00d4ff');

        $html .= "<tr><td data-label='Year'><strong>{$row['academic_year']}</strong></td><td data-label='Class'>{$row['class_name']}</td><td data-label='Attendance'>{$att_str}</td><td data-label='Midterm'>{$mid}</td><td data-label='Final'>{$fin}</td><td data-label='Status'><span style='color: {$status_color}; font-weight:bold;'>{$row['status']}</span></td></tr>";
    }
} else {
    $html .= "<tr><td colspan='6' style='text-align:center; padding:2rem; color:#999;'>No historical enrollment records found yet.</td></tr>";
}

$stmt->close();
echo json_encode(['name' => $student_name, 'html' => $html]);