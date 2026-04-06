<?php
// assign_class_master.php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Security: Admin only
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        $_SESSION['error'] = "Access denied.";
        header("Location: index.php");
        exit;
    }

    $class_id = (int)($_POST['class_id'] ?? 0);
    $teacher_id = (int)($_POST['teacher_id'] ?? 0);

    if ($class_id <= 0 || $teacher_id <= 0) {
        $_SESSION['error'] = "Please select both a class and a teacher.";
        header("Location: index.php?section=teacher");
        exit;
    }

    // Update the class with the new master
    $stmt = $conn->prepare("UPDATE classes SET class_master_id = ? WHERE id = ?");
    if (!$stmt) {
        $_SESSION['error'] = "Database Error: " . $conn->error . " (Hint: Did you run the CLASS_MASTER_SETUP.sql script?)";
        header("Location: index.php?section=teacher");
        exit;
    }
    $stmt->bind_param("ii", $teacher_id, $class_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Class Master assigned successfully!";
    } else {
        $_SESSION['error'] = "Database error: " . $conn->error;
    }
    $stmt->close();
    header("Location: index.php?section=teacher");
    exit;
}
?>