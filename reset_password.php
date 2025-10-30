<?php
/**
 * Reset Password Page
 * Allows users to set a new password using a valid token
 */

require_once 'includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';
$token = $_GET['token'] ?? '';
$validToken = false;
$email = '';

// Validate token
if (!empty($token)) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT email, expires_at FROM password_resets WHERE token = ?");
    $stmt->execute([$token]);
    
    if ($stmt->rowCount() === 1) {
        $reset = $stmt->fetch();
        $email = $reset['email'];
        
        // Check if token has expired
        if (strtotime($reset['expires_at']) > time()) {
            $validToken = true;
        } else {
            $error = "This password reset link has expired. Please request a new one.";
        }
    } else {
        $error = "Invalid password reset link.";
    }
} else {
    $error = "No reset token provided.";
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($password)) {
        $error = "Password is required";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match";
    } else {
        $pdo = getDBConnection();
        
        // Update user password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        
        if ($stmt->execute([$hashedPassword, $email])) {
            // Delete the used token
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
            $stmt->execute([$token]);
            
            $success = "Your password has been successfully reset! You can now log in with your new password.";
            header("refresh:3;url=login.php");
        } else {
            $error = "Failed to reset password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Social Media Platform</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">Reset Password</div>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo sanitizeOutput($error); ?></div>
                <div class="auth-footer">
                    <a href="forgot_password.php">Request a new reset link</a> or 
                    <a href="login.php">Back to login</a>
                </div>
            <?php elseif ($success): ?>
                <div class="success-message"><?php echo sanitizeOutput($success); ?></div>
                <div class="auth-footer">
                    <a href="login.php">Go to login page</a>
                </div>
            <?php elseif ($validToken): ?>
                <p style="text-align: center; color: var(--text-light); margin-bottom: 20px;">
                    Enter your new password below.
                </p>
                
                <form method="POST" action="" id="resetPasswordForm">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" name="password" id="password" 
                               class="form-control" required autofocus>
                        <small style="color: var(--text-light);">Minimum 6 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" 
                               class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </form>
                
                <div class="auth-footer">
                    <a href="login.php">Back to login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="js/validation.js"></script>
</body>
</html>