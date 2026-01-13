<?php
/**
 * Mobile API - Test Execution & Submission
 * Endpoints untuk menyimpan jawaban dan submit tes
 */

require_once __DIR__ . '/mobile_config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Get peserta_id langsung atau dari Firebase UID
$peserta_id = isset($_GET['peserta_id']) ? (int)$_GET['peserta_id'] : null;

// Jika tidak ada peserta_id, coba dari Firebase UID
if (!$peserta_id) {
    $firebase_uid = $_GET['firebase_uid'] ?? '';
    if (!$firebase_uid) {
        // Try to get from Authorization header (Bearer token style)
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $firebase_uid = str_replace('Bearer ', '', $headers['Authorization']);
        }
    }

    if (!$firebase_uid) {
        sendError('Parameter peserta_id atau firebase_uid harus diisi', 'VALIDATION_ERROR', 400);
    }

    // Konversi Firebase UID ke peserta_id
    $peserta_id = getPesertaIdFromFirebaseUID($firebase_uid);
    if (!$peserta_id) {
        sendError('Firebase UID tidak terdaftar', 'NOT_FOUND', 404);
    }
}

// Validasi peserta_id ada di database
$checkQuery = "SELECT id_peserta FROM peserta WHERE id_peserta = ? LIMIT 1";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param('i', $peserta_id);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    sendError('Peserta tidak ditemukan', 'NOT_FOUND', 404);
}

// ==================== SAVE ANSWER ====================
if ($action === 'save' && $method === 'POST') {
    saveAnswer($peserta_id);
}

// ==================== SAVE MULTIPLE ANSWERS ====================
elseif ($action === 'save-batch' && $method === 'POST') {
    saveAnswerBatch($peserta_id);
}

// ==================== SUBMIT TEST ====================
elseif ($action === 'submit' && $method === 'POST') {
    submitTest($peserta_id);
}

// ==================== DEFAULT ====================
else {
    sendError('Endpoint tidak ditemukan', 'NOT_FOUND', 404);
}

/**
 * Save Single Answer
 * POST /api/mobile_jawaban.php?action=save
 */
