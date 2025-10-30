<?php
/**
 * Database Configuration File
 * This file contains database connection settings and helper functions
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'social_media_platform');

// Site configuration
define('SITE_URL', 'http://localhost/social_media_platform');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', '/Social Media Platform/uploads/'); // Ensure this matches your actual uploads directory

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

/**
 * Create database connection using PDO
 * @return PDO Database connection object
 */
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database Connection Error: " . $e->getMessage());
        die("Database connection failed. Please try again later.");
    }
}

/**
 * Sanitize output to prevent XSS attacks
 * @param string $data The data to sanitize
 * @return string Sanitized data
 */
function sanitizeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 * @param string $email Email to validate
 * @return bool True if valid, false otherwise
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Redirect to a specific page
 * @param string $page Page to redirect to
 */
function redirect($page) {
    header("Location: " . $page);
    exit();
}

/**
 * Check if user is logged in
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Require user to be logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

/**
 * Get current user ID
 * @return int|null User ID or null if not logged in
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Format time ago
 * @param string $datetime DateTime string
 * @return string Formatted time ago string
 */
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if ($difference < 60) {
        return 'just now';
    } elseif ($difference < 3600) {
        $mins = floor($difference / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 86400) {
        $hours = floor($difference / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 604800) {
        $days = floor($difference / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M d, Y', $timestamp);
    }
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>