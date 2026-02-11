<?php
// student_delete.php
session_start();
include 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id > 0) {
        // 1. Get guardian_id first
        $stmt = $conn->prepare("SELECT guardian_id FROM students WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($guardian_id);
        $stmt->fetch();
        $stmt->close();

        // 2. Delete Student
        $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // 3. Delete Guardian (if exists)
            if ($guardian_id) {
                $stmt_g = $conn->prepare("DELETE FROM guardians WHERE id = ?");
                $stmt_g->bind_param("i", $guardian_id);
                $stmt_g->execute();
                $stmt_g->close();
            }
            echo json_encode(['success' => true, 'message' => 'Student and Guardian deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    }
}
?>