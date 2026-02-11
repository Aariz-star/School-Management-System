<?php
// delete_class_subject.php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
    $subject_id = isset($_POST['subject_id']) ? intval($_POST['subject_id']) : 0;

    if ($class_id <= 0 || $subject_id <= 0) {
        $_SESSION['error'] = "Please select both a class and a subject.";
        header("Location: index.php?section=add_class");
        exit();
    }

    // Delete the link
    $stmt = $conn->prepare("DELETE FROM class_subjects WHERE class_id = ? AND subject_id = ?");
    $stmt->bind_param("ii", $class_id, $subject_id);
    
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