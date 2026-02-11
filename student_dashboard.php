<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

include 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Ideal Model School</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body>
    <header class="header">
        <div class="header-overlay">
            <h1>Ideal Model School</h1>
            <p>Student Portal</p>
            <div class="welcome-msg">Welcome, Student</div>
            <button class="menu-toggle" onclick="toggleSidebar()">Dashboard</button>
        </div>
    </header>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    <nav class="nav-container" id="sidebar">
        <div class="sidebar-header"><h3>Menu</h3><button class="close-btn" onclick="toggleSidebar()">&times;</button></div>
        <button class="nav-btn active" onclick="showForm('student_view')">My Dashboard</button>
        <a href="logout.php" class="nav-btn logout-btn">Logout</a>
    </nav>

    <div class="container">
        <div id="student_view" class="form-content active">
            <h2>My Student Dashboard</h2>
            <?php
                $student_id = $_SESSION['related_id'];
                $stmt = $conn->prepare("SELECT full_name FROM students WHERE id = ?");
                $stmt->bind_param("i", $student_id);
                $stmt->execute();
                $student_info = $stmt->get_result()->fetch_assoc();
                
                if ($student_info) {
                    echo "<h3 class='student-heading'>Welcome, " . htmlspecialchars($student_info['full_name']) . "</h3>";
                    
                    // Grades
                    echo "<h4 class='student-subheading'>My Grades</h4>";
                    $grade_sql = "SELECT s.name as subject, g.score, g.term FROM grades g JOIN subjects s ON g.subject_id = s.id WHERE g.student_id = ?";
                    $g_stmt = $conn->prepare($grade_sql);
                    $g_stmt->bind_param("i", $student_id);
                    $g_stmt->execute();
                    $grades = $g_stmt->get_result();
                    
                    if ($grades->num_rows > 0) {
                        echo "<table class='students-table student-table'><thead><tr><th>Subject</th><th>Term</th><th>Score</th></tr></thead><tbody>";
                        while($g = $grades->fetch_assoc()) {
                            echo "<tr><td>{$g['subject']}</td><td>{$g['term']}</td><td>{$g['score']}</td></tr>";
                        }
                        echo "</tbody></table>";
                    } else {
                        echo "<p>No grades found.</p>";
                    }

                    // Attendance
                    echo "<h4 class='student-subheading-secondary'>My Attendance</h4>";
                    $att_sql = "SELECT date, status FROM attendance WHERE student_id = ? ORDER BY date DESC LIMIT 10";
                    $att_stmt = $conn->prepare($att_sql);
                    $att_stmt->bind_param("i", $student_id);
                    $att_stmt->execute();
                    $att_res = $att_stmt->get_result();

                    if ($att_res->num_rows > 0) {
                        echo "<table class='students-table student-table'><thead><tr><th>Date</th><th>Status</th></tr></thead><tbody>";
                        while($a = $att_res->fetch_assoc()) {
                            $color = ($a['status'] == 'Present') ? '#2ecc71' : '#ff6b6b';
                            echo "<tr><td>{$a['date']}</td><td style='color:$color; font-weight:bold;'>{$a['status']}</td></tr>";
                        }
                        echo "</tbody></table>";
                    } else {
                        echo "<p>No attendance records found.</p>";
                    }
                }
            ?>
        </div>
    </div>
    <script src="script.js?v=<?php echo time(); ?>"></script>
</body>
</html>