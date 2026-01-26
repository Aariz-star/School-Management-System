<?php
// grade_entry.php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $student_id = (int)($_POST['student_id'] ?? 0);
    $subject_id = (int)($_POST['subject_id'] ?? 0);
    $score = intval($_POST['score'] ?? 0);
    $term = trim($_POST['term'] ?? '');
    
    $errors = [];
    
    if ($student_id <= 0)       $errors[] = "Please select a student.";
    if ($subject_id <= 0)       $errors[] = "Please select a subject.";
    if ($score < 0 || $score > 100)    $errors[] = "Score must be between 0 and 100.";
    if (empty($term))           $errors[] = "Term/Semester is required.";
    
    if (empty($errors)) {
        $stmt = $conn->prepare("
            INSERT INTO grades
            (student_id, subject_id, score, term)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("iiis", $student_id, $subject_id, $score, $term);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "✓ Grades entered successfully!";
            $stmt->close();
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['error'] = "✗ Database error: " . $conn->error;
            $stmt->close();
            header("Location: index.php");
            exit;
        }
    } else {
        $_SESSION['error'] = "✗ " . implode("\n✗ ", $errors);
        header("Location: index.php");
        exit;
    }
}
?>
