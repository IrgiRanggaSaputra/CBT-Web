<?php
// Admin Logout Handler
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    // Not logged in, redirect to login
    redirect('login.php');
}

// No-cache headers to prevent caching of logout page
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

// Delete session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// Clear all session data
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page
redirect('login.php');
?>
?>
