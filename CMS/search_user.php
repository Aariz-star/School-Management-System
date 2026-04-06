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
$search_id = intval($_GET['id'] ?? 0);

if ($search_id <= 0 || !in_array($type, ['student', 'teacher'])) {
    echo json_encode(["success" => false, "message" => "Invalid ID or Type."]);
    exit;
}

$data = [];

if ($type === 'student') {
    // 1. Try Direct Search by Database ID
    // FIX: Removed 's.father_name' which does not exist in students table
    $sql = "SELECT s.id, s.full_name, c.name as class_name, 
            g.guardian_name, s.contact_number 
            FROM students s 
            LEFT JOIN classes c ON s.class_id = c.id 
            LEFT JOIN guardians g ON s.guardian_id = g.id
            WHERE s.id = ? AND s.deleted_at IS NULL";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $search_id);
        $stmt->execute();
        $result = $stmt->get_result();
    }

    if ($row = $result->fetch_assoc()) {
        $guardian_name = $row['guardian_name'] ?? 'N/A';
        $data = [
            "found" => true,
            "id" => $row['id'],
            "name" => $row['full_name'],
            "subtitle" => "Class: " . ($row['class_name'] ?? 'N/A'),
            "detail1" => "Guardian: " . $guardian_name,
            "detail2" => "Contact: " . $row['contact_number'],
            "type" => "student"
        ];
    } else {
        // 2. If not found by DB ID, try "Class Roll No" logic (e.g., 801 -> Class 8, Index 1)
        // Logic: Last 2 digits are the student index, previous digits are class number
        $s_str = (string)$search_id;
        
        if (strlen($s_str) >= 2) { 
            // Extract Class Prefix and Sequence
            // Example: 801 -> Prefix "8", Sequence "01"
            // Example: 1005 -> Prefix "10", Sequence "05"
            $sequence = intval(substr($s_str, -2));
            $prefix   = substr($s_str, 0, -2);
            
            if ($sequence > 0) {
                // Find the class that matches this prefix
                $classes_q = $conn->query("SELECT id, name FROM classes WHERE deleted_at IS NULL");
                while($c = $classes_q->fetch_assoc()) {
                    // Mimic the logic used in index.php to generate roll numbers
                    $c_num = preg_replace('/[^0-9]/', '', $c['name']);
                    if ($c_num === '') $c_num = $c['id']; // Fallback if no number in name
                    
                    if ($c_num == $prefix) {
                        // Class Found! Now find the Nth student in this class
                        $offset = $sequence - 1;
                        
                        // FIX: Removed 's.father_name' here as well
                        $sql2 = "SELECT s.id, s.full_name, c.name as class_name, 
                                 g.guardian_name, s.contact_number 
                                 FROM students s 
                                 LEFT JOIN classes c ON s.class_id = c.id 
                                 LEFT JOIN guardians g ON s.guardian_id = g.id
                                 WHERE s.class_id = ? AND s.deleted_at IS NULL
                                 ORDER BY s.full_name
                                 LIMIT 1 OFFSET ?";
                        
                        $stmt2 = $conn->prepare($sql2);
                        if ($stmt2) {
                            $stmt2->bind_param("ii", $c['id'], $offset);
                            $stmt2->execute();
                            $res2 = $stmt2->get_result();
                            
                            if ($row = $res2->fetch_assoc()) {
                                $guardian_name = $row['guardian_name'] ?? 'N/A';
                                $data = [
                                    "found" => true,
                                    "id" => $row['id'], // This returns the REAL Database ID (e.g., 21)
                                    "name" => $row['full_name'],
                                    "subtitle" => "Class: " . ($row['class_name'] ?? 'N/A') . " (Roll No: $search_id)",
                                    "detail1" => "Guardian: " . $guardian_name,
                                    "detail2" => "Contact: " . $row['contact_number'],
                                    "type" => "student"
                                ];
                            }
                            $stmt2->close();
                        }
                        break; // Stop loop once found
                    }
                }
            }
        }
    }
} elseif ($type === 'teacher') {
    // Fetch Teacher Details
    $sql = "SELECT id, name, father_name, phone, email 
            FROM teachers 
            WHERE id = ? AND deleted_at IS NULL";
            
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $search_id);
        $stmt->execute();
        $result = $stmt->get_result();
    }

    if ($row = $result->fetch_assoc()) {
        $data = [
            "found" => true,
            "id" => $row['id'],
            "name" => $row['name'],
            "subtitle" => "Teacher",
            "detail1" => "Father Name: " . $row['father_name'],
            "detail2" => "Phone: " . $row['phone'],
            "type" => "teacher"
        ];
    }
}

if (empty($data)) {
    echo json_encode(["success" => false, "message" => ucfirst($type) . " not found with ID: $search_id"]);
} else {
    // Check if they have a login account
    // FIX: Use real database ID ($data['id']) instead of search input ($search_id)
    $real_id = (int)$data['id'];
    $check_user = $conn->query("SELECT id FROM users WHERE related_id = $real_id AND role = '$type'");
    $data['has_account'] = ($check_user && $check_user->num_rows > 0);
    echo json_encode(["success" => true, "data" => $data]);
}
?>