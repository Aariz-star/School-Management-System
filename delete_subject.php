<?php
// delete_subject.php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['subject_id']) ? intval($_POST['subject_id']) : 0;

    if ($id <= 0) {
        $_SESSION['error'] = "Invalid subject selected.";
        header("Location: index.php");
        exit();
    }

    // Delete (Cascade will handle class_subjects and teacher_subjects)
    $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Subject deleted successfully.";
    } else {
        $_SESSION['error'] = "Error deleting subject: " . $conn->error;
    }
    
    $stmt->close();
    header("Location: index.php");
    exit();
}
?>