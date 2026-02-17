<?php
require_once 'student_dashboard_logic.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Ideal Model School</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="student_dashboard.css?v=<?php echo time(); ?>">
</head>
<body>
    <!-- Success/Error Notification -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="notification notification-success" id="notification">
            <strong>✓ Request Submitted</strong>
            <p><?php echo htmlspecialchars($_SESSION['success']); ?></p>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="notification notification-error" id="notification">
            <strong>✗ Error</strong>
            <p><?php echo htmlspecialchars($_SESSION['error']); ?></p>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
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
        <button class="nav-btn active" onclick="showForm('dashboard')">Home Dashboard</button>
        <button class="nav-btn" onclick="showForm('fees')">My Fees</button>
        <button class="nav-btn" onclick="showForm('classes')">My Classes</button>
        <button class="nav-btn" onclick="showForm('homework')">Homework</button>
        <button class="nav-btn" onclick="showForm('attendance')">Attendance</button>
        <button class="nav-btn" onclick="showForm('results')">Results</button>
        <button class="nav-btn" onclick="showForm('timetable')">Timetable</button>
        <button class="nav-btn" onclick="showForm('messages')">Messages</button>
        <a href="logout.php" class="nav-btn logout-btn">Logout</a>
    </nav>

    <div class="container">
        
        <!-- 1. HOME DASHBOARD -->
        <div id="dashboard" class="form-content active">
            <h2>Home Dashboard</h2>
            <h3 class='student-heading'>Welcome back, <?= htmlspecialchars($student_info['full_name']) ?></h3>
            <p>Class: <?= htmlspecialchars($student_info['class_name']) ?></p>

            <div class="dashboard-grid">
                <!-- Attendance Card -->
                <div class="dashboard-card">
                    <div class="card-header"><h3>Attendance</h3></div>
                    <div class="stat-number <?= $att_percent >= 75 ? 'text-success' : 'text-danger' ?>">
                        <?= $att_percent ?>%
                    </div>
                    <div class="stat-label">Monthly Attendance</div>
                </div>

                <!-- Announcements Card -->
                <div class="dashboard-card">
                    <div class="card-header"><h3>Announcements</h3></div>
                    <?php if ($announcements_res && $announcements_res->num_rows > 0): ?>
                        <?php while($ann = $announcements_res->fetch_assoc()): ?>
                            <div class="list-item">
                                <span class="item-title"><?= htmlspecialchars($ann['title']) ?></span>
                                <span class="item-meta"><?= $ann['publish_date'] ?></span>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="empty-state">No new announcements.</p>
                    <?php endif; ?>
                </div>

                <!-- Today's Classes (Mock based on subjects) -->
                <div class="dashboard-card">
                    <div class="card-header"><h3>My Subjects</h3></div>
                    <?php 
                    $my_classes->data_seek(0); // Reset pointer
                    $count = 0;
                    while($sub = $my_classes->fetch_assoc()): 
                        if($count++ > 4) break;
                    ?>
                        <div class="list-item">
                            <span class="item-title"><?= htmlspecialchars($sub['subject_name']) ?></span>
                            <span class="item-meta"><?= htmlspecialchars($sub['teacher_name'] ?? 'No Teacher') ?></span>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- 1.5 MY FEES -->
        <div id="fees" class="form-content">
            <h2>My Fees & Invoices</h2>
            <div class="dashboard-grid">
                <?php foreach($fee_data as $inv): ?>
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><?= htmlspecialchars($inv['title']) ?></h3>
                        <span class="invoice-status" data-status="<?= $inv['status'] ?>"><?= $inv['status'] ?></span>
                    </div>
                    
                    <div class="invoice-number-box">
                        <p>Invoice No.</p>
                        <p class="invoice-number"><?= htmlspecialchars($inv['invoice_number'] ?? 'N/A') ?></p>
                    </div>
                    
                    <p>Amount: <strong>Rs. <?= number_format($inv['amount']) ?></strong></p>
                    <p>Due Date: <strong><?= $inv['due_date'] ?></strong></p>
                    
                    <div class="bank-details">
                        <p class="bank-name"><strong>Bank: HBL</strong></p>
                        <p>Acc: 1234-5678-9012</p>
                        <p class="bank-note">Use Invoice No. as reference for offline payments</p>
                    </div>

                    <?php if ($inv['status'] !== 'Paid'): ?>
                        <form action="student_fee_upload.php" method="post" enctype="multipart/form-data" class="fee-upload-form">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="invoice_id" value="<?= $inv['id'] ?>">
                            <input type="text" name="reference_no" placeholder="Transaction Ref No. / Invoice No." required class="fee-input">
                            <label class="file-upload-label">
                                <span class="upload-text">📷 Upload Screenshot</span>
                                <input type="file" name="proof_image" accept="image/*" required class="file-input">
                            </label>
                            <button type="submit" class="submit-btn submit-btn-full">Submit Payment</button>
                        </form>
                    <?php else: ?>
                        <a href="generate_receipt.php?invoice_id=<?= $inv['id'] ?>" target="_blank" class="submit-btn submit-btn-secondary">Download Receipt ⬇</a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if (empty($fee_data)): ?>
                <p class="empty-state">No invoices found.</p>
            <?php endif; ?>
        </div>

        <!-- 2. MY CLASSES -->
        <div id="classes" class="form-content">
            <h2>My Classes</h2>
            <table class="students-table">
                <thead><tr><th>Subject</th><th>Teacher</th><th>Book / Material</th></tr></thead>
                <tbody>
                    <?php 
                    $my_classes->data_seek(0);
                    if ($my_classes->num_rows > 0):
                        while($c = $my_classes->fetch_assoc()): ?>
                        <tr>
                            <td data-label="Subject"><?= htmlspecialchars($c['subject_name']) ?></td>
                            <td data-label="Teacher"><?= htmlspecialchars($c['teacher_name'] ?? 'Not Assigned') ?></td>
                            <td data-label="Material"><?= htmlspecialchars($c['book_name'] ?? '-') ?></td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="3" class="empty-state">No classes assigned yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- 3. HOMEWORK -->
        <div id="homework" class="form-content">
            <h2>Homework & Assignments</h2>
            <?php if ($homework_res && $homework_res->num_rows > 0): ?>
                <div class="dashboard-grid">
                    <?php while($hw = $homework_res->fetch_assoc()): ?>
                        <div class="dashboard-card">
                            <div class="card-header"><h3><?= htmlspecialchars($hw['subject_name']) ?></h3></div>
                            <p><strong><?= htmlspecialchars($hw['title']) ?></strong></p>
                            <p><?= nl2br(htmlspecialchars($hw['description'])) ?></p>
                            <div class="list-item">
                                <span class="item-meta">Due: <?= $hw['due_date'] ?></span>
                                <span class="item-meta text-pending">Pending</span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="empty-state">No homework assigned.</p>
            <?php endif; ?>
        </div>

        <!-- 4. ATTENDANCE -->
        <div id="attendance" class="form-content">
            <h2>Attendance Record</h2>
            <div class="dashboard-grid" style="margin-bottom: 2rem;">
                <div class="dashboard-card text-center">
                    <div class="stat-number text-success"><?= $att_stats['present'] ?? 0 ?></div>
                    <div class="stat-label">Days Present</div>
                </div>
                <div class="dashboard-card text-center">
                    <div class="stat-number text-danger"><?= $att_stats['absent'] ?? 0 ?></div>
                    <div class="stat-label">Days Absent</div>
                </div>
                <div class="dashboard-card text-center">
                    <div class="stat-number"><?= $att_stats['total'] ?? 0 ?></div>
                    <div class="stat-label">Total Working Days</div>
                </div>
            </div>
        </div>

        <!-- 5. RESULTS -->
        <div id="results" class="form-content">
            <h2>Exam Results</h2>
            <?php 
            // Buffer grades to array to extract terms
            $grades_data = [];
            $terms = [];
            if ($grades_res && $grades_res->num_rows > 0) {
                while($row = $grades_res->fetch_assoc()) {
                    $grades_data[] = $row;
                    if (!in_array($row['term'], $terms)) {
                        $terms[] = $row['term'];
                    }
                }
            }
            ?>

            <?php if (!empty($grades_data)): ?>
                <div class="filter-box">
                    <label>Filter by Term:</label>
                    <select id="termFilter" onchange="filterResults()" class="term-filter">
                        <option value="">-- Select Term --</option>
                        <?php foreach($terms as $t): ?>
                            <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="termSummary" class="term-summary" style="display: none;">
                    <h3>Result Summary</h3>
                    <div class="summary-stats">
                        <div>Total Obtained: <strong id="summaryObtained">0</strong> / <span id="summaryTotal">0</span></div>
                        <div>Percentage: <strong id="summaryPercentage">0%</strong></div>
                        <div>Result: <strong id="summaryStatus"></strong></div>
                    </div>
                </div>

                <table class="students-table" id="resultsTable">
                    <thead><tr><th>Subject</th><th>Teacher</th><th>Term</th><th>Score</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach($grades_data as $g): 
                            $status = $g['score'] >= 40 ? 'Pass' : 'Fail';
                            $status_class = $g['score'] >= 40 ? 'text-success' : 'text-danger';
                        ?>
                            <tr class="result-row" data-term="<?= htmlspecialchars($g['term']) ?>">
                                <td data-label="Subject"><?= htmlspecialchars($g['subject']) ?></td>
                                <td data-label="Teacher"><?= htmlspecialchars($g['teacher_name'] ?? 'N/A') ?></td>
                                <td data-label="Term"><?= htmlspecialchars($g['term']) ?></td>
                                <td data-label="Score" class="score-cell"><?= $g['score'] ?></td>
                                <td data-label="Status" class="status-cell <?= $status_class ?> font-bold"><?= $status ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty-state">No grades available.</p>
            <?php endif; ?>
        </div>

        <!-- 6. TIMETABLE -->
        <div id="timetable" class="form-content">
            <h2>Weekly Timetable</h2>
            <?php if ($timetable_res && $timetable_res->num_rows > 0): ?>
                <!-- Complex grid implementation would go here, simplified list for now -->
                <table class="students-table">
                    <thead><tr><th>Day</th><th>Time</th><th>Subject</th></tr></thead>
                    <tbody>
                        <?php while($tt = $timetable_res->fetch_assoc()): ?>
                            <tr>
                                <td><?= $tt['day_of_week'] ?></td>
                                <td><?= date('h:i A', strtotime($tt['start_time'])) ?> - <?= date('h:i A', strtotime($tt['end_time'])) ?></td>
                                <td><?= htmlspecialchars($tt['subject_name']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty-state">Timetable not uploaded yet.</p>
            <?php endif; ?>
        </div>

        <!-- 7. MESSAGES -->
        <div id="messages" class="form-content">
            <h2>Messages from Teachers</h2>
            <?php if ($messages_res && $messages_res->num_rows > 0): ?>
                <!-- Message list implementation -->
            <?php else: ?>
                <p class="empty-state">No new messages.</p>
            <?php endif; ?>
        </div>
    </div>
    <script src="script.js?v=<?php echo time(); ?>"></script>
    <script src="student_dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>