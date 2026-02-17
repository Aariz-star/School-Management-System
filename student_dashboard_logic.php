<?php
session_start();

// Authentication Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

// Session Timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit;
}
$_SESSION['last_activity'] = time();

// Refresh CSRF Token (ensure it's always available)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include 'config.php';

$student_id = $_SESSION['related_id'];
$user_id = $_SESSION['user_id'];

// 1. Fetch Student & Class Info
$stmt = $conn->prepare("SELECT s.*, c.name as class_name 
                        FROM students s 
                        LEFT JOIN classes c ON s.class_id = c.id 
                        WHERE s.id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student_info = $stmt->get_result()->fetch_assoc();
$class_id = $student_info['class_id'];

// 2. Fetch My Classes (Subjects & Teachers)
$subjects_query = "SELECT s.id as subject_id, s.name as subject_name, t.name as teacher_name, cs.book_name
                   FROM class_subjects cs
                   JOIN subjects s ON cs.subject_id = s.id
                   LEFT JOIN teacher_assignments ta ON ta.class_id = cs.class_id AND ta.subject_id = s.id
                   LEFT JOIN teachers t ON ta.teacher_id = t.id
                   WHERE cs.class_id = ? AND s.deleted_at IS NULL";
$stmt = $conn->prepare($subjects_query);
$stmt->bind_param("i", $class_id);
$stmt->execute();
$my_classes = $stmt->get_result();

// 2.5. Fetch Fee Invoices
$fee_sql = "SELECT id, invoice_number, title, amount, status, due_date FROM fee_invoices WHERE student_id = ? ORDER BY due_date DESC";
$stmt = $conn->prepare($fee_sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$fee_result = $stmt->get_result();
$fee_data = [];
while($row = $fee_result->fetch_assoc()) {
    $fee_data[] = $row;
}

// 3. Fetch Attendance Stats
$att_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present,
    SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent
    FROM attendance WHERE student_id = ?";
$stmt = $conn->prepare($att_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$att_stats = $stmt->get_result()->fetch_assoc();
$att_percent = ($att_stats['total'] > 0) ? round(($att_stats['present'] / $att_stats['total']) * 100) : 0;

// 4. Fetch Results (Grades)
$grades_query = "SELECT s.name as subject, g.score, g.term, t.name as teacher_name
                 FROM grades g 
                 JOIN subjects s ON g.subject_id = s.id 
                 LEFT JOIN teacher_assignments ta ON ta.subject_id = s.id AND ta.class_id = ?
                 LEFT JOIN teachers t ON ta.teacher_id = t.id
                 WHERE g.student_id = ? 
                 ORDER BY g.term DESC, s.name ASC";
$stmt = $conn->prepare($grades_query);
$stmt->bind_param("ii", $class_id, $student_id);
$stmt->execute();
$grades_res = $stmt->get_result();

// 5. Fetch Homework (Mock/Real)
$homework_res = null;
$check_hw = $conn->query("SHOW TABLES LIKE 'homework'");
if ($check_hw && $check_hw->num_rows > 0) {
    $hw_query = "SELECT h.*, s.name as subject_name 
                 FROM homework h 
                 JOIN subjects s ON h.subject_id = s.id 
                 WHERE h.class_id = ? ORDER BY h.due_date ASC";
    $stmt = $conn->prepare($hw_query);
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $homework_res = $stmt->get_result();
}

// 6. Fetch Timetable
$timetable_res = null;
$check_tt = $conn->query("SHOW TABLES LIKE 'timetable'");
if ($check_tt && $check_tt->num_rows > 0) {
    $tt_query = "SELECT t.*, s.name as subject_name 
                 FROM timetable t 
                 JOIN subjects s ON t.subject_id = s.id 
                 WHERE t.class_id = ? ORDER BY t.day_of_week, t.start_time";
    $stmt = $conn->prepare($tt_query);
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $timetable_res = $stmt->get_result();
}

// 7. Fetch Announcements
$announcements_res = null;
$check_ann = $conn->query("SHOW TABLES LIKE 'announcements'");
if ($check_ann && $check_ann->num_rows > 0) {
    $ann_query = "SELECT * FROM announcements WHERE target_role IN ('all', 'student') ORDER BY publish_date DESC LIMIT 5";
    $announcements_res = $conn->query($ann_query);
}

// 8. Fetch Messages
$messages_res = null;
$check_msg = $conn->query("SHOW TABLES LIKE 'messages'");
if ($check_msg && $check_msg->num_rows > 0) {
    $msg_query = "SELECT * FROM messages WHERE receiver_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($msg_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $messages_res = $stmt->get_result();
}
?>