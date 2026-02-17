<?php
// db_inspector.php
// Run this file in your browser (e.g., localhost/CMS/db_inspector.php)
// Then copy the text output and paste it into the chat.

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include 'config.php';

header('Content-Type: text/plain');

echo "=== DATABASE STRUCTURE REPORT ===\n\n";

// 1. List all tables
$tables = [];
$result = $conn->query("SHOW TABLES");
if ($result) {
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
}

// 2. For each table, show how it was created (Columns & Keys)
foreach ($tables as $table) {
    echo "--------------------------------------------------\n";
    echo "TABLE: $table\n";
    echo "--------------------------------------------------\n";
    
    $createResult = $conn->query("SHOW CREATE TABLE `$table`");
    if ($createResult) {
        $row = $createResult->fetch_assoc();
        echo $row['Create Table'] . ";\n\n"; 
    }
}

echo "=== END REPORT ===\n";
$conn->close();
?>