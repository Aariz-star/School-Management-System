<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;

// Debug query - first check if class exists
$class_check = $conn->query("SELECT id, name FROM classes WHERE id = $class_id AND deleted_at IS NULL");
$class_data = $class_check ? $class_check->fetch_assoc() : null;

// Check if class_subjects exist for this class
$subjects_check = $conn->query("SELECT COUNT(*) as cnt FROM class_subjects WHERE class_id = $class_id");
$subjects_count = $subjects_check ? $subjects_check->fetch_assoc()['cnt'] : 0;

// Get the full subject data
$subjects_data = [];
if ($subjects_count > 0) {
    $sql = "SELECT cs.id, cs.class_id, cs.subject_id, cs.book_name, s.name as subject_name 
            FROM class_subjects cs 
            JOIN subjects s ON cs.subject_id = s.id 
            WHERE cs.class_id = $class_id AND s.deleted_at IS NULL";
    $res = $conn->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $subjects_data[] = $row;
        }
    }
}

echo json_encode([
    'class_id' => $class_id,
    'class_data' => $class_data,
    'subjects_count' => $subjects_count,
    'subjects' => $subjects_data,
    'last_error' => $conn->error
]);
?>
