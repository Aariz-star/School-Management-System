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

    $assignment_id = isset($_POST['assignment_id']) ? (int)$_POST['assignment_id'] : 0;

    if ($assignment_id > 0) {
        $stmt = $conn->prepare("DELETE FROM teacher_assignments WHERE id = ?");
        $stmt->bind_param("i", $assignment_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Teacher assignment removed successfully.";
        } else {
            $_SESSION['error'] = "Error removing assignment: " . $conn->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Invalid assignment ID.";
    }
    
    header("Location: index.php");
    exit;
}
?>