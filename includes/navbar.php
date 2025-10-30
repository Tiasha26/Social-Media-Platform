<?php
/**
 * Navigation Bar Component
 * Displays the main navigation menu for logged-in users
 */

if (!isLoggedIn()) {
    redirect('login.php');
}

$currentUser = $_SESSION['username'];
$profilePic = $_SESSION['profile_picture'];
?>

<nav class="navbar">
    <div class="navbar-content">
        <button class="menu-toggle" id="menu-toggle" aria-label="Toggle navigation">
            <img src="uploads/log-in.png" alt="Menu" style="width:24px; height:24px;">
        </button>
        <a href="dashboard.php" class="navbar-brand">SocialConnect</a>
        
        <ul class="navbar-menu" id="navbar-menu">
            <li><a href="dashboard.php">Home</a></li>
            <li><a href="search.php">Search Users</a></li>
            <li><a href="messages.php">Messages</a></li>
            <li>
                <a href="profile.php?username=<?php echo urlencode($currentUser); ?>">
                    <img src="<?php echo UPLOAD_URL . sanitizeOutput($profilePic); ?>" 
                         alt="Profile" class="user-avatar">
                </a>
            </li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

