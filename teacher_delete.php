<?php
// teacher_delete.php
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
            header("Location: view_list.php?view=teachers");
        }
        exit;
    }

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : 'delete'; // Default to soft delete

    if ($id > 0) {
        if ($action === 'delete') {
            // Soft Delete
            $stmt = $conn->prepare("UPDATE teachers SET deleted_at = NOW() WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Teacher moved to Recycle Bin']);
                } else {
                    $_SESSION['success'] = "Teacher moved to Recycle Bin";
                    header("Location: view_list.php?view=teachers");
                }
            } else {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
                } else {
                    $_SESSION['error'] = "Database error: " . $conn->error;
                    header("Location: view_list.php?view=teachers");
                }
            }
            $stmt->close();

        } elseif ($action === 'restore') {
            // Restore
            $stmt = $conn->prepare("UPDATE teachers SET deleted_at = NULL WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Teacher restored successfully!";
            } else {
                $_SESSION['error'] = "Error restoring teacher: " . $conn->error;
            }
            $stmt->close();
            header("Location: view_list.php?view=teachers&status=trash");

        } elseif ($action === 'permanent_delete') {
            // Permanent Delete
            
            // 1. Remove User Account (Login)
            $del_user = $conn->prepare("DELETE FROM users WHERE related_id = ? AND role = 'teacher'");
            if ($del_user) { $del_user->bind_param("i", $id); $del_user->execute(); $del_user->close(); }

            // 2. Unassign Class Master role (Set to NULL)
            $upd_class = $conn->prepare("UPDATE classes SET class_master_id = NULL WHERE class_master_id = ?");
            if ($upd_class) { $upd_class->bind_param("i", $id); $upd_class->execute(); $upd_class->close(); }

            // 3. Remove Teacher Assignments
            $del_assign = $conn->prepare("DELETE FROM teacher_assignments WHERE teacher_id = ?");
            if ($del_assign) { $del_assign->bind_param("i", $id); $del_assign->execute(); $del_assign->close(); }

            // 4. Remove Teacher Subjects (if table exists)
            $check_ts = $conn->query("SHOW TABLES LIKE 'teacher_subjects'");
            if ($check_ts && $check_ts->num_rows > 0) {
                $del_subj = $conn->prepare("DELETE FROM teacher_subjects WHERE teacher_id = ?");
                if ($del_subj) { $del_subj->bind_param("i", $id); $del_subj->execute(); $del_subj->close(); }
            }

            $stmt = $conn->prepare("DELETE FROM teachers WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Teacher permanently deleted.";
            } else {
                $_SESSION['error'] = "Error deleting teacher: " . $conn->error;
            }
            $stmt->close();
            header("Location: view_list.php?view=teachers&status=trash");
        }
    } else {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        } else {
            $_SESSION['error'] = "Invalid ID";
            header("Location: view_list.php?view=teachers");
        }
    }
}
?>