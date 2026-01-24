<?php
// fee_management.php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $student_id = (int)($_POST['student_id'] ?? 0);
    $amount = floatval($_POST['amount'] ?? 0);
    $due_date = $_POST['due_date'] ?? '';
    $status = trim($_POST['status'] ?? '');
    
    $errors = [];
    
    if ($student_id <= 0)       $errors[] = "Please select a student.";
    if ($amount <= 0)           $errors[] = "Fee amount must be greater than 0.";
    if (empty($due_date))       $errors[] = "Due date is required.";
    if (empty($status))         $errors[] = "Payment status is required.";
    
    if (empty($errors)) {
        $stmt = $conn->prepare("
            INSERT INTO fees
            (student_id, amount, due_date, status)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("idss", $student_id, $amount, $due_date, $status);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "✓ Fee processed successfully!";
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
