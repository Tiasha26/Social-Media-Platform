<?php
/**
 * User Registration Page
 * Handles new user registration with validation and security
 */

require_once 'includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
$success = '';
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form data
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Store form data for repopulation
    $formData = [
        'username' => $username,
        'email' => $email,
    ];
    
    // Validation
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = "Username must be between 3 and 50 characters";
    } elseif (!preg_match('/^[a-zA-Z0-9_ ]+$/', $username)) {
        $errors[] = "Username can only contain letters, numbers, underscores and spaces";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!isValidEmail($email)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }
    
    // Check if username or email already exists
    if (empty($errors)) {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            $checkUser = $pdo->prepare("SELECT username FROM users WHERE username = ?");
            $checkUser->execute([$username]);
            if ($checkUser->rowCount() > 0) {
                $errors[] = "Username already exists";
            }
            
            $checkEmail = $pdo->prepare("SELECT email FROM users WHERE email = ?");
            $checkEmail->execute([$email]);
            if ($checkEmail->rowCount() > 0) {
                $errors[] = "Email already exists";
            }
        }
    }
    
    // Handle profile picture upload
    $profilePicture = 'default_avatar.png';
    if (!empty($_FILES['profile_picture']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['profile_picture']['type'], $allowedTypes)) {
            $errors[] = "Only JPG, PNG and GIF images are allowed";
        } elseif ($_FILES['profile_picture']['size'] > $maxSize) {
            $errors[] = "Image size must be less than 5MB";
        } else {
            $extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $profilePicture = uniqid('profile_') . '.' . $extension;
            $uploadPath = UPLOAD_PATH . $profilePicture;
            
            if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadPath)) {
                $errors[] = "Failed to upload profile picture";
                $profilePicture = 'default_avatar.png';
            }
        }
    }
    
    // Insert user if no errors
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, profile_picture) VALUES (?, ?, ?, ?)");
        
        if ($stmt->execute([$username, $email, $hashedPassword, $profilePicture])) {
            $success = "Registration successful! Redirecting to login...";
            header("refresh:2;url=login.php");
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Social Media Platform</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">Register</div>
            
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php if (count($errors) === 1): ?>
                        <?php echo sanitizeOutput($errors[0]); ?>
                    <?php else: ?>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo sanitizeOutput($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message"><?php echo sanitizeOutput($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" enctype="multipart/form-data" id="registerForm">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-control" 
                           value="<?php echo sanitizeOutput($formData['username'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" 
                           value="<?php echo sanitizeOutput($formData['email'] ?? ''); ?>" required>
                </div>
                

                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="profile_picture">Profile Picture (Optional)</label>
                    <input type="file" name="profile_picture" id="profile_picture" 
                           class="form-control" accept="image/*">
                </div>
                
                <button type="submit" class="btn btn-primary">Register</button>
            </form>
            
            <div class="auth-footer">
                Already a member? <a href="login.php">Sign in</a>
            </div>
        </div>
    </div>
    
    <script src="js/validation.js"></script>
</body>
</html>