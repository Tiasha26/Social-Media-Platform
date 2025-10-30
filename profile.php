<?php
/**
 * User Profile Page
 * Displays user information and their posts
 */

require_once 'includes/config.php';
requireLogin();

$pdo = getDBConnection();
$currentUserId = getCurrentUserId();

// Get username from URL
$username = $_GET['username'] ?? '';

if (empty($username)) {
    redirect('dashboard.php');
}

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);

if ($stmt->rowCount() === 0) {
    die("User not found");
}

$user = $stmt->fetch();
$isOwnProfile = ($user['id'] == $currentUserId);

// Handle profile update
$updateSuccess = '';
$updateErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isOwnProfile) {
    
    $newProfilePic = $user['profile_picture'];
    if (!empty($_FILES['profile_picture']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024;
        
        if (!in_array($_FILES['profile_picture']['type'], $allowedTypes)) {
            $updateErrors[] = "Only JPG, PNG and GIF images are allowed";
        } elseif ($_FILES['profile_picture']['size'] > $maxSize) {
            $updateErrors[] = "Image size must be less than 5MB";
        } else {
            $extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $newProfilePic = uniqid('profile_') . '.' . $extension;
            $uploadPath = UPLOAD_PATH . $newProfilePic;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadPath)) {
                // Delete old profile picture if not default
                if ($user['profile_picture'] !== 'default_avatar.png') {
                    @unlink(UPLOAD_PATH . $user['profile_picture']);
                }
            } else {
                $updateErrors[] = "Failed to upload profile picture";
                $newProfilePic = $user['profile_picture'];
            }
        }
    }
    
    if (empty($updateErrors)) {
        $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
        if ($stmt->execute([$newProfilePic, $currentUserId])) {
            $_SESSION['profile_picture'] = $newProfilePic;
            $updateSuccess = "Profile updated successfully!";
            redirect('profile.php?username=' . urlencode($username));
        } else {
            $updateErrors[] = "Failed to update profile";
        }
    }
}

// Fetch user's posts
$stmt = $pdo->prepare("
    SELECT p.*, u.username, u.profile_picture 
    FROM posts p 
    JOIN users u ON p.user_id = u.id 
    WHERE u.id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$user['id']]);
$userPosts = $stmt->fetchAll();

// Get user statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as post_count FROM posts WHERE user_id = ?");
$stmt->execute([$user['id']]);
$stats = $stmt->fetch();
$postCount = $stats['post_count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as message_count FROM messages WHERE sender_id = ?");
$stmt->execute([$user['id']]);
$stats = $stmt->fetch();
$messageCount = $stats['message_count'];

$memberSince = date('F Y', strtotime($user['created_at']));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitizeOutput($user['username']); ?> - Profile</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container">
        <div class="profile-container">
            <div class="profile-header">
                <img src="<?php echo UPLOAD_URL . sanitizeOutput($user['profile_picture']); ?>" 
                     alt="<?php echo sanitizeOutput($user['username']); ?>" 
                     class="profile-picture">
                     <div class="post-author">
                <div class="post-author-name">
                <h1 class="profile-name"><?php echo sanitizeOutput($user['username']); ?></h1>
                <p class="profile-email">Email: <?php echo sanitizeOutput($user['email']); ?></p>
                </div>
                </div>
                <!-- User Statistics -->
                <div style="display: flex; justify-content: center; gap: 30px; margin-top: 20px; padding: 20px; background: var(--bg-light); border-radius: 8px;">
                    <div style="text-align: center;">
                        <div style="font-size: 24px; font-weight: bold; color: var(--primary-color);">
                            <?php echo $postCount; ?>
                        </div>
                        <div style="font-size: 14px; color: var(--text-light);">Posts</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 24px; font-weight: bold; color: var(--primary-color);">
                            <?php echo $messageCount; ?>
                        </div>
                        <div style="font-size: 14px; color: var(--text-light);">Messages Sent</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 16px; font-weight: bold; color: var(--primary-color);">
                            <?php echo $memberSince; ?>
                        </div>
                        <div style="font-size: 14px; color: var(--text-light);">Member Since</div>
                    </div>
                </div>
                
                <?php if ($isOwnProfile): ?>
                    <button onclick="document.getElementById('editModal').style.display='block'" 
                            class="btn btn-secondary" style="margin-top: 20px;">
                        Edit Profile
                    </button>
                <?php endif; ?>
            </div>
            
            <h2 style="margin: 30px 0 20px; color: var(--primary-color);">
                <?php echo $isOwnProfile ? 'Your' : sanitizeOutput($user['username']) . "'s"; ?> Posts
            </h2>
            
            <?php if (empty($userPosts)): ?>
                <div class="post-card">
                    <p style="text-align: center; color: var(--text-light);">
                        No posts yet.
                    </p>
                </div>
            <?php else: ?>
                <?php foreach ($userPosts as $post): ?>
                    <div class="post-card">
                        <div class="post-header">
                            <img src="<?php echo UPLOAD_URL . sanitizeOutput($post['profile_picture']); ?>" 
                                 alt="<?php echo sanitizeOutput($post['username']); ?>" 
                                 class="post-avatar">
                            <div class="post-author">
                                <div class="post-author-name">
                                    <?php echo sanitizeOutput($post['username']); ?>
                                </div>
                                
                            </div>
                        </div>
                        
                        <div class="post-content">
                            <?php echo nl2br(sanitizeOutput($post['content'])); ?>
                        </div>
                        
                        <?php if ($post['image']): ?>
                            <img src="<?php echo UPLOAD_URL . sanitizeOutput($post['image']); ?>" 
                                 alt="Post image" class="post-image">
                        <?php endif; ?>
                        <div class="post-time"><?php echo$post['created_at']; ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Edit Profile Modal -->
    <?php if ($isOwnProfile): ?>
    <div id="editModal" style="display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6);">
        <div style="background-color: white; margin: 5% auto; padding: 40px; border-radius: 12px; width: 90%; max-width: 500px; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
            <span onclick="document.getElementById('editModal').style.display='none'" 
                  style="float: right; font-size: 28px; font-weight: bold; cursor: pointer; color: var(--text-light);">&times;</span>
            
            <h2 style="margin-bottom: 20px; color: var(--primary-color);">Edit Profile</h2>
            
            <?php if (!empty($updateErrors)): ?>
                <div class="error-message">
                    <?php foreach ($updateErrors as $error): ?>
                        <div><?php echo sanitizeOutput($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-control" 
                           value="<?php echo sanitizeOutput($user['username']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="profile_picture">Profile Picture</label>
                    <input type="file" name="profile_picture" id="profile_picture" 
                           class="form-control" accept="image/*">
                    <small style="color: var(--text-light);">Leave empty to keep current picture</small>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Update Profile
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>
    <script src="js/navbar.js"></script>
</body>
</html>