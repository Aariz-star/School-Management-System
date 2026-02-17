<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
$term = isset($_GET['term']) ? trim($_GET['term']) : '';

if ($_SESSION['role'] === 'student' && $_SESSION['related_id'] != $student_id) {
    die("Access Denied: You can only view your own DMC.");
}

if ($student_id <= 0 || empty($term)) {
    die("Invalid Request. Student ID and Term are required.");
}

// Fetch Student Info
$stmt = $conn->prepare("SELECT s.full_name, s.contact_number, g.guardian_name, c.name as class_name 
                        FROM students s 
                        LEFT JOIN guardians g ON s.guardian_id = g.id 
                        LEFT JOIN classes c ON s.class_id = c.id 
                        WHERE s.id = ? AND s.deleted_at IS NULL");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) die("Student not found.");

// Fetch Grades
$g_stmt = $conn->prepare("SELECT s.name as subject, gr.score 
                          FROM grades gr 
                          JOIN subjects s ON gr.subject_id = s.id 
                          WHERE gr.student_id = ? AND gr.term = ?");
$g_stmt->bind_param("is", $student_id, $term);
$g_stmt->execute();
$grades_res = $g_stmt->get_result();

$total_marks = 0;
$obtained_marks = 0;
$subject_count = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DMC - <?= htmlspecialchars($student['full_name']) ?></title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <style>
        * { box-sizing: border-box; }
        body {
            background: #0f0f0f;
            color: #e0e0e0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 2rem;
        }
        .dmc-container {
            max-width: 800px;
            margin: 0 auto;
            background: #1a1a1a;
            padding: 2rem;
            border: 1px solid #333;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
        }
        .header-section {
            text-align: center;
            border-bottom: 2px solid #00d4ff;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        .header-section h1 { color: #00d4ff; margin: 0; text-transform: uppercase; letter-spacing: 2px; }
        .header-section p { color: #999; margin: 5px 0 0; }
        
        .student-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .info-group label { color: #00d4ff; font-weight: bold; display: block; font-size: 0.9rem; }
        .info-group span { font-size: 1.1rem; }

        .marks-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }
        .marks-table th, .marks-table td {
            border: 1px solid #333;
            padding: 12px;
            text-align: left;
        }
        .marks-table th { background: #252525; color: #00d4ff; }
        .marks-table td { background: #1a1a1a; }
        .total-row td { font-weight: bold; color: #00d4ff; background: #252525; }

        .footer-section {
            margin-top: 3rem;
            display: flex;
            justify-content: space-between;
            padding-top: 1rem;
        }
        .signature {
            border-top: 1px solid #666;
            width: 200px;
            text-align: center;
            padding-top: 0.5rem;
            color: #999;
        }

        .print-btn {
            display: block;
            width: 100%;
            max-width: 200px;
            margin: 2rem auto 0;
            padding: 10px;
            background: linear-gradient(45deg, #00d4ff, #0056b3);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            text-align: center;
        }

        @media print {
            body { background: white; color: black; padding: 0; }
            .dmc-container { border: none; box-shadow: none; padding: 0; margin: 0; width: 100%; max-width: 100%; }
            .header-section h1, .info-group label, .marks-table th, .total-row td { color: black !important; }
            .header-section { border-bottom-color: black; }
            .marks-table th, .marks-table td { border-color: #000; }
            .print-btn { display: none; }
        }

        @media screen and (max-width: 600px) {
            body { padding: 10px; }
            .dmc-container { padding: 1rem; width: 100%; }
            .header-section h1 { font-size: 1.4rem; }
            .student-info { grid-template-columns: 1fr; gap: 0.5rem; }
            .info-group { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #333; padding-bottom: 4px; }
            .info-group label { margin-bottom: 0; font-size: 0.85rem; }
            .info-group span { font-size: 0.95rem; text-align: right; }
            .marks-table th, .marks-table td { padding: 8px 4px; font-size: 0.85rem; }
            .footer-section { flex-direction: column; gap: 2.5rem; margin-top: 2rem; }
            .signature { width: 80%; margin: 0 auto; }
            .print-btn { width: 100%; max-width: none; }
        }
    </style>
</head>
<body>
    <div class="dmc-container">
        <div class="header-section">
            <h1>Ideal Model School</h1>
            <p>Detailed Marks Certificate</p>
            <p>Term: <?= htmlspecialchars($term) ?></p>
        </div>

        <div class="student-info">
            <div class="info-group"><label>Student Name:</label><span><?= htmlspecialchars($student['full_name']) ?></span></div>
            <div class="info-group"><label>Father Name:</label><span><?= htmlspecialchars($student['guardian_name']) ?></span></div>
            <div class="info-group"><label>Class:</label><span><?= htmlspecialchars($student['class_name']) ?></span></div>
            <div class="info-group"><label>Roll No:</label><span><?= $student_id ?></span></div>
        </div>

        <table class="marks-table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Total Marks</th>
                    <th>Obtained Marks</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($grades_res->num_rows > 0) {
                    while($g = $grades_res->fetch_assoc()) {
                        $subject_total = 100; // Assuming 100 per subject
                        $total_marks += $subject_total;
                        $obtained_marks += $g['score'];
                        $subject_count++;
                        
                        $remarks = $g['score'] >= 40 ? 'Pass' : 'Fail';
                        
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($g['subject']) . "</td>";
                        echo "<td>$subject_total</td>";
                        echo "<td>{$g['score']}</td>";
                        echo "<td>$remarks</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align:center'>No grades found for this term.</td></tr>";
                }
                ?>
                <tr class="total-row">
                    <td>Total</td>
                    <td><?= $total_marks ?></td>
                    <td><?= $obtained_marks ?></td>
                    <td>
                        <?php 
                        if ($total_marks > 0) {
                            $perc = round(($obtained_marks / $total_marks) * 100, 2);
                            echo $perc . "%";
                        } else {
                            echo "-";
                        }
                        ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="footer-section">
            <div class="signature">Class Teacher</div>
            <div class="signature">Principal</div>
        </div>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <button class="print-btn" onclick="window.print()">Print DMC</button>
        <?php endif; ?>
    </div>
</body>
</html>