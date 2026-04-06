<?php
session_start();
include 'config.php';

// Security check
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Unauthorized');
}

$sql = "SELECT t.*, 
        GROUP_CONCAT(s.name SEPARATOR ', ') as subjects,
        u.id AS user_account_id
        FROM teachers t
        LEFT JOIN teacher_subjects ts ON t.id = ts.teacher_id
        LEFT JOIN subjects s ON ts.subject_id = s.id
        LEFT JOIN users u ON u.related_id = t.id AND u.role = 'teacher'
        WHERE t.deleted_at IS NULL
        GROUP BY t.id
        ORDER BY t.id DESC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $salary = number_format($row['salary']);
        $remaining = number_format($row['remaining_payment']);
        $subjects = $row['subjects'] ? htmlspecialchars($row['subjects']) : '<span class="text-gray">No subjects</span>';
        $account_indicator = $row['user_account_id'] ? ' <span class="status-dot" title="User Account Active"></span>' : '';
        
        echo "<tr>";
        echo "<td data-label='ID'>{$row['id']}</td>";
        echo "<td data-label='Name'><strong>" . htmlspecialchars($row['name']) . "</strong>" . $account_indicator . "<br><span style='font-size:0.85em; color:#999;'>" . htmlspecialchars($row['father_name']) . "</span></td>";
        echo "<td data-label='Phone'>" . htmlspecialchars($row['phone']) . "</td>";
        echo "<td data-label='Email'>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td data-label='Salary' style='text-align:right;'>Rs. {$salary}</td>";
        echo "<td data-label='Balance' style='text-align:right;'>Rs. {$remaining}</td>";
        echo "<td data-label='Subjects'>{$subjects}</td>";
        echo "<td data-label='Actions'>
                <div class='action-buttons'>
                    <button class='action-btn edit' onclick='alert(\"Edit feature coming soon\")'>Edit</button>
                    <button class='action-btn delete delete-btn' data-id='{$row['id']}' data-type='teacher' data-csrf='{$_SESSION['csrf_token']}'>Delete</button>
                </div>
              </td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='8' style='text-align:center; padding: 2rem; color: #999;'>No teachers found in the directory.</td></tr>";
}

$conn->close();
?>