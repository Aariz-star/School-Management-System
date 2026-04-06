<?php
// Enforce secure session settings
$cookieParams = session_get_cookie_params();
$is_https = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
session_set_cookie_params([
    'lifetime' => $cookieParams['lifetime'],
    'path' => $cookieParams['path'],
    'domain' => $cookieParams['domain'],
    'secure' => $is_https,
    'httponly' => true, // Prevent JavaScript access to session cookie
    'samesite' => 'Lax'
]);
session_start();

// Prevent browser caching (Security measure for Back/Forward buttons)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// If the user is already logged in, redirect them to their respective dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: index.php");
    } elseif ($_SESSION['role'] === 'teacher') {
        header("Location: teacher_dashboard.php");
    } elseif ($_SESSION['role'] === 'student') {
        header("Location: student_dashboard.php");
    }
    exit;
}

include 'config.php';

// Check for error message from previous attempt (Post-Redirect-Get pattern)
if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password, role, related_id, failed_attempts, locked_until, is_active FROM users WHERE username = ?");
    if (!$stmt) {
        // This handles the error if the table doesn't exist
        die("Database Error: " . $conn->error . "<br><strong>Hint: Did you run the AUTH_SETUP.sql script in phpMyAdmin?</strong>");
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password, $role, $related_id, $failed_attempts, $locked_until, $is_active);
        $stmt->fetch();

        // 1. Check if account is locked
        if ($locked_until && new DateTime($locked_until) > new DateTime()) {
            $_SESSION['login_error'] = "Account locked due to too many failed attempts. Please try again after 15 minutes.";
            header("Location: login.php");
            exit;
        }

        // 2. Check if account is active
        if ($is_active == 0) {
            $_SESSION['login_error'] = "Your account has been deactivated. Please contact administration.";
            header("Location: login.php");
            exit;
        }

        if (!empty($hashed_password) && password_verify($password, $hashed_password)) {
            // SUCCESS: Reset failures and update login info
            $reset_stmt = $conn->prepare("UPDATE users SET failed_attempts = 0, locked_until = NULL, last_login = NOW() WHERE id = ?");
            $reset_stmt->bind_param("i", $id);
            $reset_stmt->execute();
            $reset_stmt->close();

            // Security: Prevent Session Fixation
            session_regenerate_id(true);

            $_SESSION['user_id'] = $id;
            $_SESSION['role'] = $role;
            $_SESSION['related_id'] = $related_id; // ID of the specific teacher or student
            $_SESSION['username'] = $username; // Store username for display
            $_SESSION['last_activity'] = time(); // For session timeout
            
            // Remember Me: Extend session cookie lifetime to 30 days
            if (isset($_POST['remember_me'])) {
                $params = session_get_cookie_params();
                setcookie(session_name(), session_id(), time() + (30 * 24 * 60 * 60), $params["path"], $params["domain"], isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', true);
            }
            
            header("Location: index.php");
            exit;
        } else {
            // FAILURE: Increment failed attempts
            $failed_attempts++;
            $lock_time = null;
            if ($failed_attempts >= 5) {
                $lock_time = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            }
            
            $fail_stmt = $conn->prepare("UPDATE users SET failed_attempts = ?, locked_until = ? WHERE id = ?");
            $fail_stmt->bind_param("isi", $failed_attempts, $lock_time, $id);
            $fail_stmt->execute();
        }
    }

    // If we reach here, login failed. Set a generic error for security.
    $_SESSION['login_error'] = "Invalid username or password.";
    $stmt->close();
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="logo.jpg" type="image/jpeg">
    <title>Login - School Management System</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="animations.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="loader.css?v=<?php echo time(); ?>">
</head>
<body class="login-body">

    <!-- Page Pre-Loader -->
    <div class="loader-wrapper">
        <div class="loading">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
    <div class="login-container">
        <div class="login-header">
            <h1>Ideal Model School</h1>
            <p>Please sign in to continue</p>
        </div>
        <form class="form-content active" method="post" autocomplete="off">
            <h2 style="text-align: center; font-size: 1.5rem;">Sign In</h2>
            
            <?php if (isset($error)): ?>
                <div style="background: #ff4444; color: white; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="form-grid" style="grid-template-columns: 1fr;">
                <input type="text" name="username" placeholder="Username" required autocomplete="off">
                
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" placeholder="Password" required autocomplete="new-password">
                    <button type="button" class="toggle-password" onclick="togglePassword()" title="Show/Hide Password">
                        <!-- Eye Icon (SVG) -->
                        <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>

            <div class="remember-me-container">
                <input type="checkbox" name="remember_me" id="remember_me">
                <label for="remember_me">Remember me</label>
            </div>

            <div style="margin-top: 1rem; text-align: center;">
                <a href="forgot_password.php" style="color: #00d4ff; text-decoration: none; font-size: 0.9rem; transition: color 0.3s;">Forgot Password?</a>
            </div>
            
            <button class="submit-btn" type="submit">Sign In</button>
        </form>
    </div>
    <script>
        // Page loader
        window.addEventListener('load', function() {
            const loader = document.querySelector('.loader-wrapper');
            if (loader) {
                loader.classList.add('hidden');
            }
        });
    </script>
    <script src="animations.js?v=<?php echo time(); ?>"></script>
</body>

</html>