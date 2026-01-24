<?php
// teacher_assign.php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $teacher_id = (int)($_POST['teacher_id'] ?? 0);
    $subject_id = (int)($_POST['subject_id'] ?? 0);
    $class_id = (int)($_POST['class_id'] ?? 0);
    $academic_year = trim($_POST['academic_year'] ?? '');
    
    $errors = [];
    
    if ($teacher_id <= 0)       $errors[] = "Please select a teacher.";
    if ($subject_id <= 0)       $errors[] = "Please select a subject.";
    if ($class_id <= 0)         $errors[] = "Please select a class.";
    if (empty($academic_year))  $errors[] = "Academic year is required.";
    
    if (empty($errors)) {
        $stmt = $conn->prepare("
            INSERT INTO teacher_assignments
            (teacher_id, subject_id, class_id, academic_year)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("iiis", $teacher_id, $subject_id, $class_id, $academic_year);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "✓ Teacher assigned successfully!";
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
