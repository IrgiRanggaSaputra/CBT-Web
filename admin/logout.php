<?php
require_once '../config.php';
check_login_admin();
$_SESSION['admin_id'] = null;
$_SESSION['admin_nama'] = null;
$_SESSION['admin_username'] = null;
session_destroy();
redirect('login.php');
?>