function saveAnswer($peserta_id) {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    validateRequired($input, ['id_peserta_tes', 'id_soal_tes', 'jawaban']);
    
    $id_peserta_tes = (int)$input['id_peserta_tes'];
    $id_soal_tes = (int)$input['id_soal_tes'];
    $jawaban = sanitizeInput($input['jawaban']);
    
    // Verify peserta_tes belongs to peserta
    $verifyQuery = "
        SELECT id_peserta_tes FROM peserta_tes
        WHERE id_peserta_tes = ? AND id_peserta = ?
        AND status_tes = 'sedang_tes'
        LIMIT 1
    ";
    
    $stmt = $conn->prepare($verifyQuery);
    $stmt->bind_param('ii', $id_peserta_tes, $peserta_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        sendError('Tes tidak valid', 'NOT_FOUND', 404);
    }
    
    // Check if answer already exists
    $checkQuery = "
        SELECT id_jawaban FROM jawaban_peserta
        WHERE id_peserta_tes = ? AND id_soal_tes = ?
        LIMIT 1
    ";
    
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param('ii', $id_peserta_tes, $id_soal_tes);
    $stmt->execute();
    $checkResult = $stmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        // Update existing answer
        $updateQuery = "
            UPDATE jawaban_peserta
            SET jawaban = ?, waktu_submit = NOW()
            WHERE id_peserta_tes = ? AND id_soal_tes = ?
        ";
        
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param('sii', $jawaban, $id_peserta_tes, $id_soal_tes);
        
        if ($stmt->execute()) {
            sendSuccess('Jawaban tersimpan', [
                'id_jawaban' => $checkResult->fetch_assoc()['id_jawaban'],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } else {
            sendError('Gagal menyimpan jawaban', 'DB_ERROR', 500);
        }
    } else {
        // Insert new answer
        $insertQuery = "
            INSERT INTO jawaban_peserta 
            (id_peserta_tes, id_soal_tes, jawaban, waktu_submit)
            VALUES (?, ?, ?, NOW())
        ";
        
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param('iis', $id_peserta_tes, $id_soal_tes, $jawaban);
        
        if ($stmt->execute()) {
            sendSuccess('Jawaban tersimpan', [
                'id_jawaban' => $conn->insert_id,
                'timestamp' => date('Y-m-d H:i:s')
            ], 201);
        } else {
            sendError('Gagal menyimpan jawaban', 'DB_ERROR', 500);
        }
    }
}

/**
 * Save Batch Answers
 * POST /api/mobile_jawaban.php?action=save-batch
 */
function saveAnswerBatch($peserta_id) {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    validateRequired($input, ['id_peserta_tes', 'jawaban']);
    
    $id_peserta_tes = (int)$input['id_peserta_tes'];
    $jawaban = $input['jawaban']; // Array of answers
    
    // Verify peserta_tes
    $verifyQuery = "
        SELECT id_peserta_tes FROM peserta_tes
        WHERE id_peserta_tes = ? AND id_peserta = ?
        AND status_tes = 'sedang_tes'
        LIMIT 1
    ";
    
    $stmt = $conn->prepare($verifyQuery);
    $stmt->bind_param('ii', $id_peserta_tes, $peserta_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        sendError('Tes tidak valid', 'NOT_FOUND', 404);
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        foreach ($jawaban as $answer) {
            $id_soal_tes = (int)$answer['id_soal_tes'];
            $jawabanText = sanitizeInput($answer['jawaban']);
            
            // Check if answer exists
            $checkQuery = "
                SELECT id_jawaban FROM jawaban_peserta
                WHERE id_peserta_tes = ? AND id_soal_tes = ?
            ";
            
            $stmt = $conn->prepare($checkQuery);
            $stmt->bind_param('ii', $id_peserta_tes, $id_soal_tes);
            $stmt->execute();
            $checkResult = $stmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                // Update
                $updateQuery = "
                    UPDATE jawaban_peserta
                    SET jawaban = ?, waktu_submit = NOW()
                    WHERE id_peserta_tes = ? AND id_soal_tes = ?
                ";
                
                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param('sii', $jawabanText, $id_peserta_tes, $id_soal_tes);
                $stmt->execute();
            } else {
                // Insert
                $insertQuery = "
                    INSERT INTO jawaban_peserta 
                    (id_peserta_tes, id_soal_tes, jawaban, waktu_submit)
                    VALUES (?, ?, ?, NOW())
                ";
                
                $stmt = $conn->prepare($insertQuery);
                $stmt->bind_param('iis', $id_peserta_tes, $id_soal_tes, $jawabanText);
                $stmt->execute();
            }
        }
        
        $conn->commit();
        sendSuccess('Jawaban berhasil disimpan');
    } catch (Exception $e) {
        $conn->rollback();
        sendError('Gagal menyimpan jawaban batch', 'DB_ERROR', 500);
    }
}

/**
 * Submit Test
 * POST /api/mobile_jawaban.php?action=submit
 */
