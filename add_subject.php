<?php
// add_subject.php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['subject_name']) ? trim($_POST['subject_name']) : '';

    if (empty($name)) {
        $_SESSION['error'] = "Subject name is required.";
        header("Location: index.php");
        exit();
    }

    // Check duplicate
    $check = $conn->prepare("SELECT id FROM subjects WHERE name = ?");
    $check->bind_param("s", $name);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $_SESSION['error'] = "Subject '$name' already exists.";
        header("Location: index.php");
        exit();
    }
    $check->close();

    // Insert
    $stmt = $conn->prepare("INSERT INTO subjects (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Subject '$name' added successfully.";
    } else {
        $_SESSION['error'] = "Error adding subject: " . $conn->error;
    }
    
    $stmt->close();
    header("Location: index.php");
    exit();
}
?>