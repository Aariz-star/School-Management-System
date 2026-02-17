<?php
// create_user.php - Run this to create new users
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $related_id = !empty($_POST['related_id']) ? $_POST['related_id'] : NULL;

    // Check if username exists
    $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        die("Error: Username '$username' is already taken.");
    }

    $stmt = $conn->prepare("INSERT INTO users (username, password, role, related_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $username, $password, $role, $related_id);
    
    if ($stmt->execute()) {
        echo "User created successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="styles.css"></head>
<body style="padding: 50px;">
    <form class="form-content active" method="post" style="max-width: 500px; margin: 0 auto;">
        <h2>Create User</h2>
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <div class="form-grid" style="grid-template-columns: 1fr;">
            <input type="text" name="username" placeholder="Username" required>
            <input type="text" name="password" placeholder="Password" required>
            <select name="role" required>
                <option value="admin">Admin</option>
                <option value="teacher">Teacher</option>
                <option value="student">Student</option>
            </select>
            <input type="number" name="related_id" placeholder="Related ID (Teacher ID or Student ID)">
            <p style="color: #999; font-size: 0.8rem;">
                * For Teachers: Enter ID from 'teachers' table.<br>
                * For Students: Enter ID from 'students' table.<br>
                * For Admin: Leave blank.
            </p>
        </div>
        <button class="submit-btn" type="submit">Create User</button>
    </form>
</body>
</html>