<?php
// Enforce secure session settings
$cookieParams = session_get_cookie_params();
session_set_cookie_params([
    'lifetime' => $cookieParams['lifetime'],
    'path' => $cookieParams['path'],
    'domain' => $cookieParams['domain'],
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', // Only set secure flag if HTTPS is active
    'httponly' => true, // Prevent JavaScript access to session cookie
    'samesite' => 'Strict' // Prevent CSRF attacks
]);
session_start();
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
    <title>Login - School Management System</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <style>
        body { 
            display: flex; 
            flex-direction: column;
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
        }
        .login-container { 
            width: 100%; 
            max-width: 450px; 
            padding: 2rem;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header h1 {
            color: #00d4ff;
            font-size: 2rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .login-header p {
            color: #999;
        }
        /* Password Toggle & Remember Me Styles */
        .password-wrapper {
            position: relative;
            width: 100%;
        }
        .password-wrapper input {
            width: 100%;
            padding-right: 45px; /* Space for the eye icon */
        }
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #00d4ff;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0.7;
            transition: all 0.3s ease;
        }
        .toggle-password:hover {
            opacity: 1;
            transform: translateY(-50%) scale(1.1);
        }
        .remember-me-container {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
            color: #e0e0e0;
            font-size: 0.9rem;
        }
        .remember-me-container input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #00d4ff;
            cursor: pointer;
        }
        .remember-me-container label {
            cursor: pointer;
            user-select: none;
        }
        @media (max-width: 480px) {
            .login-container {
                padding: 1rem;
            }
            .login-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
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
            
            <button class="submit-btn" type="submit">Sign In</button>
        </form>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                // Switch to Eye Off Icon
                eyeIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07-2.3 2.3"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
            } else {
                passwordInput.type = 'password';
                // Switch back to Eye Icon
                eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
            }
        }
    </script>
</body>

</html>