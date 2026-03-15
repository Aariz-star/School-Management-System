<?php
session_start();
include 'config.php';

// Security Check: Only Admin can access this
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Unauthorized access."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(["success" => false, "message" => "Invalid CSRF token."]);
        exit;
    }

    $related_id = (int)$_POST['id'];
    $role_type  = $_POST['type']; // 'student' or 'teacher'
    $new_pass   = $_POST['password'];

    if (strlen($new_pass) < 6) {
        echo json_encode(["success" => false, "message" => "Password must be at least 6 characters."]);
        exit;
    }

    // Hash the new password
    $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);

    // Find the user account linked to this student/teacher
    // Note: The 'role' column in users table matches 'student' or 'teacher'
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE related_id = ? AND role = ?");
    $stmt->bind_param("sis", $hashed_password, $related_id, $role_type);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Password reset successfully!"]);
        } else {
            // No rows affected means either ID/Role wrong OR no user account exists for this person
            echo json_encode(["success" => false, "message" => "No user account found for this $role_type. Please create a user account first."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
    }
    $stmt->close();
}
?>