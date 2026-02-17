<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'teacher'])) {
    exit;
}

$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$role = $_SESSION['role'];
$related_id = $_SESSION['related_id'] ?? 0;

echo "<option value=''>-- Select Subject --</option>";

if ($class_id > 0) {
    $sql = "";
    if ($role === 'admin') {
        // Admin sees all subjects in the class
        $sql = "SELECT s.id, s.name 
                FROM subjects s 
                JOIN class_subjects cs ON s.id = cs.subject_id 
                WHERE cs.class_id = $class_id 
                AND s.deleted_at IS NULL
                AND cs.deleted_at IS NULL
                ORDER BY s.name";
    } elseif ($role === 'teacher') {
        // Teacher sees only assigned subjects
        $sql = "SELECT s.id, s.name 
                FROM subjects s 
                JOIN teacher_assignments ta ON s.id = ta.subject_id 
                WHERE ta.teacher_id = $related_id AND ta.class_id = $class_id 
                AND s.deleted_at IS NULL
                ORDER BY s.name";
    }

    if ($sql) {
        $res = $conn->query($sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                echo "<option value='{$row['id']}'>{$row['name']}</option>";
            }
        }
    }
}
?>