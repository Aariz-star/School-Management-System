<?php
// delete_class.php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;

    if ($class_id <= 0) {
        $_SESSION['error'] = "Please select a valid class to delete.";
        header("Location: index.php");
        exit();
    }

    // Prepare Delete Statement
    // Note: If you have foreign keys set up correctly (ON DELETE CASCADE), 
    // this will also delete related records in class_subjects.
    // However, if students are assigned to this class, this might fail unless students are deleted first
    // or the constraint allows it.
    
    $stmt = $conn->prepare("DELETE FROM classes WHERE id = ?");
    $stmt->bind_param("i", $class_id);

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