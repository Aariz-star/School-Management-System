<?php
// attendance_record.php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'teacher'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }
    
    // Get return_to parameter
    $return_to = isset($_POST['return_to']) ? trim($_POST['return_to']) : 'attendance';
    
    // ────────────────────────────────────────────────────────────────
    // BULK ATTENDANCE HANDLING
    // ────────────────────────────────────────────────────────────────
    if (isset($_POST['attendance']) && is_array($_POST['attendance'])) {
        $date = $_POST['date'] ?? '';
        $class_id = isset($_POST['class_id']) ? (int)$_POST['class_id'] : 0;
        
        if (empty($date)) {
            $_SESSION['error'] = "Attendance date is required.";
            header("Location: index.php?return_to=" . urlencode($return_to));
            exit;
        }

        // Prepare statements for Check, Insert, and Update
        $check_stmt = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND date = ?");
        $insert_stmt = $conn->prepare("INSERT INTO attendance (student_id, date, status) VALUES (?, ?, ?)");
        $update_stmt = $conn->prepare("UPDATE attendance SET status = ? WHERE id = ?");
        
        $count = 0;
        $updated = 0;

        foreach ($_POST['attendance'] as $student_id => $status) {
            $student_id = (int)$student_id;
            $status = trim($status);
            
            if ($student_id > 0 && !empty($status)) {
                // Check if record exists
                $check_stmt->bind_param("is", $student_id, $date);
                $check_stmt->execute();
                $check_res = $check_stmt->get_result();
                
                if ($row = $check_res->fetch_assoc()) {
                    // Update existing
                    $update_stmt->bind_param("si", $status, $row['id']);
                    $update_stmt->execute();
                    $updated++;
                } else {
                    // Insert new
                    $insert_stmt->bind_param("iss", $student_id, $date, $status);
                    $insert_stmt->execute();
                    $count++;
                }
            }
        }
        
        $check_stmt->close();
        $insert_stmt->close();
        $update_stmt->close();
        
        $msg = "✓ Attendance saved for $date.";
        if ($count > 0) $msg .= " ($count new)";
        if ($updated > 0) $msg .= " ($updated updated)";
        
        $_SESSION['success'] = $msg;
        
        // Redirect back with return_to parameter
        $redirect_url = "index.php?return_to=" . urlencode($return_to);
        if ($class_id > 0) {
            $redirect_url .= "&attendance_class_id=$class_id&attendance_date=$date";
        }
        header("Location: $redirect_url");
        exit;
    }

    // ────────────────────────────────────────────────────────────────
    // SINGLE STUDENT HANDLING (Legacy/Fallback)
    // ────────────────────────────────────────────────────────────────
    $student_id = (int)($_POST['student_id'] ?? 0);
    $date = $_POST['date'] ?? '';
    $status = trim($_POST['status'] ?? '');
    
    $errors = [];
    
    if ($student_id <= 0)       $errors[] = "Please select a student.";
    if (empty($date))           $errors[] = "Attendance date is required.";
    if (empty($status))         $errors[] = "Please mark attendance status.";
    
    // Security Check: If Teacher, verify they are the Class Master of this student
    if (empty($errors) && $_SESSION['role'] === 'teacher') {
        $tid = $_SESSION['related_id'];
        
        // Get student's class master
        $check = $conn->prepare("SELECT c.class_master_id 
                                 FROM students s 
                                 JOIN classes c ON s.class_id = c.id 
                                 WHERE s.id = ?");
        $check->bind_param("i", $student_id);
        $check->execute();
        $check->bind_result($master_id);
        $check->fetch();
        $check->close();

        if ($master_id !== $tid) {
            $errors[] = "Access Denied: You are not the Class Master for this student.";
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("
            INSERT INTO attendance
            (student_id, date, status)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iss", $student_id, $date, $status);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "✓ Attendance recorded successfully!";
            $stmt->close();
            header("Location: index.php?return_to=" . urlencode($return_to));
            exit;
        } else {
            $_SESSION['error'] = "✗ Database error: " . $conn->error;
            $stmt->close();
            header("Location: index.php?return_to=" . urlencode($return_to));
            exit;
        }
    } else {
        $_SESSION['error'] = "✗ " . implode("\n✗ ", $errors);
        header("Location: index.php?return_to=" . urlencode($return_to));
        exit;
    }
}
?>
