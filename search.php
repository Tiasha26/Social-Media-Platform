<?php
/**
 * Search Users Page
 * Allows users to search for other users by name or email
 */

require_once 'includes/config.php';
requireLogin();

$pdo = getDBConnection();
$searchResults = [];
$searchQuery = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['q'])) {
    $searchQuery = trim($_POST['search'] ?? $_GET['q'] ?? '');
    
    if (!empty($searchQuery)) {
        $stmt = $pdo->prepare("
            SELECT id, username, email, profile_picture 
            FROM users 
            WHERE (username LIKE ? OR email LIKE ?)
            AND id != ?
            LIMIT 20
        ");
        
        $searchTerm = '%' . $searchQuery . '%';
        $stmt->execute([$searchTerm, $searchTerm, getCurrentUserId()]);
        $searchResults = $stmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Users - Social Media Platform</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container">
        <div class="search-container">
            <h1 class="search-header">Search Users:</h1>
            
            <form method="POST" action="" id="searchForm">
                <div class="search-box">
                    <input type="text" name="search" id="searchInput" 
                           class="search-input" 
                           placeholder="Search by name or email" 
                           value="<?php echo sanitizeOutput($searchQuery); ?>"
                           required>
                </div>
                <button type="submit" class="btn btn-primary search-btn">Search</button>
            </form>
            
            <?php if (!empty($searchQuery)): ?>
                <div style="margin-top: 30px;">
                    <?php if (empty($searchResults)): ?>
                        <p style="text-align: center; color: var(--text-light); padding: 20px;">
                            No users found matching "<?php echo sanitizeOutput($searchQuery); ?>"
                        </p>
                    <?php else: ?>
                        <?php foreach ($searchResults as $user): ?>
                            <a href="profile.php?username=<?php echo urlencode($user['username']); ?>" 
                               class="user-result">
                                <img src="<?php echo UPLOAD_URL . sanitizeOutput($user['profile_picture']); ?>" 
                                     class="user-result-avatar">
                                <div class="user-result-info">
                                    <p><?php echo sanitizeOutput($user['email']); ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="js/search.js"></script>
    <script src="js/navbar.js"></script>
</body>
</html>