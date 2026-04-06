<?php
// delete_class.php
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

    if ($class_id <= 0) {
        $_SESSION['error'] = "Please select a valid class to delete.";
        header("Location: index.php");
        exit();
    }

    // Check for active students in this class
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM students WHERE class_id = ? AND deleted_at IS NULL");
    $check_stmt->bind_param("i", $class_id);
    $check_stmt->execute();
    $check_stmt->bind_result($student_count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($student_count > 0) {
        $_SESSION['error'] = "Cannot delete class. There are $student_count active students assigned to it.";
        header("Location: index.php");
        exit();
    }
    
    // Soft Delete
    $stmt = $conn->prepare("UPDATE classes SET deleted_at = NOW(), deleted_by = ? WHERE id = ?");
    $stmt->bind_param("ii", $_SESSION['user_id'], $class_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['success'] = "Class deleted successfully.";
        } else {
            $_SESSION['error'] = "Class not found or already deleted.";
        }
    } else {
        // Check for foreign key constraint failure (e.g. students exist in class)
        $_SESSION['error'] = "Cannot delete class. It may have students assigned to it.\nError: " . $conn->error;
    }

    $stmt->close();
    header("Location: index.php");
    exit();
}
?>