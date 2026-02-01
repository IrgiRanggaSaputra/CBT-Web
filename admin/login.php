<?php
/**
 * Protective redirect untuk akses admin/login.php yang salah
 * Login page hanya ada di root, bukan di folder admin
 */
require_once '../config.php';

// Jika sudah login, langsung redirect ke dashboard
if (isset($_SESSION['admin_id'])) {
    redirect('dashboard.php');
}
if (isset($_SESSION['peserta_id'])) {
    redirect('peserta/dashboard.php');
}

// Jika belum login, redirect ke halaman login yang benar dengan parameter role
redirect('../login.php?role=admin');
?>
