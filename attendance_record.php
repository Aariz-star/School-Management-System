<?php
// attendance_record.php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $student_id = (int)($_POST['student_id'] ?? 0);
    $date = $_POST['date'] ?? '';
    $status = trim($_POST['status'] ?? '');
    
    $errors = [];
    
    if ($student_id <= 0)       $errors[] = "Please select a student.";
    if (empty($date))           $errors[] = "Attendance date is required.";
    if (empty($status))         $errors[] = "Please mark attendance status.";
    
    if (empty($errors)) {
        $stmt = $conn->prepare("
            INSERT INTO attendance
            (student_id, date, status)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iss", $student_id, $date, $status);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "✓ Attendance recorded successfully!";
            $stmt->close();
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['error'] = "✗ Database error: " . $conn->error;
            $stmt->close();
            header("Location: index.php");
            exit;
        }
    } else {
        $_SESSION['error'] = "✗ " . implode("\n✗ ", $errors);
        header("Location: index.php");
        exit;
    }
}
?>
