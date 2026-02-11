<?php
session_start();
include 'config.php';

$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;
$term = isset($_GET['term']) ? trim($_GET['term']) : '';
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'edit';

if ($class_id <= 0) {
    echo "";
    exit;
}

// Determine if we are in "View Percentage" mode (View mode with no subject selected)
$view_percentage_mode = ($mode === 'view' && $subject_id <= 0);

if ($view_percentage_mode) {
    // Fetch students and their total score for the term
    // We will calculate percentage based on total subjects in the class
    $sql = "SELECT s.id, s.full_name, g.guardian_name, SUM(gr.score) as total_score
            FROM students s
            LEFT JOIN guardians g ON s.guardian_id = g.id
            LEFT JOIN grades gr ON s.id = gr.student_id AND gr.term = '$term'
            WHERE s.class_id = $class_id
            GROUP BY s.id
            ORDER BY s.full_name";
            
    // Get total subjects count for the class to calculate max marks
    $subj_count_res = $conn->query("SELECT COUNT(*) as cnt FROM class_subjects WHERE class_id = $class_id");
    $total_subjects = ($subj_count_res && $row = $subj_count_res->fetch_assoc()) ? (int)$row['cnt'] : 0;
} else {
    // Fetch students and their score for a specific subject
    $sql = "SELECT s.id, s.full_name, g.guardian_name,
            gr.score as current_score
            FROM students s
            LEFT JOIN guardians g ON s.guardian_id = g.id
            LEFT JOIN grades gr ON s.id = gr.student_id AND gr.subject_id = $subject_id AND gr.term = '$term'
            WHERE s.class_id = $class_id
            ORDER BY s.full_name";
}

$res = $conn->query($sql);
$counter = 1;

// Get class name for roll no generation
$c_name_res = $conn->query("SELECT name FROM classes WHERE id = $class_id");
$c_name_row = $c_name_res->fetch_assoc();
$c_name = $c_name_row['name'] ?? '';

if ($res && $res->num_rows > 0) {
    while($row = $res->fetch_assoc()) {
        // Roll No Logic
        $class_num = preg_replace('/[^0-9]/', '', $c_name);
        $prefix = ($class_num !== '') ? $class_num : $class_id;
        $roll_no = $prefix . str_pad($counter++, 2, '0', STR_PAD_LEFT);
        
        $val = isset($row['current_score']) ? $row['current_score'] : '';

        echo "<tr>";
        echo "<td data-label='Roll No'><strong>$roll_no</strong></td>";
        echo "<td data-label='Name'>" . htmlspecialchars($row['full_name']) . "</td>";
        echo "<td data-label='Father Name'>" . htmlspecialchars($row['guardian_name'] ?? '-') . "</td>";
        echo "<td data-label='Score'>";
        if ($mode === 'view') {
            if ($view_percentage_mode) {
                $obtained = isset($row['total_score']) ? (int)$row['total_score'] : 0;
                $max_marks = $total_subjects * 100;
                if ($max_marks > 0) {
                    echo round(($obtained / $max_marks) * 100, 2) . "%";
                } else {
                    echo "N/A";
                }
            } else {
                echo ($val !== '' ? $val : '-');
            }
        } else {
            echo "<input type='number' name='grades[{$row['id']}]' value='$val' min='0' max='100' style='width: 80px; padding: 0.5rem; border: 1px solid #333; border-radius: 4px; background: #1a1a1a; color: #fff;'>";
        }
        echo "</td>";
        
        if ($mode === 'view') {
            echo "<td data-label='DMC'>";
            if (!empty($term)) {
                echo "<a href='view_dmc.php?student_id={$row['id']}&term=" . urlencode($term) . "' target='_blank' class='action-btn edit' style='text-decoration:none; font-size: 0.8rem; display:inline-block; text-align:center;'>View DMC</a>";
            } else {
                echo "<span style='color:#666; font-size:0.8rem; font-style:italic;'>Enter Term</span>";
            }
            echo "</td>";
        }
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='4' style='text-align:center; padding: 2rem;'>No students found.</td></tr>";
}
?>