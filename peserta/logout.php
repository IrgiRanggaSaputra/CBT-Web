
<?php
require_once '../config.php';
check_login_peserta();

// No-cache headers untuk prevent browser caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

// Delete session cookie properly
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// Unset semua session variables
foreach ($_SESSION as $key => $value) {
    unset($_SESSION[$key]);
}

// Destroy session
session_destroy();

// Redirect ke login - gunakan relative path ke parent directory
redirect('../login.php');
?>
