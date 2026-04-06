<?php
// student_fee_upload.php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    $invoice_id = (int)$_POST['invoice_id'];
    $ref_no = trim($_POST['reference_no']);

    // Check for duplicate reference number
    $check_dup = $conn->prepare("SELECT id FROM fee_payments WHERE reference_no = ?");
    $check_dup->bind_param("s", $ref_no);
    $check_dup->execute();
    if ($check_dup->get_result()->num_rows > 0) {
        $_SESSION['error'] = "Error: This transaction reference number has already been submitted.";
        header("Location: student_dashboard.php");
        exit;
    }
    
    // Get Invoice Amount
    $inv_stmt = $conn->prepare("SELECT amount FROM fee_invoices WHERE id = ?");
    $inv_stmt->bind_param("i", $invoice_id);
    $inv_stmt->execute();
    $inv = $inv_stmt->get_result()->fetch_assoc();
    
    if (!$inv) {
        $_SESSION['error'] = "Invalid Invoice.";
        header("Location: student_dashboard.php");
        exit;
    }
    $amount = $inv['amount'];

    // Check if file was uploaded without errors
    if (!isset($_FILES["proof_image"]) || $_FILES["proof_image"]["error"] != UPLOAD_ERR_OK) {
        $_SESSION['error'] = "Please select a valid image file.";
        header("Location: student_dashboard.php");
        exit;
    }

    // Check file size (Max 4MB)
    if ($_FILES["proof_image"]["size"] > 4194304) {
        $_SESSION['error'] = "File is too large. Max size is 4MB.";
        header("Location: student_dashboard.php");
        exit;
    }

    // Handle File Upload
    $upload_dir = "uploads/payments/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_name = time() . "_" . basename($_FILES["proof_image"]["name"]);
    $target_file = $upload_dir . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a actual image
    $check = getimagesize($_FILES["proof_image"]["tmp_name"]);
    if($check === false) {
        $_SESSION['error'] = "File is not an image.";
        header("Location: student_dashboard.php");
        exit;
    }

    if (move_uploaded_file($_FILES["proof_image"]["tmp_name"], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO fee_payments (invoice_id, amount, method, reference_no, proof_image, status) VALUES (?, ?, 'Bank Transfer', ?, ?, 'Pending')");
        $stmt->bind_param("idss", $invoice_id, $amount, $ref_no, $target_file);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Payment proof uploaded! Verification pending.";
        } else {
            $_SESSION['error'] = "Database error: " . $conn->error;
        }
    } else {
        $_SESSION['error'] = "Sorry, there was an error uploading your file.";
    }

    header("Location: student_dashboard.php?return_to=fees");
    exit;
}
?>