<?php
session_start();
include 'config.php';

// Get all classes with their subject counts
$classes_sql = "SELECT c.id, c.name, COUNT(cs.id) as subject_count
                FROM classes c
                LEFT JOIN class_subjects cs ON c.id = cs.class_id
                WHERE c.deleted_at IS NULL
                GROUP BY c.id, c.name
                ORDER BY c.name";

$classes_res = $conn->query($classes_sql);

echo "<h2>Classes and Subject Count:</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<thead><tr><th>Class ID</th><th>Class Name</th><th>Subject Count</th></tr></thead>";
echo "<tbody>";

if ($classes_res && $classes_res->num_rows > 0) {
    while ($class = $classes_res->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $class['id'] . "</td>";
        echo "<td>" . htmlspecialchars($class['name']) . "</td>";
        echo "<td>" . $class['subject_count'] . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='3'>No classes found</td></tr>";
}
echo "</tbody></table>";

echo "<hr>";
echo "<h2>All Class-Subject Mappings:</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<thead><tr><th>Class Subject ID</th><th>Class ID</th><th>Class Name</th><th>Subject ID</th><th>Subject Name</th><th>Book Name</th></tr></thead>";
echo "<tbody>";

$all_cs_sql = "SELECT cs.id, cs.class_id, c.name as class_name, cs.subject_id, s.name as subject_name, cs.book_name
               FROM class_subjects cs
               JOIN classes c ON cs.class_id = c.id
               JOIN subjects s ON cs.subject_id = s.id
               WHERE c.deleted_at IS NULL AND s.deleted_at IS NULL
               ORDER BY c.name, s.name";

$all_cs_res = $conn->query($all_cs_sql);

if ($all_cs_res && $all_cs_res->num_rows > 0) {
    while ($row = $all_cs_res->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['class_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['class_name']) . "</td>";
        echo "<td>" . $row['subject_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['subject_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['book_name'] ?? '-') . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>No class-subject mappings found</td></tr>";
}
echo "</tbody></table>";
?>
