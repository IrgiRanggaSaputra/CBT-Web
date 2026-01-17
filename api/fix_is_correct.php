<?php
/**
 * Script untuk memperbaiki kolom is_correct pada tabel jawaban_peserta
 * yang sebelumnya tidak diisi saat menyimpan jawaban dari mobile app.
 * 
 * Jalankan sekali untuk memperbaiki data lama.
 * Akses: /api/fix_is_correct.php
 */

require_once __DIR__ . '/mobile_config.php';

// Hanya bisa diakses dari localhost atau dengan key tertentu
$allowed = false;
$access_key = $_GET['key'] ?? '';

if ($access_key === 'fix_jawaban_2024') {
    $allowed = true;
} elseif (in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    $allowed = true;
}

if (!$allowed) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak']);
    exit;
}

header('Content-Type: application/json');

try {
    // Update semua jawaban yang is_correct = NULL
    $updateQuery = "
        UPDATE jawaban_peserta jp
        JOIN bank_soal bs ON jp.id_soal = bs.id_soal
        SET jp.is_correct = CASE 
            WHEN UPPER(jp.jawaban) = UPPER(bs.jawaban_benar) THEN 1 
            ELSE 0 
        END
        WHERE jp.is_correct IS NULL
    ";
    
    $result = $conn->query($updateQuery);
    $affected = $conn->affected_rows;
    
    if ($result) {
        // Juga update yang is_correct = 0 tapi seharusnya benar, atau sebaliknya
        $verifyQuery = "
            UPDATE jawaban_peserta jp
            JOIN bank_soal bs ON jp.id_soal = bs.id_soal
            SET jp.is_correct = CASE 
                WHEN UPPER(jp.jawaban) = UPPER(bs.jawaban_benar) THEN 1 
                ELSE 0 
            END
            WHERE (jp.is_correct = 1 AND UPPER(jp.jawaban) != UPPER(bs.jawaban_benar))
               OR (jp.is_correct = 0 AND UPPER(jp.jawaban) = UPPER(bs.jawaban_benar))
        ";
        
        $conn->query($verifyQuery);
        $verifyAffected = $conn->affected_rows;
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil diperbaiki',
            'data' => [
                'null_fixed' => $affected,
                'mismatch_fixed' => $verifyAffected,
                'total_fixed' => $affected + $verifyAffected
            ]
        ]);
    } else {
        throw new Exception('Gagal menjalankan query: ' . $conn->error);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
