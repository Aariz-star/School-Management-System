<?php
ob_start();
// get_invoices.php - Fetch invoices for a specific class via AJAX
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$class_id = (int)($_GET['class_id'] ?? 0);

if ($class_id === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid class ID']);
    exit;
}

// Fetch class name
$class_res = $conn->query("SELECT name FROM classes WHERE id = $class_id AND deleted_at IS NULL");
if (!$class_res || $class_res->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Class not found']);
    exit;
}

$class_name = $class_res->fetch_assoc()['name'];

// Fetch all invoices for this class
$query = "SELECT fi.id, fi.invoice_number, fi.title, fi.amount, fi.status, fi.due_date, s.full_name as student_name
          FROM fee_invoices fi
          JOIN students s ON fi.student_id = s.id
          WHERE s.class_id = $class_id AND s.deleted_at IS NULL
          ORDER BY s.full_name, fi.due_date DESC";

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
    'class_name' => $class_name,
    'invoices' => $invoices
]);
