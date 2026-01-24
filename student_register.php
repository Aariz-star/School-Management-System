<?php
// student_register.php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name      = trim($_POST['full_name'] ?? '');
    $admission_date = $_POST['admission_date'] ?? '';
    $guardian_name  = trim($_POST['guardian_name'] ?? '');
    $contact        = trim($_POST['contact_number'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $class_id       = (int)($_POST['class_id'] ?? 0);

    $errors = [];

    if (empty($full_name))      $errors[] = "Full name is required.";
    if (empty($admission_date)) $errors[] = "Admission date is required.";
    if (empty($guardian_name))  $errors[] = "Guardian name is required.";
    if (empty($contact))        $errors[] = "Contact number is required.";
    if (empty($email))          $errors[] = "Email is required.";
    if ($class_id <= 0)         $errors[] = "Please select a class.";

    if (empty($errors)) {
        $stmt = $conn->prepare("
            INSERT INTO students
            (full_name, admission_date, guardian_name, contact_number, email, class_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssssi", $full_name, $admission_date, $guardian_name, $contact, $email, $class_id);

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