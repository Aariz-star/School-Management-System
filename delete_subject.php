<?php
// delete_subject.php
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

    $id = isset($_POST['subject_id']) ? intval($_POST['subject_id']) : 0;

    if ($id <= 0) {
        $_SESSION['error'] = "Invalid subject selected.";
        header("Location: index.php?section=add_class");
        exit();
    }

    // Soft Delete
    $stmt = $conn->prepare("UPDATE subjects SET deleted_at = NOW(), deleted_by = ? WHERE id = ?");
    $stmt->bind_param("ii", $_SESSION['user_id'], $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Subject deleted successfully.";
    } else {
        $_SESSION['error'] = "Error deleting subject: " . $conn->error;
    }
    
    $stmt->close();
    header("Location: index.php?section=add_class");
    exit();
}
?>