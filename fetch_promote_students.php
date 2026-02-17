<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    exit;
}

$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;

if ($class_id <= 0) {
    echo '<p class="empty-state">Please select a class.</p>';
    exit;
}

$sql = "SELECT 
        s.id, 
        s.full_name, 
        g.guardian_name,
        s.contact_number,
        COALESCE(
            ROUND(
                (SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) * 100) / 
                NULLIF(COUNT(DISTINCT a.id), 0), 
                1
            ), 
            0
        ) AS attendance_percent
        FROM students s 
        LEFT JOIN guardians g ON s.guardian_id = g.id 
        LEFT JOIN attendance a ON s.id = a.student_id
        WHERE s.class_id = ? AND s.deleted_at IS NULL AND s.status = 'active' 
        GROUP BY s.id, s.full_name, g.guardian_name, s.contact_number
        ORDER BY s.full_name";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $class_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    echo '<table class="students-table">';
    echo '<thead><tr>
            <th style="width: 50px;"><input type="checkbox" onchange="toggleAllPromote(this)"></th>
            <th>Student Name</th>
            <th>Father Name</th>
            <th>Attendance %</th>
          </tr></thead>';
    echo '<tbody>';
    while ($row = $res->fetch_assoc()) {
        $father_name = htmlspecialchars($row['guardian_name'] ?? '-');
        $attendance = (int)$row['attendance_percent'];
        $attendance_color = $attendance >= 75 ? '#10b981' : ($attendance >= 50 ? '#f59e0b' : '#ef4444');
        
        echo "<tr>
                <td><input type='checkbox' name='students[]' value='{$row['id']}' class='promote-checkbox'></td>
                <td>" . htmlspecialchars($row['full_name']) . "</td>
                <td>$father_name</td>
                <td><span style='color: $attendance_color; font-weight: bold;'>$attendance%</span></td>
              </tr>";
    }
    echo '</tbody></table>';
} else {
    echo '<p class="empty-state">No active students found in this class.</p>';
}
?>