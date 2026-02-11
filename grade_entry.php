<?php
// grade_entry.php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $class_id = isset($_POST['class_id']) ? (int)$_POST['class_id'] : 0;
    $subject_id = isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : 0;
    $term = isset($_POST['term']) ? trim($_POST['term']) : '';
    $grades = isset($_POST['grades']) ? $_POST['grades'] : [];
    $grade_section = isset($_POST['grade_section']) ? $_POST['grade_section'] : 'enter';

    if ($class_id <= 0 || $subject_id <= 0 || empty($term)) {
        $_SESSION['error'] = "Missing Class, Subject, or Term information.";
        header("Location: index.php");
        exit;
    }

    // Prepare statements
    $check_stmt = $conn->prepare("SELECT id FROM grades WHERE student_id = ? AND subject_id = ? AND term = ?");
    $insert_stmt = $conn->prepare("INSERT INTO grades (student_id, subject_id, term, score) VALUES (?, ?, ?, ?)");
    $update_stmt = $conn->prepare("UPDATE grades SET score = ? WHERE id = ?");

    foreach ($grades as $student_id => $score) {
        if ($score === '') continue; // Skip empty inputs
        
        $student_id = (int)$student_id;
        $score = (int)$score;

        // Check if grade exists
            $check_stmt->bind_param("iis", $student_id, $subject_id, $term);
            $check_stmt->execute();
            $res = $check_stmt->get_result();

            if ($row = $res->fetch_assoc()) {
                // Update
                $update_stmt->bind_param("ii", $score, $row['id']);
                $update_stmt->execute();
            } else {
                // Insert
                $insert_stmt->bind_param("iisi", $student_id, $subject_id, $term, $score);
                $insert_stmt->execute();
            }
    }
    
    $_SESSION['success'] = "Grades saved successfully!";
    $redirect_page = ($_SESSION['role'] === 'teacher') ? 'teacher_dashboard.php' : 'index.php';
    header("Location: $redirect_page?grade_class_id=$class_id&grade_subject_id=$subject_id&grade_term=" . urlencode($term) . "&grade_section=$grade_section");
    exit;
}
?>
