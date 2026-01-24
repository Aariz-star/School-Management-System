<?php
// add_teacher.php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name               = isset($_POST['name']) ? trim($_POST['name']) : '';
    $father_name        = isset($_POST['father_name']) ? trim($_POST['father_name']) : '';
    $salary             = isset($_POST['salary']) ? floatval($_POST['salary']) : 0;
    $phone              = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $email              = isset($_POST['email']) ? trim($_POST['email']) : '';
    $remaining_payment  = isset($_POST['remaining_payment']) ? floatval($_POST['remaining_payment']) : 0;
    $subjects           = isset($_POST['subjects']) ? $_POST['subjects'] : [];
    
    // Validation
    if (empty($name) || empty($father_name) || empty($phone) || empty($email)) {
        $_SESSION['error'] = "All fields are required!";
        header("Location: index.php");
        exit();
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format!";
        header("Location: index.php");
        exit();
    }
    
    if (!is_numeric($salary) || $salary < 0) {
        $_SESSION['error'] = "Salary must be a valid positive number!";
        header("Location: index.php");
        exit();
    }
    
    if (!is_numeric($remaining_payment) || $remaining_payment < 0) {
        $_SESSION['error'] = "Remaining payment must be a valid positive number!";
        header("Location: index.php");
        exit();
    }
    
    // Insert into teachers table
    $stmt = $conn->prepare("INSERT INTO teachers (name, father_name, salary, phone, email, remaining_payment) VALUES (?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        $_SESSION['error'] = "Prepare failed: " . $conn->error;
        header("Location: index.php");
        exit();
    }
    
    $stmt->bind_param("ssddss", $name, $father_name, $salary, $phone, $email, $remaining_payment);
    
    if ($stmt->execute()) {
        $teacher_id = $stmt->insert_id;
        
        // Insert teacher-subject relationships
        if (!empty($subjects) && is_array($subjects)) {
            $subject_stmt = $conn->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id) VALUES (?, ?)");
            
            if (!$subject_stmt) {
                $_SESSION['error'] = "Failed to link subjects: " . $conn->error;
                header("Location: index.php");
                exit();
            }
            
            foreach ($subjects as $subject_id) {
                $subject_id = intval($subject_id);
                if ($subject_id > 0) {
                    $subject_stmt->bind_param("ii", $teacher_id, $subject_id);
                    $subject_stmt->execute();
                }
            }
            
            $subject_stmt->close();
        }
        
        $_SESSION['success'] = "Teacher '$name' registered successfully!";
        $stmt->close();
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['error'] = "Failed to register teacher: " . $stmt->error;
        $stmt->close();
        header("Location: index.php");
        exit();
    }
}

$conn->close();
?>
