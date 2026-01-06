<?php
require_once '../config.php';
check_login_peserta();
$_SESSION['peserta_id'] = null;
$_SESSION['peserta_nama'] = null;
$_SESSION['peserta_nomor'] = null;
session_destroy();
redirect('login.php');
?>
