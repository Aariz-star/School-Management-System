<?php
ob_start();
// fee_backend.php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    ob_end_clean();
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    $action = $_POST['action'];

    // 1. Generate Bulk Invoices
    if ($action === 'generate_bulk') {
        $class_id = (int)$_POST['class_id'];
        $title = trim($_POST['title']);
        $amount = (float)$_POST['amount'];
        $due_date = $_POST['due_date'];

        // Get all enrolled students in the class (including those without active accounts)
        $stmt = $conn->prepare("SELECT s.id, c.id as class_id FROM students s JOIN classes c ON s.class_id = c.id WHERE s.class_id = ? AND s.deleted_at IS NULL");
        $stmt->bind_param("i", $class_id);
        $stmt->execute();
        $res = $stmt->get_result();
        
        $count = 0;
        $ins = $conn->prepare("INSERT INTO fee_invoices (student_id, invoice_number, title, amount, due_date) VALUES (?, ?, ?, ?, ?)");
        
        // Get the class reference (like "5th" or "8th") for invoice numbering
        $class_res = $conn->query("SELECT name FROM classes WHERE id = $class_id");
        $class_name = $class_res->fetch_assoc()['name'];
        // Extract numeric part from class name (e.g., "5th" -> "5")
        $class_num = preg_replace('/[^0-9]/', '', $class_name);
        
        while($row = $res->fetch_assoc()) {
            // Generate invoice number: CLASS-SEQUENCE (e.g., 5-001, 5-002, etc.)
            // Get the count of existing invoices for this student to create sequential numbers
            $seq_res = $conn->query("SELECT COUNT(*) + 1 as next_seq FROM fee_invoices WHERE student_id = " . $row['id']);
            $next_seq = $seq_res->fetch_assoc()['next_seq'];
            $invoice_number = $class_num . "-" . str_pad($next_seq, 3, '0', STR_PAD_LEFT);
            
            $ins->bind_param("issds", $row['id'], $invoice_number, $title, $amount, $due_date);
            $ins->execute();
            $count++;
        }
        
        $_SESSION['success'] = "Generated $count invoices successfully!";
        header("Location: index.php");
        exit;
    }

    // 2. Verify Online Payment
    if ($action === 'verify_payment') {
        $payment_id = (int)$_POST['payment_id'];
        $status = $_POST['status']; // Verified or Rejected
        $admin_id = $_SESSION['user_id'];

        // Update Payment Status
        $stmt = $conn->prepare("UPDATE fee_payments SET status = ?, collected_by = ? WHERE id = ?");
        $stmt->bind_param("sii", $status, $admin_id, $payment_id);
        $stmt->execute();

        if ($status === 'Verified') {
            // Update Invoice Status Logic
            // 1. Get invoice ID and amount
            $p_res = $conn->query("SELECT invoice_id, amount FROM fee_payments WHERE id = $payment_id");
            $p_row = $p_res->fetch_assoc();
            $invoice_id = $p_row['invoice_id'];

            // 2. Check total due for this invoice
            $inv_res = $conn->query("SELECT amount FROM fee_invoices WHERE id = $invoice_id");
            $inv_row = $inv_res->fetch_assoc();
            $total_due = $inv_row['amount'];

            // 3. Calculate total paid so far (sum of all verified payments for this invoice)
            $sum_res = $conn->query("SELECT SUM(amount) as paid FROM fee_payments WHERE invoice_id = $invoice_id AND status = 'Verified'");
            $total_paid = $sum_res->fetch_assoc()['paid'];

            // 4. Determine new status
            $new_status = ($total_paid >= $total_due) ? 'Paid' : 'Partially Paid';
            
            $conn->query("UPDATE fee_invoices SET status = '$new_status' WHERE id = $invoice_id");
        }

        $_SESSION['success'] = "Payment marked as $status.";
        header("Location: index.php");
        exit;
    }

    // 3. Cash Collection
    if ($action === 'collect_cash') {
        $student_id = (int)$_POST['student_id'];
        $amount = (float)$_POST['amount'];
        $invoice_id = !empty($_POST['invoice_id']) ? (int)$_POST['invoice_id'] : 0;
        $admin_id = $_SESSION['user_id'];

        // If no invoice ID provided, find oldest unpaid invoice
        if ($invoice_id === 0) {
            $find = $conn->query("SELECT id FROM fee_invoices WHERE student_id = $student_id AND status != 'Paid' ORDER BY due_date ASC LIMIT 1");
            if ($find->num_rows > 0) {
                $invoice_id = $find->fetch_assoc()['id'];
            } else {
                $_SESSION['error'] = "No unpaid invoices found for this student.";
                header("Location: index.php");
                exit;
            }
        }

        // Record Payment
        $stmt = $conn->prepare("INSERT INTO fee_payments (invoice_id, amount, method, status, collected_by) VALUES (?, ?, 'Cash', 'Verified', ?)");
        $stmt->bind_param("idi", $invoice_id, $amount, $admin_id);
        
        if ($stmt->execute()) {
            // Update Invoice Status
            $inv_res = $conn->query("SELECT amount FROM fee_invoices WHERE id = $invoice_id");
            $total_due = $inv_res->fetch_assoc()['amount'];

            $sum_res = $conn->query("SELECT SUM(amount) as paid FROM fee_payments WHERE invoice_id = $invoice_id AND status = 'Verified'");
            $total_paid = $sum_res->fetch_assoc()['paid'];

            $new_status = ($total_paid >= $total_due) ? 'Paid' : 'Partially Paid';
            $conn->query("UPDATE fee_invoices SET status = '$new_status' WHERE id = $invoice_id");

            $_SESSION['success'] = "Cash collected successfully!";
        } else {
            $_SESSION['error'] = "Error recording payment.";
        }
        header("Location: index.php");
        exit;
    }
    // 4. Delete Invoice
    if ($action === 'delete_invoice') {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        
        $invoice_id = (int)$_POST['invoice_id'];
        
        // Check if invoice has any verified payments
        $check = $conn->query("SELECT COUNT(*) as cnt FROM fee_payments WHERE invoice_id = $invoice_id AND status = 'Verified'");
        $has_payments = $check->fetch_assoc()['cnt'] ?? 0;
        
        if ($has_payments > 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Cannot delete invoice with verified payments.']);
            exit;
        }
        
        // Delete associated pending/rejected payments first
        $conn->query("DELETE FROM fee_payments WHERE invoice_id = $invoice_id AND status IN ('Pending', 'Rejected')");
        
        // Delete the invoice
        $stmt = $conn->prepare("DELETE FROM fee_invoices WHERE id = ?");
        $stmt->bind_param("i", $invoice_id);
        
        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Invoice deleted successfully!']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Failed to delete invoice.']);
        }
        exit;
    }

    // 5. Bulk Edit Invoice
    if ($action === 'bulk_edit_invoice') {
        $invoice_id = (int)$_POST['invoice_id'];
        $new_title = isset($_POST['new_title']) && !empty(trim($_POST['new_title'])) ? trim($_POST['new_title']) : null;
        $new_amount = isset($_POST['new_amount']) && !empty($_POST['new_amount']) ? (float)$_POST['new_amount'] : null;
        
        if (!$new_title && !$new_amount) {
            $_SESSION['error'] = "Please enter at least title or amount to update.";
            header("Location: index.php?return_to=fee");
            exit;
        }
        
        // Get template invoice details
        $template = $conn->query("SELECT amount, title FROM fee_invoices WHERE id = $invoice_id");
        if (!$template || $template->num_rows === 0) {
            $_SESSION['error'] = "Invoice not found.";
            header("Location: index.php?return_to=fee");
            exit;
        }
        
        $template_row = $template->fetch_assoc();
        $update_title = $new_title ?? $template_row['title'];
        $update_amount = $new_amount ?? $template_row['amount'];
        
        // Update all invoices with same title and original amount (identifies template set)
        $stmt = $conn->prepare("UPDATE fee_invoices SET title = ?, amount = ? WHERE title = ? AND amount = ?");
        $stmt->bind_param("sdsd", $update_title, $update_amount, $template_row['title'], $template_row['amount']);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Invoice updated for all students!";
        } else {
            $_SESSION['error'] = "Error updating invoices.";
        }
        
        header("Location: index.php?return_to=fee");
        exit;
    }

    // 6. Single Student Invoice Edit
    if ($action === 'single_edit_invoice') {
        $invoice_id = (int)$_POST['invoice_id'];
        $new_title = isset($_POST['new_title']) && !empty(trim($_POST['new_title'])) ? trim($_POST['new_title']) : null;
        $new_amount = isset($_POST['new_amount']) && !empty($_POST['new_amount']) ? (float)$_POST['new_amount'] : null;
        
        if (!$new_title && !$new_amount) {
            $_SESSION['error'] = "Please enter at least title or amount to update.";
            header("Location: index.php?return_to=fee");
            exit;
        }
        
        // Update specific invoice
        if ($new_title && $new_amount) {
            $stmt = $conn->prepare("UPDATE fee_invoices SET title = ?, amount = ? WHERE id = ?");
            $stmt->bind_param("sdi", $new_title, $new_amount, $invoice_id);
        } elseif ($new_title) {
            $stmt = $conn->prepare("UPDATE fee_invoices SET title = ? WHERE id = ?");
            $stmt->bind_param("si", $new_title, $invoice_id);
        } else {
            $stmt = $conn->prepare("UPDATE fee_invoices SET amount = ? WHERE id = ?");
            $stmt->bind_param("di", $new_amount, $invoice_id);
        }
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Invoice updated successfully!";
        } else {
            $_SESSION['error'] = "Error updating invoice.";
        }
        
        header("Location: index.php?return_to=fee");
        exit;
    }
}