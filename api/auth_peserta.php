<?php
require_once __DIR__ . '/config_api.php';
require_once __DIR__ . '/helpers_api.php';

// Ambil Authorization Header
$headers = getallheaders();
$firebase_uid = $headers['Authorization'] ?? '';

if (!$firebase_uid) {
    unauthorized('Firebase UID tidak ditemukan');
}

// Cari peserta berdasarkan firebase_uid
$q = mysqli_query($conn, "
    SELECT id, nama, nomor_peserta 
    FROM peserta 
    WHERE firebase_uid = '$firebase_uid'
");

if (mysqli_num_rows($q) === 0) {
    unauthorized('Akun peserta belum terhubung');
}

$peserta = mysqli_fetch_assoc($q);
$peserta_id = $peserta['id'];
