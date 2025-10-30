<?php
/**
 * Logout Script
 * Destroys user session and redirects to login
 */

require_once 'includes/config.php';

// Unset all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to login page
redirect('login.php');
?>