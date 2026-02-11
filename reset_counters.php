<?php
// reset_counters.php
session_start();
include 'config.php';

// Security check: Only allow if logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("<h3>Access Denied</h3><p>You must be logged in as an Admin to run this script.</p><a href='login.php'>Login</a>");
}

echo "<body style='background: #1a1a1a; color: #e0e0e0; font-family: sans-serif; padding: 2rem;'>";
echo "<h2>Database Counter Reset Tool</h2>";
echo "<p>This tool resets the ID numbering (Auto Increment) for your tables.</p>";
echo "<p><i>Note: If a table is empty, the ID will reset to 1. If it has data, it will reset to the next available number.</i></p>";
echo "<hr style='border-color: #333;'>";

$tables = ['students', 'guardians', 'teachers', 'classes', 'subjects', 'users', 'grades', 'attendance', 'fees', 'teacher_assignments', 'class_subjects', 'teacher_subjects'];

foreach ($tables as $table) {
    // Check if table exists first to avoid errors
    $check = $conn->query("SHOW TABLES LIKE '$table'");
    if ($check && $check->num_rows > 0) {
        // Reset Auto Increment
        if ($conn->query("ALTER TABLE $table AUTO_INCREMENT = 1")) {
            echo "<div style='color: #00d4ff; margin-bottom: 5px;'>✓ Reset ID counter for table: <b>$table</b></div>";
        } else {
            echo "<div style='color: #ff4444; margin-bottom: 5px;'>✗ Error resetting $table: " . $conn->error . "</div>";
        }
    }
}

echo "<hr style='border-color: #333;'>";
echo "<br>";
echo "<a href='index.php' style='padding: 10px 20px; background: #00d4ff; color: #000; text-decoration: none; border-radius: 5px; font-weight: bold;'>Back to Dashboard</a>";
echo "</body>";
?>