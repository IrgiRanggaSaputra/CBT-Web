<?php
/**
 * Protective redirect untuk akses peserta/login.php yang salah
 * Login page hanya ada di root, bukan di folder peserta
 */
require_once '../config.php';

// Jika sudah login, langsung redirect ke dashboard
if (isset($_SESSION['admin_id'])) {
    redirect('admin/dashboard.php');
}
if (isset($_SESSION['peserta_id'])) {
    redirect('dashboard.php');
}

// Jika belum login, redirect ke halaman login yang benar
redirect('../login.php');
?>
