<?php
/**
 * User Dashboard
 * Main feed showing user posts and post creation
 */

require_once 'includes/config.php';
requireLogin();

$pdo = getDBConnection();
$errors = [];
$success = '';

// Handle post deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post'])) {
    $postId = intval($_POST['post_id'] ?? 0);
    
    // Verify post belongs to current user
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
    $stmt->execute([$postId, $userId]);
    
    if ($stmt->rowCount() === 1) {
        $post = $stmt->fetch();
        
        // Delete post image if exists
        if ($post['image'] && file_exists(UPLOAD_PATH . $post['image'])) {
            unlink(UPLOAD_PATH . $post['image']);
        }
        
        // Delete post
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        if ($stmt->execute([$postId])) {
            $success = "Post deleted successfully!";
        } else {
            $errors[] = "Failed to delete post";
        }
    } else {
        $errors[] = "You can only delete your own posts";
    }
}


// Handle post creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_post'])) {
    $content = trim($_POST['content'] ?? '');
    $userId = getCurrentUserId();
    
    if (empty($content)) {
        $errors[] = "Post content cannot be empty";
    }
    
    $imagePath = null;
    if (!empty($_FILES['post_image']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['post_image']['type'], $allowedTypes)) {
            $errors[] = "Only JPG, PNG and GIF images are allowed";
        } elseif ($_FILES['post_image']['size'] > $maxSize) {
            $errors[] = "Image size must be less than 5MB";
        } else {
            $extension = pathinfo($_FILES['post_image']['name'], PATHINFO_EXTENSION);
            $imagePath = uniqid('post_') . '.' . $extension;
            $uploadPath = UPLOAD_PATH . $imagePath;
            
            if (!move_uploaded_file($_FILES['post_image']['tmp_name'], $uploadPath)) {
                $errors[] = "Failed to upload image";
                $imagePath = null;
            }
        }
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)");
        if ($stmt->execute([$userId, $content, $imagePath])) {
            $success = "Post created successfully!";
            redirect('dashboard.php');
        } else {
            $errors[] = "Failed to create post";
        }
    }
}

// Fetch all posts with user information
$stmt = $pdo->prepare("
    SELECT p.*, u.username, u.profile_picture 
    FROM posts p 
    JOIN users u ON p.user_id = u.id 
    ORDER BY p.created_at DESC
");
$stmt->execute();
$posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Social Media Platform</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container">
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo sanitizeOutput($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Post Creator -->
        <div class="post-creator">
            <form method="POST" action="" enctype="multipart/form-data" id="postForm">
                <textarea name="content" id="postContent" placeholder="Share Something ..." required></textarea>
                
                <div id="imagePreview" class="image-preview"></div>
                
                <div class="post-actions">
                    <div class="file-input-wrapper">
                        <input type="file" name="post_image" id="postImage" accept="image/*">
                        <label for="postImage" class="file-input-label">
                            📷 Add Photo
                        </label>
                    </div>
                    <button type="submit" name="create_post" class="btn btn-primary">POST</button>
                </div>
            </form>
        </div>
        
        <!-- Posts Feed -->
        <div id="postsFeed">
            <?php if (empty($posts)): ?>
                <div class="post-card">
                    <p style="text-align: center; color: var(--text-light);">
                        No posts yet. Be the first to share something!
                    </p>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post-card">
                        <div class="post-header">
                            <a href="profile.php?username=<?php echo urlencode($post['username']); ?>">
                                <img src="<?php echo UPLOAD_URL . sanitizeOutput($post['profile_picture']); ?>" 
                                     class="post-avatar">
                            </a>
                            <div class="post-author-name">
                                <a href="profile.php?username=<?php echo urlencode($post['username']); ?>" 
                                   class="post-author-name"><?php echo sanitizeOutput($post['username']); ?>
                                </a>
                                
                            </div>
                        </div>
                        
                        <div class="post-content">
                            <?php echo nl2br(sanitizeOutput($post['content'])); ?>
                        </div>
                        
                        <?php if ($post['image']): ?>
                            <img src="<?php echo UPLOAD_URL . sanitizeOutput($post['image']); ?>" 
                                 alt="Post image" class="post-image">
                        <?php endif; ?>
                        <div class="post-time"><?php echo $post['created_at']; ?></div>

                                              <?php if ($post['user_id'] == getCurrentUserId()): ?>
                            <form method="POST" action="" style="margin-top: 15px;" 
                                  onsubmit="return confirm('Are you sure you want to delete this post?');">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <button type="submit" name="delete_post" class="btn" 
                                        style="background: var(--error-color); color: white; font-size: 14px; padding: 8px 15px;">
                                    🗑️ Delete Post
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="js/dashboard.js"></script>
    <script src="js/navbar.js"></script>
</body>
</html>