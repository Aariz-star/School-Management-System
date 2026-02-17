<?php
session_start();

// Authentication Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

// Session Timeout (30 minutes)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit;
}
$_SESSION['last_activity'] = time();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include 'config.php';

$teacher_id = $_SESSION['related_id'];

// ---------------------------------------------------------
// 1. Fetch Teacher Info
// ---------------------------------------------------------
$stmt = $conn->prepare("SELECT name FROM teachers WHERE id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$teacher_info = $stmt->get_result()->fetch_assoc();

// ---------------------------------------------------------
// 2. Fetch Assignments (Schedule)
// ---------------------------------------------------------
$assignments = null;
if ($teacher_info) {
    $assign_sql = "SELECT c.name as class_name, s.name as subject_name, ta.academic_year 
                   FROM teacher_assignments ta 
                   JOIN classes c ON ta.class_id = c.id 
                   JOIN subjects s ON ta.subject_id = s.id 
                   WHERE ta.teacher_id = ?
                   AND c.deleted_at IS NULL AND s.deleted_at IS NULL
                   ORDER BY ta.academic_year DESC, c.name ASC";
    $a_stmt = $conn->prepare($assign_sql);
    $a_stmt->bind_param("i", $teacher_id);
    $a_stmt->execute();
    $assignments = $a_stmt->get_result();
}

// ---------------------------------------------------------
// 3. Attendance Logic
// ---------------------------------------------------------
$chk = $conn->prepare("SELECT id, name FROM classes WHERE class_master_id = ?");
$chk->bind_param("i", $teacher_id);
$chk->execute();
$chk_res = $chk->get_result();
$chk_row = $chk_res->fetch_assoc();

$target_class_id = 0;
$target_class_name = "";
$attendance_students = null;
$attendance_date = isset($_GET['attendance_date']) ? $_GET['attendance_date'] : date('Y-m-d');

if ($chk_row) {
    $target_class_id = $chk_row['id'];
    $target_class_name = $chk_row['name'];
    
    $sql = "SELECT s.id, s.full_name, 
            (SELECT COUNT(*) FROM attendance WHERE student_id = s.id) as total_days,
            (SELECT COUNT(*) FROM attendance WHERE student_id = s.id AND status = 'Present') as present_days,
            a.status as today_status
            FROM students s
            LEFT JOIN attendance a ON s.id = a.student_id AND a.date = ?
            WHERE s.class_id = ? AND s.deleted_at IS NULL ORDER BY s.full_name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $attendance_date, $target_class_id);
    $stmt->execute();
    $attendance_students = $stmt->get_result();
}

// ---------------------------------------------------------
// 4. Grade Logic
// ---------------------------------------------------------
$g_class_id = isset($_GET['grade_class_id']) ? (int)$_GET['grade_class_id'] : 0;
$g_subject_id = isset($_GET['grade_subject_id']) ? (int)$_GET['grade_subject_id'] : 0;
$g_term = isset($_GET['grade_term']) ? trim($_GET['grade_term']) : '';
$active_grade_section = isset($_GET['grade_section']) ? $_GET['grade_section'] : 'enter';

// Fetch Classes for Dropdown
$grade_classes_res = $conn->query("SELECT DISTINCT c.id, c.name FROM classes c JOIN teacher_assignments ta ON c.id = ta.class_id WHERE ta.teacher_id = $teacher_id AND c.deleted_at IS NULL ORDER BY c.name");

// Fetch Subjects for Dropdown (if class selected)
$grade_subjects_res = null;
if ($g_class_id > 0) {
    $grade_subjects_res = $conn->query("SELECT s.id, s.name FROM subjects s JOIN teacher_assignments ta ON s.id = ta.subject_id WHERE ta.teacher_id = $teacher_id AND ta.class_id = $g_class_id AND s.deleted_at IS NULL ORDER BY s.name");
}

// ---------------------------------------------------------
// 5. Fetch Timetable (Teacher's Schedule)
// ---------------------------------------------------------
$timetable_res = null;
$check_tt = $conn->query("SHOW TABLES LIKE 'timetable'");
if ($check_tt && $check_tt->num_rows > 0) {
    $tt_query = "SELECT t.*, c.name as class_name, s.name as subject_name 
                 FROM timetable t 
                 JOIN teacher_assignments ta ON t.class_id = ta.class_id AND t.subject_id = ta.subject_id
                 JOIN classes c ON t.class_id = c.id 
                 JOIN subjects s ON t.subject_id = s.id 
                 WHERE ta.teacher_id = ? 
                 ORDER BY FIELD(t.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), t.start_time";
    $stmt = $conn->prepare($tt_query);
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $timetable_res = $stmt->get_result();
}

// ---------------------------------------------------------
// 6. Fetch Announcements
// ---------------------------------------------------------
$announcements_res = null;
$check_ann = $conn->query("SHOW TABLES LIKE 'announcements'");
if ($check_ann && $check_ann->num_rows > 0) {
    $ann_query = "SELECT * FROM announcements WHERE target_role IN ('all', 'teacher') ORDER BY publish_date DESC LIMIT 5";
    $announcements_res = $conn->query($ann_query);
}

// ---------------------------------------------------------
// 7. Fetch Posted Homework
// ---------------------------------------------------------
$homework_res = null;
$check_hw = $conn->query("SHOW TABLES LIKE 'homework'");
if ($check_hw && $check_hw->num_rows > 0) {
    // Fetch homework posted for classes this teacher teaches
    // (Simplified: showing all homework for now, ideally filter by teacher's subjects)
    $homework_res = $conn->query("SELECT h.*, c.name as class_name, s.name as subject_name FROM homework h JOIN classes c ON h.class_id = c.id JOIN subjects s ON h.subject_id = s.id JOIN teacher_assignments ta ON h.class_id = ta.class_id AND h.subject_id = ta.subject_id WHERE ta.teacher_id = $teacher_id ORDER BY h.created_at DESC LIMIT 10");
}
?>