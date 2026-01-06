<?php
require_once 'config_api.php';
require_once 'helpers.php';

$data = json_decode(file_get_contents("php://input"), true);

$firebase_uid   = $data['firebase_uid'] ?? '';
$nomor_peserta  = $data['nomor_peserta'] ?? '';

if (!$firebase_uid || !$nomor_peserta) {
    json_response(['status' => 'error', 'message' => 'Data tidak lengkap'], 400);
}

// Cek peserta
$q = mysqli_query(
    $conn,
    "SELECT id, firebase_uid FROM peserta WHERE nomor_peserta='$nomor_peserta'"
);

if (mysqli_num_rows($q) == 0) {
    json_response(['status' => 'error', 'message' => 'Peserta tidak ditemukan'], 404);
}

$peserta = mysqli_fetch_assoc($q);

// Cegah overwrite UID
if (!empty($peserta['firebase_uid']) && $peserta['firebase_uid'] !== $firebase_uid) {
    json_response([
        'status' => 'error',
        'message' => 'Akun peserta sudah terhubung dengan Firebase lain'
    ], 403);
}

// Simpan UID
mysqli_query($conn, "
    UPDATE peserta
    SET firebase_uid='$firebase_uid'
    WHERE nomor_peserta='$nomor_peserta'
");

json_response([
    'status' => 'success',
    'message' => 'Firebase UID berhasil terhubung'
]);
