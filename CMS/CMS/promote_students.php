<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    $current_class_id = (int)$_POST['current_class_id'];
    $target_class_id = (int)$_POST['target_class_id'];
    $students = isset($_POST['students']) ? $_POST['students'] : [];

    if ($current_class_id <= 0 || $target_class_id <= 0) {
        $_SESSION['error'] = "Please select both current and target classes.";
        header("Location: index.php?section=add_class");
        exit;
    }

    if ($current_class_id === $target_class_id) {
        $_SESSION['error'] = "Target class cannot be the same as current class.";
        header("Location: index.php?section=add_class");
        exit;
    }

    if (empty($students)) {
        $_SESSION['error'] = "No students selected for promotion.";
        header("Location: index.php?section=add_class");
        exit;
    }

    $count = 0;
    $stmt = $conn->prepare("UPDATE students SET class_id = ? WHERE id = ? AND class_id = ?");
    
    foreach ($students as $student_id) {
        $sid = (int)$student_id;
        $stmt->bind_param("iii", $target_class_id, $sid, $current_class_id);
        if ($stmt->execute()) $count++;
    }
    
    $_SESSION['success'] = "Successfully promoted $count students.";
    header("Location: index.php?section=add_class");
    exit;
}
?>