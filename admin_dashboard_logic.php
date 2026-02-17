<?php
// admin_dashboard_logic.php

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    exit; // Prevent direct access
}

// 1. Key Metrics (Counts)
// Use checks to prevent crashes if tables are empty or queries fail
$total_students = 0;
$total_teachers = 0;
$total_classes = 0;

if ($res = $conn->query("SELECT COUNT(*) as cnt FROM students WHERE deleted_at IS NULL")) {
    $total_students = $res->fetch_assoc()['cnt'];
}
if ($res = $conn->query("SELECT COUNT(*) as cnt FROM teachers WHERE deleted_at IS NULL")) {
    $total_teachers = $res->fetch_assoc()['cnt'];
}
if ($res = $conn->query("SELECT COUNT(*) as cnt FROM classes WHERE deleted_at IS NULL")) {
    $total_classes = $res->fetch_assoc()['cnt'];
}

// 2. Today's Attendance Overview
$today = date('Y-m-d');
$present_today = 0;
$absent_today = 0;

if ($att_res = $conn->query("SELECT status, COUNT(*) as cnt FROM attendance WHERE date = '$today' GROUP BY status")) {
    while($row = $att_res->fetch_assoc()) {
        // Case-insensitive check for robustness
        if (strcasecmp($row['status'], 'Present') === 0) $present_today = (int)$row['cnt'];
        if (strcasecmp($row['status'], 'Absent') === 0) $absent_today = (int)$row['cnt'];
    }
}
$total_att_marked = $present_today + $absent_today;
$today_attendance_percent = ($total_att_marked > 0) ? round(($present_today / $total_att_marked) * 100) : 0;

// 3. Attendance Trend (Last 7 Days)
$dates = [];
$present_counts = [];
$absent_counts = [];

// Optimization: Fetch all 7 days of data in ONE query instead of 7 queries
$start_date = date('Y-m-d', strtotime("-6 days"));
$end_date = date('Y-m-d');

$sql_trend = "SELECT date, 
    SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as p,
    SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as a
    FROM attendance 
    WHERE date BETWEEN '$start_date' AND '$end_date'
    GROUP BY date";

$trend_data = [];
if ($res = $conn->query($sql_trend)) {
    while($row = $res->fetch_assoc()) {
        $trend_data[$row['date']] = $row;
    }
}

// Fill in the arrays (handling days with no data)
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $dates[] = date('M d', strtotime($d)); // e.g., "Oct 25"
    
    if (isset($trend_data[$d])) {
        $present_counts[] = (int)$trend_data[$d]['p'];
        $absent_counts[] = (int)$trend_data[$d]['a'];
    } else {
        $present_counts[] = 0;
        $absent_counts[] = 0;
    }
}

// 4. Grade Distribution (Overall Performance)
$grades_dist = ['Excellent' => 0, 'Good' => 0, 'Average' => 0, 'Fail' => 0];

// Optimization: Calculate counts in SQL instead of fetching all rows to PHP
$sql_grades = "SELECT 
    SUM(CASE WHEN g.score >= 80 THEN 1 ELSE 0 END) as excellent,
    SUM(CASE WHEN g.score >= 60 AND g.score < 80 THEN 1 ELSE 0 END) as good,
    SUM(CASE WHEN g.score >= 40 AND g.score < 60 THEN 1 ELSE 0 END) as average,
    SUM(CASE WHEN g.score < 40 THEN 1 ELSE 0 END) as fail
    FROM grades g 
    JOIN students s ON g.student_id = s.id 
    WHERE s.deleted_at IS NULL";

if ($res = $conn->query($sql_grades)) {
    $row = $res->fetch_assoc();
    $grades_dist['Excellent'] = (int)($row['excellent'] ?? 0);
    $grades_dist['Good'] = (int)($row['good'] ?? 0);
    $grades_dist['Average'] = (int)($row['average'] ?? 0);
    $grades_dist['Fail'] = (int)($row['fail'] ?? 0);
}

// 5. Common Dropdown Data (Fetched once, used multiple times)
$classes_list = [];
if ($res = $conn->query("SELECT id, name FROM classes WHERE deleted_at IS NULL ORDER BY name")) {
    while($row = $res->fetch_assoc()) $classes_list[] = $row;
}

$teachers_list = [];
if ($res = $conn->query("SELECT id, name FROM teachers WHERE deleted_at IS NULL ORDER BY name")) {
    while($row = $res->fetch_assoc()) $teachers_list[] = $row;
}

$subjects_list = [];
if ($res = $conn->query("SELECT id, name FROM subjects WHERE deleted_at IS NULL ORDER BY name")) {
    while($row = $res->fetch_assoc()) $subjects_list[] = $row;
}

// 6. Fee Management Stats
$total_due = $conn->query("SELECT SUM(amount) as amt FROM fee_invoices WHERE status != 'Paid'")->fetch_assoc()['amt'] ?? 0;
$pending_verifications = $conn->query("SELECT COUNT(*) as cnt FROM fee_payments WHERE status = 'Pending'")->fetch_assoc()['cnt'] ?? 0;
$collected_month = $conn->query("SELECT SUM(amount) as amt FROM fee_payments WHERE status = 'Verified' AND MONTH(payment_date) = MONTH(CURRENT_DATE())")->fetch_assoc()['amt'] ?? 0;

// 7. Pending Payments List
$pending_payments_list = [];
$pending_sql = "SELECT p.*, i.title, i.invoice_number, i.amount as invoice_amount, s.full_name, s.id as student_id, c.name as class_name
                FROM fee_payments p 
                JOIN fee_invoices i ON p.invoice_id = i.id 
                JOIN students s ON i.student_id = s.id 
                JOIN classes c ON s.class_id = c.id
                WHERE p.status = 'Pending' ORDER BY p.payment_date DESC";
if ($res = $conn->query($pending_sql)) {
    while($row = $res->fetch_assoc()) $pending_payments_list[] = $row;
}
?>