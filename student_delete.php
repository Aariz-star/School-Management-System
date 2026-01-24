<?php
// student_delete.php - AJAX endpoint to delete a student and return JSON
session_start();
include 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid student ID']);
    exit;
}

// Optionally delete related records (attendance, grades) here if needed
$stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}
$stmt->bind_param('i', $id);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Student deleted successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete student']);
}

$stmt->close();
$conn->close();

?>
