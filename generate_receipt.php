<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$invoice_id = isset($_GET['invoice_id']) ? (int)$_GET['invoice_id'] : 0;

if ($invoice_id <= 0) {
    die("Invalid Invoice ID.");
}

// 1. Fetch Invoice, Student, Class, and Guardian Details
$sql = "SELECT i.*, s.id as student_id, s.full_name, s.class_id, c.name as class_name, g.guardian_name
        FROM fee_invoices i
        JOIN students s ON i.student_id = s.id
        JOIN classes c ON s.class_id = c.id
        LEFT JOIN guardians g ON s.guardian_id = g.id
        WHERE i.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();

if (!$invoice) {
    die("Invoice not found.");
}

// Access Control: Student can only view their own receipt
if ($_SESSION['role'] === 'student' && $_SESSION['related_id'] != $invoice['student_id']) {
    die("Access Denied.");
}

// 2. Fetch Verified Payments for this Invoice
$pay_sql = "SELECT * FROM fee_payments WHERE invoice_id = ? AND status = 'Verified' ORDER BY payment_date DESC";
$pay_stmt = $conn->prepare($pay_sql);
$pay_stmt->bind_param("i", $invoice_id);
$pay_stmt->execute();
$payments = $pay_stmt->get_result();

// 3. Calculate Dynamic Roll Number
$class_id = $invoice['class_id'];
$s_sql = "SELECT id FROM students WHERE class_id = ? AND deleted_at IS NULL ORDER BY full_name";
$s_stmt = $conn->prepare($s_sql);
$s_stmt->bind_param("i", $class_id);
$s_stmt->execute();
$s_res = $s_stmt->get_result();

$counter = 1;
$roll_no = "N/A";
while($row = $s_res->fetch_assoc()) {
    if ($row['id'] == $invoice['student_id']) {
        $class_num = preg_replace('/[^0-9]/', '', $invoice['class_name']);
        $prefix = ($class_num !== '') ? $class_num : $class_id;
        $roll_no = $prefix . str_pad($counter, 2, '0', STR_PAD_LEFT);
        break;
    }
    $counter++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="logo.jpg" type="image/jpeg">
    <meta charset="UTF-8">
    <title>Fee Receipt - <?= htmlspecialchars($invoice['title']) ?></title>
    <link rel="stylesheet" href="reports.css?v=<?php echo time(); ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body>

<div class="receipt-container" id="receipt-content">
    <div class="header">
        <h1>Ideal Model School</h1>
        <p>Official Fee Receipt</p>
        <p>Date: <?= date('d M Y') ?></p>
    </div>

    <div class="info-grid">
        <div class="info-item"><label>Student Name</label><span><?= htmlspecialchars($invoice['full_name']) ?></span></div>
        <div class="info-item"><label>Father Name</label><span><?= htmlspecialchars($invoice['guardian_name']) ?></span></div>
        <div class="info-item"><label>Class</label><span><?= htmlspecialchars($invoice['class_name']) ?></span></div>
        <div class="info-item"><label>Roll Number</label><span><?= $roll_no ?></span></div>
        <div class="info-item"><label>Invoice Title</label><span><?= htmlspecialchars($invoice['title']) ?></span></div>
        <div class="info-item"><label>Invoice Number</label><span style="background:#00d4ff; color:#000; padding:0.3rem 0.6rem; border-radius:3px; font-weight:bold; letter-spacing:1px;"><?= htmlspecialchars($invoice['invoice_number'] ?? 'N/A') ?></span></div>
        <div class="info-item"><label>Invoice Amount</label><span>Rs. <?= number_format($invoice['amount']) ?></span></div>
        <div class="info-item"><label>Status</label><span class="status-badge">PAID</span></div>
    </div>

    <h3>Payment Details</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Transaction ID</th>
                <th>Payment Type</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $total_paid = 0;
            if ($payments->num_rows > 0):
                while($pay = $payments->fetch_assoc()): 
                    $total_paid += $pay['amount'];
                    $method_display = ($pay['method'] === 'Bank Transfer') ? 'Online (Bank Transfer)' : 'Physical (Cash)';
            ?>
                <tr>
                    <td><?= date('d-m-Y', strtotime($pay['payment_date'])) ?></td>
                    <td><?= htmlspecialchars($pay['reference_no'] ?? '-') ?></td>
                    <td><?= $method_display ?></td>
                    <td>Rs. <?= number_format($pay['amount']) ?></td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="4">No verified payments found.</td></tr>
            <?php endif; ?>
            <tr>
                <td colspan="3" style="text-align:right; font-weight:bold;">Total Paid</td>
                <td style="font-weight:bold;">Rs. <?= number_format($total_paid) ?></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>This is a computer-generated receipt and does not require a physical signature.</p>
        <p>Ideal Model School | Contact: 0300-1234567</p>
    </div>
</div>

<script>
    // Pass filename to external JS
    window.receiptFilename = 'Receipt_<?= $invoice_id ?>_<?= preg_replace("/[^a-zA-Z0-9]/", "", $invoice["full_name"]) ?>.png';
</script>
<script src="receipt.js?v=<?php echo time(); ?>"></script>
</body>
</html>