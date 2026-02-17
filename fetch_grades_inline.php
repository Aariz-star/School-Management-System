<?php
// Helper file to render grades inline for index.php
// Assumes $g_class_id, $g_subject_id, $g_term, $conn are available

$sql = "SELECT s.id, s.full_name, g.guardian_name,
        gr.score as current_score
        FROM students s
        LEFT JOIN guardians g ON s.guardian_id = g.id
        LEFT JOIN grades gr ON s.id = gr.student_id AND gr.subject_id = ? AND gr.term = ?
        WHERE s.class_id = ?
        AND s.deleted_at IS NULL
        ORDER BY s.full_name";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isi", $g_subject_id, $g_term, $g_class_id);
$stmt->execute();
$res = $stmt->get_result();
$counter = 1;

// Get class name for roll no generation
$c_name_res = $conn->query("SELECT name FROM classes WHERE id = $g_class_id");
$c_name_row = $c_name_res->fetch_assoc();
$c_name = $c_name_row['name'];

if ($res && $res->num_rows > 0) {
    while($row = $res->fetch_assoc()) {
        $class_num = preg_replace('/[^0-9]/', '', $c_name);
        $prefix = ($class_num !== '') ? $class_num : $g_class_id;
        $roll_no = $prefix . str_pad($counter++, 2, '0', STR_PAD_LEFT);
        
        $val = isset($row['current_score']) ? $row['current_score'] : '';

        echo "<tr>";
        echo "<td data-label='Roll No'><strong>$roll_no</strong></td>";
        echo "<td data-label='Name'>" . htmlspecialchars($row['full_name']) . "</td>";
        echo "<td data-label='Father Name'>" . htmlspecialchars($row['guardian_name'] ?? '-') . "</td>";
        echo "<td data-label='Score'><input type='number' name='grades[{$row['id']}]' value='$val' min='0' max='100' class='grade-input'></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='4' style='text-align:center; padding: 2rem;'>No students found.</td></tr>";
}
?>