<?php
ob_start();
// get_student_invoices.php - Fetch invoices for a specific student via AJAX
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$student_id = (int)($_GET['student_id'] ?? 0);

if ($student_id === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid student ID']);
    exit;
}

// Verify student exists
$student_check = $conn->query("SELECT id FROM students WHERE id = $student_id AND deleted_at IS NULL");
if (!$student_check || $student_check->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Student not found']);
    exit;
}

// Fetch all invoices for this student
$query = "SELECT id, invoice_number, title, amount, status, due_date
          FROM fee_invoices
          WHERE student_id = $student_id
          ORDER BY due_date DESC";

$result = $conn->query($query);
$invoices = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $invoices[] = $row;
    }
}

ob_end_clean();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
echo json_encode([
    'invoices' => $invoices
]);
