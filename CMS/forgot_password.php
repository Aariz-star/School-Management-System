<?php
// Enforce secure session settings to match login.php
$cookieParams = session_get_cookie_params();
$is_https = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
session_set_cookie_params(
    $cookieParams['lifetime'],
    $cookieParams['path'] . '; samesite=Lax',
    $cookieParams['domain'],
    $is_https,
    true
);
session_start();
include 'config.php';

// Load PHPMailer (Assuming you pasted the 'PHPMailer' folder in the root)
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// FIX: Reset session if visiting via GET (fresh load) to prevent getting stuck
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    unset($_SESSION['fp_step']);
    unset($_SESSION['fp_otp']);
    unset($_SESSION['fp_user_id']);
    unset($_SESSION['fp_email']);
}

// Initialize variables
$step = isset($_SESSION['fp_step']) ? $_SESSION['fp_step'] : 1;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ─────────────────────────────────────────────────────────────
    // STEP 1: Verify Email & Identify Admin
    // ─────────────────────────────────────────────────────────────
    if (isset($_POST['action']) && $_POST['action'] === 'verify_email') {
        $email = trim($_POST['email']);
        
        // Check if there is an ADMIN linked to this email
        // 1. Check if user is linked to a Teacher with this email AND is an Admin
        $sql = "SELECT u.id, u.username, t.email 
                FROM users u 
                JOIN teachers t ON u.related_id = t.id 
                WHERE t.email = ? AND u.role = 'admin'";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $admin_found = false;
        $user_id = 0;

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $user_id = $row['id'];
            $admin_found = true;
        } else {
            // 2. Check if the username in users table IS the email (for direct admins)
            $stmt2 = $conn->prepare("SELECT id FROM users WHERE username = ? AND role = 'admin'");
            $stmt2->bind_param("s", $email);
            $stmt2->execute();
            $res2 = $stmt2->get_result();
            if ($res2->num_rows > 0) {
                $row = $res2->fetch_assoc();
                $user_id = $row['id'];
                $admin_found = true;
            }
        }

        if ($admin_found) {
            // Generate OTP
            $otp = rand(100000, 999999);
            
            // Send Real Email
            $subject = "Password Reset OTP - Ideal Model School";
            
            // Send Email using PHPMailer
            $mail_sent = false;
            $mail = new PHPMailer(true);

            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'azwalker257@gmail.com'; 
                $mail->Password   = 'poskvmkbevkbhgws';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Recipients
                $mail->setFrom('azwalker257@gmail.com', 'Ideal Model School');
                $mail->addAddress($email);

                // Content
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = "<h3>Password Reset Request</h3><p>Your One-Time Password (OTP) is: <strong style='font-size:1.5em; color:#00d4ff;'>$otp</strong></p><p>If you did not request this, please ignore this email.</p>";
                $mail->AltBody = "Your OTP is: $otp";

                $mail->send();
                $mail_sent = true;
            } catch (Exception $e) {
                // Mail failed, fallback to logging
            }
            
            // ALWAYS log OTP to file (fail-safe for localhost)
            $log_file = 'email_debug_log.txt';
            $log_entry = "[" . date('Y-m-d H:i:s') . "] To: $email | From: azwalker257@gmail.com | OTP: $otp | Mail Status: " . ($mail_sent ? "Sent (PHPMailer)" : "Failed") . PHP_EOL;
            file_put_contents($log_file, $log_entry, FILE_APPEND);

            // Proceed to Step 2 regardless of email success (so user can use the log file OTP)
            $_SESSION['fp_otp'] = $otp;
            $_SESSION['fp_user_id'] = $user_id;
            $_SESSION['fp_email'] = $email;
            $_SESSION['fp_step'] = 2;
            $step = 2;

            if ($mail_sent) {
                $success = "An OTP has been sent to <strong>" . htmlspecialchars($email) . "</strong>. Please check your inbox (and spam folder).<br><small>(If not received, check <code>email_debug_log.txt</code> in project folder)</small>";
            } else {
                $error = "Email sending failed (Server Config).<br><strong>Debug Mode:</strong> OTP saved to <code>email_debug_log.txt</code>.";
            }
        } else {
            // Check if email exists for non-admin to give specific message
            // Or just give generic "Contact Admin" message
            $error = "If you are a student or teacher, please contact the Administration to reset your password.<br><br>Only Administrators can reset their password here.";
        }
    }

    // ─────────────────────────────────────────────────────────────
    // STEP 2: Verify OTP
    // ─────────────────────────────────────────────────────────────
    elseif (isset($_POST['action']) && $_POST['action'] === 'verify_otp') {
        $otp_input = trim($_POST['otp']);
        
        if ($otp_input == $_SESSION['fp_otp']) {
            $_SESSION['fp_step'] = 3;
            $step = 3;
            $success = "OTP Verified. Please enter your new password.";
        } else {
            $error = "Invalid OTP. Please try again.";
            $step = 2;
        }
    }

    // ─────────────────────────────────────────────────────────────
    // STEP 3: Reset Password
    // ─────────────────────────────────────────────────────────────
    elseif (isset($_POST['action']) && $_POST['action'] === 'reset_password') {
        $pass1 = $_POST['new_password'];
        $pass2 = $_POST['confirm_password'];
        
        if ($pass1 === $pass2) {
            if (strlen($pass1) < 6) {
                $error = "Password must be at least 6 characters.";
                $step = 3;
            } else {
                $new_hash = password_hash($pass1, PASSWORD_DEFAULT);
                $uid = $_SESSION['fp_user_id'];
                
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $new_hash, $uid);
                
                if ($stmt->execute()) {
                    // Clear session
                    unset($_SESSION['fp_step']);
                    unset($_SESSION['fp_otp']);
                    unset($_SESSION['fp_user_id']);
                    unset($_SESSION['fp_email']);
                    
                    $_SESSION['success'] = "Password reset successfully! Please login.";
                    header("Location: login.php");
                    exit;
                } else {
                    $error = "Database error.";
                    $step = 3;
                }
            }
        } else {
            $error = "Passwords do not match.";
            $step = 3;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - School Management</title>
    <link rel="stylesheet" href="styles.css?v=1.0.0">
    <style>
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .container-fp { width: 100%; max-width: 450px; padding: 1rem; }
        .back-link { display: block; text-align: center; margin-top: 1rem; color: #666; text-decoration: none; }
        .back-link:hover { color: #00d4ff; }
    </style>
</head>
<body>
    <div class="container-fp">
        <form class="form-content active" method="post">
            <h2 style="text-align: center; border:none; margin-bottom: 1rem;">Reset Password</h2>

            <?php if ($error): ?>
                <div class="notification notification-error" style="position:static; width:100%; margin-bottom:1rem; transform:none; animation:none;">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="notification notification-success" style="position:static; width:100%; margin-bottom:1rem; transform:none; animation:none;">
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <!-- STEP 1: Email Input -->
            <?php if ($step == 1): ?>
                <p style="text-align:center; color:#ccc; margin-bottom:1.5rem;">Enter your registered email address.</p>
                <input type="hidden" name="action" value="verify_email">
                <div class="form-grid" style="grid-template-columns: 1fr;">
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>
                <button class="submit-btn" type="submit">Send OTP</button>

            <!-- STEP 2: OTP Input -->
            <?php elseif ($step == 2): ?>
                <p style="text-align:center; color:#ccc; margin-bottom:1.5rem;">Enter the OTP sent to <strong><?= htmlspecialchars($_SESSION['fp_email']) ?></strong></p>
                <input type="hidden" name="action" value="verify_otp">
                <div class="form-grid" style="grid-template-columns: 1fr;">
                    <input type="number" name="otp" placeholder="Enter 6-digit OTP" required>
                </div>
                <button class="submit-btn" type="submit">Verify OTP</button>

            <!-- STEP 3: New Password -->
            <?php elseif ($step == 3): ?>
                <p style="text-align:center; color:#ccc; margin-bottom:1.5rem;">Create a new password.</p>
                <input type="hidden" name="action" value="reset_password">
                <div class="form-grid" style="grid-template-columns: 1fr;">
                    <input type="password" name="new_password" placeholder="New Password" required minlength="6">
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required minlength="6">
                </div>
                <button class="submit-btn" type="submit">Update Password</button>
            <?php endif; ?>

            <a href="login.php" class="back-link">Back to Login</a>
        </form>
    </div>
</body>
</html>