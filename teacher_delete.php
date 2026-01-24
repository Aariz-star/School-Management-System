<?php
// teacher_delete.php - AJAX endpoint to delete a teacher and return JSON
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
    echo json_encode(['success' => false, 'message' => 'Invalid teacher ID']);
    exit;
}

// Remove subject associations for this teacher
$delAssoc = $conn->prepare("DELETE FROM teacher_subjects WHERE teacher_id = ?");
if ($delAssoc) {
    $delAssoc->bind_param('i', $id);
    $delAssoc->execute();
    $delAssoc->close();
}

$stmt = $conn->prepare("DELETE FROM teachers WHERE id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}
$stmt->bind_param('i', $id);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Teacher deleted successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete teacher']);
}

$stmt->close();
$conn->close();

?>
