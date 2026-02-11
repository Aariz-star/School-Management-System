<?php
// add_class_subject.php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
    $subject_id = isset($_POST['subject_id']) ? intval($_POST['subject_id']) : 0;
    $book_name = isset($_POST['book_name']) ? trim($_POST['book_name']) : '';

    if ($class_id <= 0 || $subject_id <= 0) {
        $_SESSION['error'] = "Please select both a class and a subject.";
        header("Location: index.php?section=add_class");
        exit();
    }

    // Check if link already exists
    $check = $conn->prepare("SELECT 1 FROM class_subjects WHERE class_id = ? AND subject_id = ?");
    $check->bind_param("ii", $class_id, $subject_id);
    $check->execute();
    $exists = $check->get_result()->num_rows > 0;
    $check->close();

    if ($exists) {
        // Update existing link with new book name
        $stmt = $conn->prepare("UPDATE class_subjects SET book_name = ? WHERE class_id = ? AND subject_id = ?");
        $stmt->bind_param("sii", $book_name, $class_id, $subject_id);
        $action = "updated";
    } else {
        // Insert new link
        $stmt = $conn->prepare("INSERT INTO class_subjects (class_id, subject_id, book_name) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $class_id, $subject_id, $book_name);
        $action = "added";
    }

    if ($stmt->execute()) {
        $_SESSION['success'] = "Subject successfully $action to the class" . ($book_name ? " with book: $book_name" : ".");
    } else {
        $_SESSION['error'] = "Database error: " . $conn->error;
    }

    $stmt->close();
    header("Location: index.php?section=add_class");
    exit();
}
?>