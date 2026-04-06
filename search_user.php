<?php
// Prevent HTML errors from breaking JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

session_start();
include 'config.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$type = $_GET['type'] ?? '';
$username = trim($_GET['username'] ?? '');

if (empty($username) || !in_array($type, ['student', 'teacher'])) {
    echo json_encode(["success" => false, "message" => "Invalid Username or Type."]);
    exit;
}

$data = [];

// Find user in the users table first based on the provided username
$stmt = $conn->prepare("SELECT id, related_id FROM users WHERE username = ? AND role = ?");
if ($stmt) {
    $stmt->bind_param("ss", $username, $type);
    $stmt->execute();
    $user_result = $stmt->get_result();

    if ($user_row = $user_result->fetch_assoc()) {
        $related_id = $user_row['related_id'];

        if ($type === 'student') {
            $sql = "SELECT s.id, s.full_name, c.name as class_name, 
                    g.guardian_name, s.contact_number 
                    FROM students s 
                    LEFT JOIN classes c ON s.class_id = c.id 
                    LEFT JOIN guardians g ON s.guardian_id = g.id
                    WHERE s.id = ? AND s.deleted_at IS NULL";
            
            $stmt2 = $conn->prepare($sql);
            if ($stmt2) {
                $stmt2->bind_param("i", $related_id);
                $stmt2->execute();
                $result = $stmt2->get_result();
                
                if ($row = $result->fetch_assoc()) {
                    $guardian_name = $row['guardian_name'] ?? 'N/A';
                    $data = [
                        "found" => true,
                        "id" => $related_id,
                        "name" => $row['full_name'],
                        "subtitle" => "Class: " . ($row['class_name'] ?? 'N/A'),
                        "detail1" => "Guardian: " . $guardian_name,
                        "detail2" => "Contact: " . $row['contact_number'],
                        "type" => "student",
                        "has_account" => true
                    ];
                }
                $stmt2->close();
            }
        } elseif ($type === 'teacher') {
            $sql = "SELECT id, name, father_name, phone, email 
                    FROM teachers 
                    WHERE id = ? AND deleted_at IS NULL";
                    
            $stmt2 = $conn->prepare($sql);
            if ($stmt2) {
                $stmt2->bind_param("i", $related_id);
                $stmt2->execute();
                $result = $stmt2->get_result();
                
                if ($row = $result->fetch_assoc()) {
                    $data = [
                        "found" => true,
                        "id" => $related_id,
                        "name" => $row['name'],
                        "subtitle" => "Teacher",
                        "detail1" => "Father Name: " . $row['father_name'],
                        "detail2" => "Phone: " . $row['phone'],
                        "type" => "teacher",
                        "has_account" => true
                    ];
                }
                $stmt2->close();
            }
        }
    }
    $stmt->close();
}

if (empty($data)) {
    echo json_encode(["success" => false, "message" => "No active " . ucfirst($type) . " found with username: $username"]);
} else {
    echo json_encode(["success" => true, "data" => $data]);
}

$conn->close();
?>