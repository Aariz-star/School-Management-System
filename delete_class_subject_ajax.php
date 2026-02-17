<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $composite_key = isset($_POST['composite_key']) ? trim($_POST['composite_key']) : '';
    
    // Parse composite key format: "class_id:subject_id"
    $parts = explode(':', $composite_key);
    if (count($parts) !== 2) {
        echo json_encode(['success' => false, 'message' => 'Invalid key format']);
        exit;
    }
    
    $class_id = (int)$parts[0];
    $subject_id = (int)$parts[1];
    
    if ($class_id <= 0 || $subject_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid class or subject ID']);
        exit;
    }
    
    // Use soft delete for class_subjects (mark as deleted instead of removing)
    $delete_time = date('Y-m-d H:i:s');
    $admin_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("UPDATE class_subjects SET deleted_at = ?, deleted_by = ? WHERE class_id = ? AND subject_id = ?");
    $stmt->bind_param("siii", $delete_time, $admin_id, $class_id, $subject_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Subject removed successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
