<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    exit;
}

$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;

if ($class_id <= 0) {
    echo '<p class="empty-state">Invalid class selected.</p>';
    exit;
}

// Fetch class details
$class_res = $conn->query("SELECT name FROM classes WHERE id = $class_id AND deleted_at IS NULL");
if (!$class_res || $class_res->num_rows == 0) {
    echo '<p class="empty-state">Class not found.</p>';
    exit;
}
$class = $class_res->fetch_assoc();

// Fetch subjects assigned to this class
$sql = "SELECT 
        cs.class_id,
        cs.subject_id,
        s.name as subject_name,
        cs.book_name
        FROM class_subjects cs
        JOIN subjects s ON cs.subject_id = s.id
        WHERE cs.class_id = $class_id AND s.deleted_at IS NULL AND cs.deleted_at IS NULL
        ORDER BY s.name";

$res = $conn->query($sql);

if (!$res) {
    echo '<p style="color: #ef4444;">Database Error: ' . htmlspecialchars($conn->error) . '</p>';
    exit;
}

if ($res && $res->num_rows > 0) {
    echo '<table class="students-table subjects-details-table" style="width: 100%; margin-bottom: 2rem;">';
    echo '<thead><tr>';
    echo '<th>Subject Name</th>';
    echo '<th>Book Name</th>';
    echo '<th>Assigned Teacher(s)</th>';
    echo '<th class="action-column">Action</th>';
    echo '</tr></thead>';
    echo '<tbody>';
    
    while ($row = $res->fetch_assoc()) {
        $row_class_id = $row['class_id'];
        $row_subject_id = $row['subject_id'];
        $subject_name = htmlspecialchars($row['subject_name']);
        $book_name = htmlspecialchars($row['book_name'] ?? '-');
        
        // Get assigned teachers for this subject in this class using teacher_assignments table
        $teacher_sql = "SELECT GROUP_CONCAT(DISTINCT t.name SEPARATOR ', ') as teachers
                       FROM teacher_assignments ta
                       JOIN teachers t ON ta.teacher_id = t.id
                       WHERE ta.subject_id = $row_subject_id AND ta.class_id = $row_class_id AND t.deleted_at IS NULL";
        $teacher_res = $conn->query($teacher_sql);
        $teachers = 'Not Assigned';
        if ($teacher_res && $teacher_res->num_rows > 0) {
            $teacher_row = $teacher_res->fetch_assoc();
            $teachers = htmlspecialchars($teacher_row['teachers'] ?? 'Not Assigned');
        }
        
        // Create a composite key for removal (class_id:subject_id)
        $removal_key = $row_class_id . ':' . $row_subject_id;
        
        echo '<tr>';
        echo "<td class='subject-cell'>📚 $subject_name</td>";
        echo "<td class='book-cell'>$book_name</td>";
        echo "<td class='teacher-cell'>$teachers</td>";
        echo "<td class='action-cell'>";
        echo "<button type='button' onclick='removeSubjectFromClass(\"$removal_key\")' class='submit-btn btn-danger btn-small'>Remove</button>";
        echo '</td>';
        echo '</tr>';
    }
    
    echo '</tbody></table>';
} else {
    echo '<p class="empty-state">No subjects assigned to this class yet.</p>';
}
?>

