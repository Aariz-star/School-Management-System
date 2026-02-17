<?php
// student_delete.php
session_start();
include 'config.php';

// Check if it's an AJAX request (for soft delete from script.js)
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    if ($is_ajax) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access Denied']);
    } else {
        $_SESSION['error'] = "Access Denied";
        header("Location: index.php");
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'CSRF token validation failed']);
        } else {
            $_SESSION['error'] = "CSRF token validation failed";
            header("Location: view_list.php?view=students");
        }
        exit;
    }

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : 'delete'; // Default to soft delete
    $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;

    if ($id > 0) {
        if ($action === 'delete') {
            // Soft Delete Student
            $stmt = $conn->prepare("UPDATE students SET deleted_at = NOW(), deleted_by = ? WHERE id = ?");
            $stmt->bind_param("ii", $_SESSION['user_id'], $id);
            
            if ($stmt->execute()) {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Student moved to Recycle Bin']);
                } else {
                    $_SESSION['success'] = "Student moved to Recycle Bin";
                    header("Location: view_list.php?view=students&class_id=$class_id");
                }
            } else {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
                } else {
                    $_SESSION['error'] = "Database error: " . $conn->error;
                    header("Location: view_list.php?view=students&class_id=$class_id");
                }
            }
            $stmt->close();

        } elseif ($action === 'restore') {
            // Restore Student
            $stmt = $conn->prepare("UPDATE students SET deleted_at = NULL, deleted_by = NULL WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Student restored successfully!";
            } else {
                $_SESSION['error'] = "Error restoring student: " . $conn->error;
            }
            $stmt->close();
            header("Location: view_list.php?view=students&class_id=$class_id&status=trash");

        } elseif ($action === 'permanent_delete') {
            // Permanent Delete (Move to Inactive/Archive)
            // Instead of deleting, we set status to 'inactive' so it's hidden from frontend but kept in DB
            $stmt = $conn->prepare("UPDATE students SET status = 'inactive', deleted_by = ? WHERE id = ?");
            $stmt->bind_param("ii", $_SESSION['user_id'], $id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Student permanently deleted (Archived).";
            } else {
                $_SESSION['error'] = "Error deleting student: " . $conn->error;
            }
            $stmt->close();
            header("Location: view_list.php?view=students&class_id=$class_id&status=trash");
        }
    } else {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        } else {
            $_SESSION['error'] = "Invalid ID";
            header("Location: view_list.php?view=students");
        }
    }
}
?>