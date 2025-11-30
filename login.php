<?php
session_start();
ob_start(); // Start output buffering to prevent header issues
include "config.php";

$email_error = false;
$password_error = false;
$error = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Email validation
    if (empty($email)) {
        $error = "Email is required.";
        $email_error = true;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
        $email_error = true;
    } elseif (empty($password)) {
        $error = "Password is required.";
        $password_error = true;
    } else {
        // Use prepared statement to prevent SQL injection
        $stmt = mysqli_prepare($conn, "SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1");
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) === 1) {
                $user = mysqli_fetch_assoc($result);

                // Check password (supports both hashed and plain text for migration)
                $password_valid = false;
                
                // Check if password is hashed (starts with $2y$ or similar)
                if (password_get_info($user['password'])['algoName'] !== 'unknown') {
                    // Password is hashed, use password_verify
                    $password_valid = password_verify($password, $user['password']);
                } else {
                    // Password is plain text, compare directly
                    $password_valid = ($password === $user['password']);
                    
                    // If login successful with plain text, hash it for future use
                    if ($password_valid) {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $update_stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
                        if ($update_stmt) {
                            mysqli_stmt_bind_param($update_stmt, "si", $hashed_password, $user['id']);
                            mysqli_stmt_execute($update_stmt);
                            mysqli_stmt_close($update_stmt);
                        }
                    }
                }

                if ($password_valid) {
                    // Save session values
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = $user['role'];

                    mysqli_stmt_close($stmt);
                    ob_end_clean(); // Clear output buffer before redirect
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Incorrect password.";
                    $password_error = true;
                }
            } else {
                $error = "Email not found.";
                $email_error = true;
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = "Database error. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Emergency Communication System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <div class="logo-icon">🚨</div>
                <h1>Emergency Communication</h1>
                <p>Sign in to your account</p>
            </div>
            
            <?php if(isset($error)): ?>
                <div class="error-alert">
                    <span class="error-icon">⚠️</span>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="login-form" id="loginForm" novalidate>
                <div class="form-group">
                    <div class="input-wrapper <?php echo $email_error ? 'error' : ''; ?>">
                        <span class="input-icon">📧</span>
                        <input type="email" id="email" name="email" placeholder="Email Address" required autocomplete="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-wrapper <?php echo $password_error ? 'error' : ''; ?>">
                        <span class="input-icon">🔒</span>
                        <input type="password" id="password" name="password" placeholder="Password" required autocomplete="current-password">
                        <button type="button" class="toggle-password" id="togglePassword" aria-label="Show password">
                            <span class="eye-icon">👁️</span>
                        </button>
                    </div>
                </div>
                
                <button type="submit" name="login" class="login-btn">
                    <span>Sign In</span>
                    <span class="btn-arrow">→</span>
                </button>
            </form>
            
            <script>
                // Client-side validation
                document.getElementById('loginForm').addEventListener('submit', function(e) {
                    const email = document.getElementById('email');
                    const password = document.getElementById('password');
                    const emailWrapper = email.closest('.input-wrapper');
                    const passwordWrapper = password.closest('.input-wrapper');
                    let isValid = true;
                    
                    // Reset error states
                    emailWrapper.classList.remove('error');
                    passwordWrapper.classList.remove('error');
                    
                    // Email validation
                    if (!email.value.trim()) {
                        emailWrapper.classList.add('error');
                        isValid = false;
                    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
                        emailWrapper.classList.add('error');
                        isValid = false;
                    }
                    
                    // Password validation
                    if (!password.value) {
                        passwordWrapper.classList.add('error');
                        isValid = false;
                    }
                    
                    if (!isValid) {
                        e.preventDefault();
                    }
                });
                
                // Real-time validation on blur
                document.getElementById('email').addEventListener('blur', function() {
                    const email = this.value.trim();
                    const wrapper = this.closest('.input-wrapper');
                    
                    if (!email) {
                        wrapper.classList.add('error');
                    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                        wrapper.classList.add('error');
                    } else {
                        wrapper.classList.remove('error');
                    }
                });
                
                document.getElementById('password').addEventListener('blur', function() {
                    const wrapper = this.closest('.input-wrapper');
                    if (!this.value) {
                        wrapper.classList.add('error');
                    } else {
                        wrapper.classList.remove('error');
                    }
                });
                
                // Remove error on input
                document.getElementById('email').addEventListener('input', function() {
                    const wrapper = this.closest('.input-wrapper');
                    if (this.value.trim() && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value)) {
                        wrapper.classList.remove('error');
                    }
                });
                
                document.getElementById('password').addEventListener('input', function() {
                    const wrapper = this.closest('.input-wrapper');
                    if (this.value) {
                        wrapper.classList.remove('error');
                    }
                });
                
                // Password visibility toggle
                const togglePassword = document.getElementById('togglePassword');
                const passwordInput = document.getElementById('password');
                const eyeIcon = togglePassword.querySelector('.eye-icon');
                
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    // Change icon
                    if (type === 'text') {
                        eyeIcon.textContent = '🙈';
                        togglePassword.setAttribute('aria-label', 'Hide password');
                    } else {
                        eyeIcon.textContent = '👁️';
                        togglePassword.setAttribute('aria-label', 'Show password');
                    }
                });
            </script>
        </div>
    </div>
</body>
</html>
