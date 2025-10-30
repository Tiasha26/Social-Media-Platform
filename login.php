<?php
/**
 * User Login Page
 * Handles user authentication
 */

require_once 'includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password";
    } else {
        $pdo = getDBConnection();
        
        // Check if login is by email or username
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        
        if ($stmt->rowCount() === 1) {
            $user = $stmt->fetch();
            
            if (password_verify($password, $user['password'])) {
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['profile_picture'] = $user['profile_picture'];
                
                redirect('dashboard.php');
            } else {
                $error = "Wrong username/password combination";
            }
        } else {
            $error = "Wrong username/password combination";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Social Media Platform</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">Login</div>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo sanitizeOutput($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" id="loginForm">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-control" 
                           value="<?php echo sanitizeOutput($username); ?>" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
                            <div style="text-align: right; margin-bottom: 15px;">
                    <a href="forgot_password.php" style="color: var(--secondary-color); font-size: 14px; text-decoration: none;">
                        Forgot Password?
                    </a>
                </div>
            <div class="auth-footer">
                Not yet a member? <a href="register.php">Sign up</a>
            </div>
        </div>
    </div>
    
    <script src="js/validation.js"></script>
</body>
</html>