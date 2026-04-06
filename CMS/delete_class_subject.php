<?php
// delete_class_subject.php
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

    $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
    $subject_id = isset($_POST['subject_id']) ? intval($_POST['subject_id']) : 0;

    if ($class_id <= 0 || $subject_id <= 0) {
        $_SESSION['error'] = "Please select both a class and a subject.";
        header("Location: index.php?section=add_class");
        exit();
    }

    // Soft Delete the link
    $stmt = $conn->prepare("UPDATE class_subjects SET deleted_at = NOW(), deleted_by = ? WHERE class_id = ? AND subject_id = ? AND deleted_at IS NULL");
    $stmt->bind_param("iii", $_SESSION['user_id'], $class_id, $subject_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['success'] = "Subject removed from the class successfully.";
        } else {
            $_SESSION['error'] = "That subject was not assigned to the selected class.";
        }
    } else {
        $_SESSION['error'] = "Error removing subject: " . $conn->error;
    }
    
    $stmt->close();
    header("Location: index.php?section=add_class");
    exit();
}
?>