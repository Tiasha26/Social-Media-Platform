<?php
/**
 * Account Settings Page
 * Allows users to update their account information and change password
 * BONUS FEATURE
 */

require_once 'includes/config.php';
requireLogin();

$pdo = getDBConnection();
$currentUserId = getCurrentUserId();
$errors = [];
$success = '';

// Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$currentUserId]);
$user = $stmt->fetch();

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Update Email
    if (isset($_POST['update_email'])) {
        $newEmail = trim($_POST['new_email'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        
        if (empty($newEmail)) {
            $errors[] = "Email is required";
        } elseif (!isValidEmail($newEmail)) {
            $errors[] = "Invalid email format";
        } elseif (empty($currentPassword)) {
            $errors[] = "Current password is required";
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $errors[] = "Current password is incorrect";
        } else {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$newEmail, $currentUserId]);
            
            if ($stmt->rowCount() > 0) {
                $errors[] = "Email is already in use";
            } else {
                $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
                if ($stmt->execute([$newEmail, $currentUserId])) {
                    $success = "Email updated successfully!";
                    $user['email'] = $newEmail;
                } else {
                    $errors[] = "Failed to update email";
                }
            }
        }
    }
    
    // Change Password
    if (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password_pass'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPassword)) {
            $errors[] = "Current password is required";
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $errors[] = "Current password is incorrect";
        } elseif (empty($newPassword)) {
            $errors[] = "New password is required";
        } elseif (strlen($newPassword) < 6) {
            $errors[] = "New password must be at least 6 characters";
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = "New passwords do not match";
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            
            if ($stmt->execute([$hashedPassword, $currentUserId])) {
                $success = "Password changed successfully!";
            } else {
                $errors[] = "Failed to change password";
            }
        }
    }
    
    // Delete Account
    if (isset($_POST['delete_account'])) {
        $confirmPassword = $_POST['confirm_delete_password'] ?? '';
        $confirmText = $_POST['confirm_delete_text'] ?? '';
        
        if (empty($confirmPassword)) {
            $errors[] = "Password is required to delete account";
        } elseif (!password_verify($confirmPassword, $user['password'])) {
            $errors[] = "Password is incorrect";
        } elseif (strtoupper(trim($confirmText)) !== 'DELETE') {
            $errors[] = "Please type DELETE to confirm";
        } else {
            // Delete user account (cascade will delete posts and messages)
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$currentUserId])) {
                session_destroy();
                redirect('register.php?deleted=1');
            } else {
                $errors[] = "Failed to delete account";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - Social Media Platform</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container">
        <div class="settings-container" style="max-width: 800px; margin: 40px auto;">
            <h1 style="text-align: center; color: var(--primary-color); margin-bottom: 30px;">
                ⚙️ Account Settings
            </h1>
            
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo sanitizeOutput($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message"><?php echo sanitizeOutput($success); ?></div>
            <?php endif; ?>
            
            <!-- Account Information -->
            <div class="settings-section" style="background: white; padding: 30px; border-radius: 12px; box-shadow: var(--shadow); margin-bottom: 20px;">
                <h2 style="color: var(--primary-color); margin-bottom: 20px;">📧 Email Address</h2>
                <p style="color: var(--text-light); margin-bottom: 15px;">
                    Current email: <strong><?php echo sanitizeOutput($user['email']); ?></strong>
                </p>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="new_email">New Email Address</label>
                        <input type="email" name="new_email" id="new_email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="current_password">Current Password (for verification)</label>
                        <input type="password" name="current_password" id="current_password" class="form-control" required>
                    </div>
                    
                    <button type="submit" name="update_email" class="btn btn-primary">Update Email</button>
                </form>
            </div>
            
            <!-- Change Password -->
            <div class="settings-section" style="background: white; padding: 30px; border-radius: 12px; box-shadow: var(--shadow); margin-bottom: 20px;">
                <h2 style="color: var(--primary-color); margin-bottom: 20px;">🔒 Change Password</h2>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="current_password_pass">Current Password</label>
                        <input type="password" name="current_password_pass" id="current_password_pass" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" name="new_password" id="new_password" class="form-control" required>
                        <small style="color: var(--text-light);">Minimum 6 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                </form>
            </div>
            
            <!-- Delete Account -->
            <div class="settings-section" style="background: #fee; padding: 30px; border-radius: 12px; box-shadow: var(--shadow); border: 2px solid var(--error-color);">
                <h2 style="color: var(--error-color); margin-bottom: 20px;">⚠️ Danger Zone</h2>
                <p style="color: var(--text-dark); margin-bottom: 20px;">
                    <strong>Warning:</strong> Deleting your account is permanent and cannot be undone. 
                    All your posts, messages, and data will be permanently deleted.
                </p>
                
                <button onclick="document.getElementById('deleteModal').style.display='block'" 
                        class="btn" style="background: var(--error-color); color: white;">
                    Delete My Account
                </button>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" style="display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6);">
        <div style="background-color: white; margin: 10% auto; padding: 40px; border-radius: 12px; width: 90%; max-width: 500px; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
            <span onclick="document.getElementById('deleteModal').style.display='none'" 
                  style="float: right; font-size: 28px; font-weight: bold; cursor: pointer; color: var(--text-light);">&times;</span>
            
            <h2 style="margin-bottom: 20px; color: var(--error-color);">⚠️ Delete Account</h2>
            <p style="margin-bottom: 20px; color: var(--text-dark);">
                This action cannot be undone. Please type <strong>DELETE</strong> to confirm.
            </p>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="confirm_delete_text">Type DELETE to confirm</label>
                    <input type="text" name="confirm_delete_text" id="confirm_delete_text" 
                           class="form-control" required placeholder="DELETE">
                </div>
                
                <div class="form-group">
                    <label for="confirm_delete_password">Enter your password</label>
                    <input type="password" name="confirm_delete_password" id="confirm_delete_password" 
                           class="form-control" required>
                </div>
                
                <button type="submit" name="delete_account" class="btn" 
                        style="width: 100%; background: var(--error-color); color: white;">
                    Permanently Delete My Account
                </button>
            </form>
        </div>
    </div>
    
    <script src="js/validation.js"></script>
</body>
</html>