<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    $class_id = (int)$_POST['class_id'];
    $subject_id = (int)$_POST['subject_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $due_date = $_POST['due_date'];

    // Validation: Ensure teacher is assigned to this class/subject
    $teacher_id = $_SESSION['related_id'];
    $check = $conn->prepare("SELECT id FROM teacher_assignments WHERE teacher_id = ? AND class_id = ? AND subject_id = ?");
    $check->bind_param("iii", $teacher_id, $class_id, $subject_id);
    $check->execute();
    if ($check->get_result()->num_rows === 0) {
        $_SESSION['error'] = "You are not assigned to this class and subject.";
        header("Location: teacher_dashboard.php");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO homework (class_id, subject_id, title, description, due_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $class_id, $subject_id, $title, $description, $due_date);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Homework posted successfully!";
    } else {
        $_SESSION['error'] = "Error posting homework: " . $conn->error;
    }

    $stmt->close();
    header("Location: teacher_dashboard.php");
    exit;
}
?>