function submitTest($peserta_id) {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    validateRequired($input, ['id_peserta_tes']);
    
    $id_peserta_tes = (int)$input['id_peserta_tes'];
    
    // Verify peserta_tes
    $verifyQuery = "
        SELECT pt.id_peserta_tes, pt.id_jadwal, jt.durasi,
               pt.waktu_mulai
        FROM peserta_tes pt
        JOIN jadwal_tes jt ON pt.id_jadwal = jt.id_jadwal
        WHERE pt.id_peserta_tes = ? AND pt.id_peserta = ?
        AND pt.status_tes = 'sedang_tes'
        LIMIT 1
    ";
    
    $stmt = $conn->prepare($verifyQuery);
    $stmt->bind_param('ii', $id_peserta_tes, $peserta_id);
    $stmt->execute();
    $verifyResult = $stmt->get_result();
    
    if ($verifyResult->num_rows === 0) {
        sendError('Tes tidak valid', 'NOT_FOUND', 404);
    }
    
    $testData = $verifyResult->fetch_assoc();
    $id_jadwal = (int)$testData['id_jadwal'];
    
    // Update status test to completed
    $updateQuery = "
        UPDATE peserta_tes
        SET status_tes = 'selesai', waktu_selesai = NOW()
        WHERE id_peserta_tes = ?
    ";
    
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param('i', $id_peserta_tes);
    
    if (!$stmt->execute()) {
        sendError('Gagal submit tes', 'DB_ERROR', 500);
    }
    
    // Calculate score
    $scoreQuery = "
        SELECT 
            COUNT(DISTINCT jp.id_soal_tes) as total_jawab,
            SUM(CASE WHEN jp.jawaban = bs.jawaban_benar THEN 1 ELSE 0 END) as benar,
            COUNT(DISTINCT st.id_soal_tes) as total_soal
        FROM soal_tes st
        LEFT JOIN jawaban_peserta jp ON st.id_soal_tes = jp.id_soal_tes 
            AND jp.id_peserta_tes = ?
        LEFT JOIN bank_soal bs ON st.id_soal = bs.id_soal
        WHERE st.id_jadwal = ?
    ";
    
    $stmt = $conn->prepare($scoreQuery);
    $stmt->bind_param('ii', $id_peserta_tes, $id_jadwal);
    $stmt->execute();
    $scoreResult = $stmt->get_result()->fetch_assoc();
    
    $totalSoal = (int)$scoreResult['total_soal'];
    $totalBenar = (int)($scoreResult['benar'] ?? 0);
    $totalJawab = (int)$scoreResult['total_jawab'];
    $totalKosong = $totalSoal - $totalJawab;
    $nilai = $totalSoal > 0 ? ($totalBenar / $totalSoal) * 100 : 0;
    
    // Get passing grade
    $passingQuery = "SELECT passing_grade FROM jadwal_tes WHERE id_jadwal = ?";
    $stmt = $conn->prepare($passingQuery);
    $stmt->bind_param('i', $id_jadwal);
    $stmt->execute();
    $passingGrade = (float)$stmt->get_result()->fetch_assoc()['passing_grade'];
    
    // Determine status
    $statusKelulusan = $nilai >= $passingGrade ? 'lulus' : 'tidak_lulus';
    
    // Update nilai and status
    $updateNilaiQuery = "
        UPDATE peserta_tes
        SET nilai = ?, status_kelulusan = 'belum_dinilai'
        WHERE id_peserta_tes = ?
    ";
    
    $stmt = $conn->prepare($updateNilaiQuery);
    $stmt->bind_param('di', $nilai, $id_peserta_tes);
    $stmt->execute();
    
    $waktuPengerjaan = isset($testData['waktu_mulai']) 
        ? (int)((time() - strtotime($testData['waktu_mulai'])) / 60)
        : 0;
    
    $response = [
        'id_peserta_tes' => $id_peserta_tes,
        'waktu_selesai' => date('Y-m-d H:i:s'),
        'waktu_pengerjaan' => $waktuPengerjaan,
        'total_soal' => $totalSoal,
        'soal_terjawab' => $totalJawab,
        'soal_kosong' => $totalKosong,
        'nilai_sementara' => round($nilai, 2),
        'status' => 'selesai'
    ];
    
    sendSuccess('Tes berhasil disubmit', $response);
}

/**
 * Verify Token Helper
 */
function verifyTokenAndGetPesertaId($token) {
    try {
        $decoded = base64_decode($token);
        $parts = explode('|', $decoded);
        
        if (count($parts) !== 3) {
            return null;
        }
        
        $peserta_id = (int)$parts[0];
        $timestamp = (int)$parts[1];
        $hash = $parts[2];
        
        $now = time();
        if ($now - $timestamp > 86400) {
            return null;
        }
        
        $secret = $_ENV['APP_SECRET'] ?? 'your-secret-key';
        $expectedHash = hash('sha256', $peserta_id . $timestamp . $secret);
        
        if ($hash !== $expectedHash) {
            return null;
        }
        
        return $peserta_id;
    } catch (Exception $e) {
        return null;
    }
}
?>
