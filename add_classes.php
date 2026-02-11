<?php
// add_classes.php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $subjects = isset($_POST['subjects']) ? $_POST['subjects'] : [];
    $book_names = isset($_POST['book_names']) ? $_POST['book_names'] : [];
    
    // Validation
    if (empty($name)) {
        $_SESSION['error'] = "Class name is required!";
        header("Location: index.php?section=add_class");
        exit();
    }
    
    // Check if class already exists
    $check_stmt = $conn->prepare("SELECT id FROM classes WHERE name = ?");
    $check_stmt->bind_param("s", $name);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $_SESSION['error'] = "Class '$name' already exists!";
        $check_stmt->close();
        header("Location: index.php?section=add_class");
        exit();
    }
    $check_stmt->close();
    
    // Insert into classes table
    $stmt = $conn->prepare("INSERT INTO classes (name) VALUES (?)");
    
    if (!$stmt) {
        $_SESSION['error'] = "Prepare failed: " . $conn->error;
        header("Location: index.php?section=add_class");
        exit();
    }
    
    $stmt->bind_param("s", $name);
    
    if ($stmt->execute()) {
        $class_id = $stmt->insert_id;

        // Insert subjects for this class
        if (!empty($subjects) && is_array($subjects)) {
            $sub_stmt = $conn->prepare("INSERT INTO class_subjects (class_id, subject_id, book_name) VALUES (?, ?, ?)");
            foreach ($subjects as $sub_id) {
                $sub_id = intval($sub_id);
                if ($sub_id > 0) {
                    $book = isset($book_names[$sub_id]) ? trim($book_names[$sub_id]) : NULL;
                    $sub_stmt->bind_param("iis", $class_id, $sub_id, $book);
                    $sub_stmt->execute();
                }
            }
            $sub_stmt->close();
        }

        $_SESSION['success'] = "Class '$name' added successfully with " . count($subjects) . " subjects!";
        $stmt->close();
        header("Location: index.php?section=add_class");
        exit();
    } else {
        $_SESSION['error'] = "Failed to add class: " . $stmt->error;
        $stmt->close();
        header("Location: index.php?section=add_class");
        exit();
    }
}

$conn->close();
?>
