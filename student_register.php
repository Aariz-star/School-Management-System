<?php
// student_register.php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name      = trim($_POST['full_name'] ?? '');
    $admission_date = $_POST['admission_date'] ?? '';
    $dob            = $_POST['dob'] ?? '';
    $email          = trim($_POST['email'] ?? '');
    $contact        = trim($_POST['contact_number'] ?? '');
    $class_id       = (int)($_POST['class_id'] ?? 0);
    
    // Guardian Details
    $g_name         = trim($_POST['guardian_name'] ?? '');
    $g_contact      = trim($_POST['guardian_contact'] ?? '');
    $g_email        = trim($_POST['guardian_email'] ?? '');
    $g_relation     = trim($_POST['relationship'] ?? '');
    $g_address      = trim($_POST['guardian_address'] ?? '');

    $errors = [];

    if (empty($full_name))      $errors[] = "Full name is required.";
    if (empty($admission_date)) $errors[] = "Admission date is required.";
    if (empty($dob))            $errors[] = "Date of Birth is required.";
    if (empty($g_name))         $errors[] = "Guardian name is required.";
    if (empty($g_contact))      $errors[] = "Guardian contact is required.";
    if ($class_id <= 0)         $errors[] = "Please select a class.";

    if (empty($errors)) {
        // 1. Insert Guardian First
        $stmt_g = $conn->prepare("INSERT INTO guardians (guardian_name, contact_number, email, address, relationship_to_student) VALUES (?, ?, ?, ?, ?)");
        $stmt_g->bind_param("sssss", $g_name, $g_contact, $g_email, $g_address, $g_relation);
        
        if (!$stmt_g->execute()) {
            $_SESSION['error'] = "✗ Guardian Error: " . $conn->error;
            header("Location: index.php");
            exit;
        }
        $guardian_id = $conn->insert_id;
        $stmt_g->close();

        // 2. Insert Student linked to Guardian
        $stmt = $conn->prepare("
            INSERT INTO students
            (full_name, admission_date, dob, email, contact_number, class_id, guardian_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param("sssssii", $full_name, $admission_date, $dob, $email, $contact, $class_id, $guardian_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "✓ Student registered successfully! ID: " . $conn->insert_id;
            $stmt->close();
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['error'] = "✗ Database error: " . $conn->error;
            $stmt->close();
            header("Location: index.php");
            exit;
        }
    } else {
        $_SESSION['error'] = "✗ " . implode("\n✗ ", $errors);
        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Student - Ideal Model School</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .message { padding: 1rem; margin: 1rem 0; border-radius: 8px; }
        .success { background: #d4edda; color: #155724; }
        .error   { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<div class="container" style="max-width: 700px; margin-top: 3rem;">

    <h1>Student Registration</h1>

    <?php if ($success): ?>
        <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="message error">
            <ul style="margin:0; padding-left:1.2rem;">
                <?php foreach($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post">
        <!-- same fields as in index.php, but without <select> dynamic part if you prefer -->
        <!-- ... paste the form fields here ... -->
        <button type="submit" class="submit-btn">Register Student</button>
        <a href="index.php" style="margin-left:1rem;">Back to Dashboard</a>
    </form>

</div>

</body>
</html>

<?php $conn->close(); ?>