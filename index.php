<?php
/**
 * Index/Landing Page
 * Redirects users based on authentication status
 */

require_once 'includes/config.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
} else {
    redirect('login.php');
}
?>