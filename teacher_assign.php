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

    $teacher_id = (int)$_POST['teacher_id'];
    $subject_id = (int)$_POST['subject_id'];
    $class_id = (int)$_POST['class_id'];
    $academic_year = trim($_POST['academic_year']);

    // Validation
    if ($teacher_id <= 0 || $subject_id <= 0 || $class_id <= 0 || empty($academic_year)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: index.php");
        exit;
    }

    // Check for existing assignment (Prevent Duplicates)
    // We check if ANY teacher is already assigned to this subject in this class
    $check = $conn->prepare("SELECT t.name FROM teacher_assignments ta JOIN teachers t ON ta.teacher_id = t.id WHERE ta.class_id = ? AND ta.subject_id = ?");
    $check->bind_param("ii", $class_id, $subject_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Inform user who is currently assigned
        $_SESSION['error'] = "Assignment Failed: This subject is already assigned to " . htmlspecialchars($row['name']) . " for this class. Please delete the existing assignment first.";
    } else {
        $stmt = $conn->prepare("INSERT INTO teacher_assignments (teacher_id, subject_id, class_id, academic_year) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $teacher_id, $subject_id, $class_id, $academic_year);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Teacher assigned successfully!";
        } else {
            $_SESSION['error'] = "Database error: " . $conn->error;
        }
        $stmt->close();
    }
    $check->close();
    
    header("Location: index.php");
    exit;
}
?>