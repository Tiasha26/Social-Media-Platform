<?php
/**
 * Forgot Password Page
 * Allows users to request a password reset
 */

require_once 'includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = "Please enter your email address";
    } elseif (!isValidEmail($email)) {
        $error = "Please enter a valid email address";
    } else {
        $pdo = getDBConnection();
        
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() === 1) {
            $user = $stmt->fetch();
            
            // Generate a unique token
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Delete any existing tokens for this email
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$email]);
            
            // Insert new token
            $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$email, $token, $expiresAt]);
            
            // In a real application, you would send an email here
            // For this demo, we'll just show the reset link
            $resetLink = SITE_URL . "reset_password.php?token=" . $token;
            
            $success = "Password reset instructions have been sent! <br><br>
                       <strong>Demo Mode:</strong> Since email is not configured, here's your reset link:<br>
                       <a href='$resetLink' style='color: #0074D9; word-break: break-all;'>$resetLink</a><br><br>
                       <small>This link will expire in 1 hour.</small>";
        } else {
            // Don't reveal if email exists for security
            $success = "If an account exists with that email, password reset instructions have been sent.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Social Media Platform</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">Forgot Password</div>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo sanitizeOutput($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php else: ?>
                <p style="text-align: center; color: var(--text-light); margin-bottom: 20px;">
                    Enter your email address and we'll send you instructions to reset your password.
                </p>
                
                <form method="POST" action="" id="forgotPasswordForm">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control" 
                               value="<?php echo sanitizeOutput($email); ?>" required autofocus>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Send Reset Link</button>
                </form>
            <?php endif; ?>
            
            <div class="auth-footer">
                Remember your password? <a href="login.php">Sign in</a>
            </div>
        </div>
    </div>
    
    <script src="js/validation.js"></script>
</body>
</html>