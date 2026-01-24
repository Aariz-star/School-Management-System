<?php
// config.sample.php
// Instructions: Rename this file to 'config.php' and update the credentials below.

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "school_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>