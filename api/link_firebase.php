<?php
/**
 * Mobile API - Link Firebase UID to Peserta
 * Endpoint untuk menghubungkan akun Firebase dengan peserta
 */

require_once __DIR__ . '/mobile_config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    sendError('Method tidak diizinkan', 'METHOD_NOT_ALLOWED', 405);
}

$data = json_decode(file_get_contents("php://input"), true);

$firebase_uid = $data['firebase_uid'] ?? '';
$nomor_peserta = $data['nomor_peserta'] ?? '';

if (!$firebase_uid || !$nomor_peserta) {
    sendError('Data tidak lengkap. Harap isi firebase_uid dan nomor_peserta', 'VALIDATION_ERROR', 400);
}

// Sanitize input
$firebase_uid = sanitizeInput($firebase_uid);
$nomor_peserta = sanitizeInput($nomor_peserta);

// Cek peserta dengan prepared statement (untuk keamanan SQL Injection)
$query = "SELECT id_peserta, firebase_uid, nama_lengkap FROM peserta WHERE nomor_peserta = ? LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $nomor_peserta);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    sendError('Peserta tidak ditemukan', 'NOT_FOUND', 404);
}

$peserta = $result->fetch_assoc();

// Cegah overwrite UID jika sudah terhubung dengan akun lain
if (!empty($peserta['firebase_uid']) && $peserta['firebase_uid'] !== $firebase_uid) {
    sendError('Akun peserta sudah terhubung dengan Firebase lain', 'ALREADY_LINKED', 403);
}

// Cek apakah Firebase UID sudah digunakan oleh peserta lain
$checkUidQuery = "SELECT id_peserta FROM peserta WHERE firebase_uid = ? AND nomor_peserta != ? LIMIT 1";
$stmt = $conn->prepare($checkUidQuery);
$stmt->bind_param('ss', $firebase_uid, $nomor_peserta);
$stmt->execute();
$checkResult = $stmt->get_result();

if ($checkResult->num_rows > 0) {
    sendError('Firebase UID sudah digunakan oleh peserta lain', 'UID_IN_USE', 409);
}

// Simpan UID dengan prepared statement
$updateQuery = "UPDATE peserta SET firebase_uid = ? WHERE nomor_peserta = ?";
$stmt = $conn->prepare($updateQuery);
$stmt->bind_param('ss', $firebase_uid, $nomor_peserta);

if ($stmt->execute()) {
    sendSuccess('Firebase UID berhasil terhubung', [
        'id_peserta' => (int)$peserta['id_peserta'],
        'nama_lengkap' => $peserta['nama_lengkap'],
        'nomor_peserta' => $nomor_peserta,
        'firebase_uid' => $firebase_uid
    ]);
} else {
    sendError('Gagal menyimpan Firebase UID', 'DB_ERROR', 500);
}
