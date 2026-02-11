<?php
// reset_admin.php
// Run this file to reset the admin account if login is failing.

include 'config.php';

$username = 'admin';
$password = 'password123';

// Generate a fresh hash using the server's current PHP version
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "<h3>Resetting Admin Account...</h3>";

// 1. Check if admin exists
$check = $conn->prepare("SELECT id FROM users WHERE username = ?");
$check->bind_param("s", $username);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    // Update existing user
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
    $stmt->bind_param("ss", $hash, $username);
    echo "Updating existing 'admin' user...<br>";
} else {
    // Create new user
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
    $stmt->bind_param("ss", $username, $hash);
    echo "Creating new 'admin' user...<br>";
}

if ($stmt->execute()) {
    echo "<h2 style='color: green;'>Success!</h2>";
    echo "You can now login with:<br>";
    echo "Username: <b>admin</b><br>";
    echo "Password: <b>password123</b><br>";
    echo "<br><a href='login.php'>Go to Login Page</a>";
} else {
    echo "<h2 style='color: red;'>Error</h2>";
    echo "Database error: " . $conn->error;
}
?>