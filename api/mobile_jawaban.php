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
    
    // Convert id_soal_tes to id_soal (the actual soal ID from bank_soal) and get jawaban_benar
    $soalQuery = "SELECT st.id_soal, bs.jawaban_benar 
                  FROM soal_tes st 
                  JOIN bank_soal bs ON st.id_soal = bs.id_soal 
                  WHERE st.id_soal_tes = ? LIMIT 1";
    $stmt = $conn->prepare($soalQuery);
    $stmt->bind_param('i', $id_soal_tes);
    $stmt->execute();
    $soalResult = $stmt->get_result();
    
    if ($soalResult->num_rows === 0) {
        sendError('Soal tidak ditemukan', 'NOT_FOUND', 404);
    }
    
    $soalData = $soalResult->fetch_assoc();
    $id_soal = (int)$soalData['id_soal'];
    $jawaban_benar = $soalData['jawaban_benar'];
    
    // Hitung is_correct berdasarkan perbandingan jawaban
    $is_correct = (strtoupper($jawaban) === strtoupper($jawaban_benar)) ? 1 : 0;
    
    // Verify peserta_tes belongs to peserta and check status
    $verifyQuery = "
        SELECT id_peserta_tes, status_tes FROM peserta_tes
        WHERE id_peserta_tes = ? AND id_peserta = ?
        LIMIT 1
    ";
    
    $stmt = $conn->prepare($verifyQuery);
    $stmt->bind_param('ii', $id_peserta_tes, $peserta_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendError('Data tes tidak ditemukan', 'NOT_FOUND', 404);
    }
    
    $tesData = $result->fetch_assoc();
    
    // Allow saving answers only during test
    if ($tesData['status_tes'] !== 'sedang_tes') {
        sendError('Tidak dapat menyimpan jawaban - tes sudah selesai', 'TEST_COMPLETED', 400);
    }
    
    // Check if answer already exists (use id_soal, not id_soal_tes)
    $checkQuery = "
        SELECT id_jawaban FROM jawaban_peserta
        WHERE id_peserta_tes = ? AND id_soal = ?
        LIMIT 1
    ";
    
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param('ii', $id_peserta_tes, $id_soal);
    $stmt->execute();
    $checkResult = $stmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        // Update existing answer
        $updateQuery = "
            UPDATE jawaban_peserta
            SET jawaban = ?, is_correct = ?, waktu_jawab = NOW()
            WHERE id_peserta_tes = ? AND id_soal = ?
        ";
        
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param('siii', $jawaban, $is_correct, $id_peserta_tes, $id_soal);
        
        if ($stmt->execute()) {
            sendSuccess('Jawaban tersimpan', [
                'id_jawaban' => $checkResult->fetch_assoc()['id_jawaban'],
                'is_correct' => $is_correct,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } else {
            sendError('Gagal menyimpan jawaban: ' . $conn->error, 'DB_ERROR', 500);
        }
    } else {
        // Insert new answer (use id_soal, not id_soal_tes)
        $insertQuery = "
            INSERT INTO jawaban_peserta 
            (id_peserta_tes, id_soal, jawaban, is_correct, waktu_jawab)
            VALUES (?, ?, ?, ?, NOW())
        ";
        
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param('iisi', $id_peserta_tes, $id_soal, $jawaban, $is_correct);
        
        if ($stmt->execute()) {
            sendSuccess('Jawaban tersimpan', [
                'id_jawaban' => $conn->insert_id,
                'is_correct' => $is_correct,
                'timestamp' => date('Y-m-d H:i:s')
            ], 201);
        } else {
            sendError('Gagal menyimpan jawaban: ' . $conn->error, 'DB_ERROR', 500);
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
    
    // Verify peserta_tes and check status
    $verifyQuery = "
        SELECT id_peserta_tes, status_tes FROM peserta_tes
        WHERE id_peserta_tes = ? AND id_peserta = ?
        LIMIT 1
    ";
    
    $stmt = $conn->prepare($verifyQuery);
    $stmt->bind_param('ii', $id_peserta_tes, $peserta_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendError('Data tes tidak ditemukan', 'NOT_FOUND', 404);
    }
    
    $tesData = $result->fetch_assoc();
    
    if ($tesData['status_tes'] !== 'sedang_tes') {
        sendError('Tidak dapat menyimpan jawaban - tes sudah selesai', 'TEST_COMPLETED', 400);
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        foreach ($jawaban as $answer) {
            $id_soal_tes = (int)$answer['id_soal_tes'];
            $jawabanText = sanitizeInput($answer['jawaban']);
            
            // Convert id_soal_tes to id_soal and get jawaban_benar
            $soalQuery = "SELECT st.id_soal, bs.jawaban_benar 
                          FROM soal_tes st 
                          JOIN bank_soal bs ON st.id_soal = bs.id_soal 
                          WHERE st.id_soal_tes = ? LIMIT 1";
            $stmt = $conn->prepare($soalQuery);
            $stmt->bind_param('i', $id_soal_tes);
            $stmt->execute();
            $soalResult = $stmt->get_result();
            
            if ($soalResult->num_rows === 0) {
                continue; // Skip invalid soal
            }
            
            $soalData = $soalResult->fetch_assoc();
            $id_soal = (int)$soalData['id_soal'];
            $jawaban_benar = $soalData['jawaban_benar'];
            
            // Hitung is_correct berdasarkan perbandingan jawaban
            $is_correct = (strtoupper($jawabanText) === strtoupper($jawaban_benar)) ? 1 : 0;
            
            // Check if answer exists (use id_soal)
            $checkQuery = "
                SELECT id_jawaban FROM jawaban_peserta
                WHERE id_peserta_tes = ? AND id_soal = ?
            ";
            
            $stmt = $conn->prepare($checkQuery);
            $stmt->bind_param('ii', $id_peserta_tes, $id_soal);
            $stmt->execute();
            $checkResult = $stmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                // Update dengan is_correct
                $updateQuery = "
                    UPDATE jawaban_peserta
                    SET jawaban = ?, is_correct = ?, waktu_jawab = NOW()
                    WHERE id_peserta_tes = ? AND id_soal = ?
                ";
                
                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param('siii', $jawabanText, $is_correct, $id_peserta_tes, $id_soal);
                $stmt->execute();
            } else {
                // Insert dengan is_correct
                $insertQuery = "
                    INSERT INTO jawaban_peserta 
                    (id_peserta_tes, id_soal, jawaban, is_correct, waktu_jawab)
                    VALUES (?, ?, ?, ?, NOW())
                ";
                
                $stmt = $conn->prepare($insertQuery);
                $stmt->bind_param('iisi', $id_peserta_tes, $id_soal, $jawabanText, $is_correct);
                $stmt->execute();
            }
        }
        
        $conn->commit();
        sendSuccess('Jawaban berhasil disimpan');
    } catch (Exception $e) {
        $conn->rollback();
        sendError('Gagal menyimpan jawaban batch: ' . $e->getMessage(), 'DB_ERROR', 500);
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
    
    // First check if peserta_tes exists for this peserta
    $checkQuery = "
        SELECT pt.id_peserta_tes, pt.id_jadwal, pt.status_tes,
               pt.waktu_mulai, pt.waktu_selesai, pt.nilai, jt.durasi
        FROM peserta_tes pt
        JOIN jadwal_tes jt ON pt.id_jadwal = jt.id_jadwal
        WHERE pt.id_peserta_tes = ? AND pt.id_peserta = ?
        LIMIT 1
    ";
    
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param('ii', $id_peserta_tes, $peserta_id);
    $stmt->execute();
    $checkResult = $stmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        sendError('Data tes tidak ditemukan untuk peserta ini', 'NOT_FOUND', 404);
    }
    
    $testData = $checkResult->fetch_assoc();
    
    // If test already submitted, return success with existing data
    if ($testData['status_tes'] === 'selesai') {
        // Calculate stats from existing data
        $id_jadwal = (int)$testData['id_jadwal'];
        
        // Use id_soal to join with jawaban_peserta (not id_soal_tes)
        $statsQuery = "
            SELECT 
                COUNT(DISTINCT jp.id_soal) as total_jawab,
                COUNT(DISTINCT st.id_soal_tes) as total_soal
            FROM soal_tes st
            LEFT JOIN jawaban_peserta jp ON st.id_soal = jp.id_soal 
                AND jp.id_peserta_tes = ?
            WHERE st.id_jadwal = ?
        ";
        
        $stmt = $conn->prepare($statsQuery);
        $stmt->bind_param('ii', $id_peserta_tes, $id_jadwal);
        $stmt->execute();
        $statsResult = $stmt->get_result()->fetch_assoc();
        
        $response = [
            'id_peserta_tes' => $id_peserta_tes,
            'waktu_selesai' => $testData['waktu_selesai'],
            'total_soal' => (int)$statsResult['total_soal'],
            'soal_terjawab' => (int)$statsResult['total_jawab'],
            'soal_kosong' => (int)$statsResult['total_soal'] - (int)$statsResult['total_jawab'],
            'nilai_sementara' => round((float)$testData['nilai'], 2),
            'status' => 'sudah_selesai',
            'message' => 'Tes sudah disubmit sebelumnya'
        ];
        
        sendSuccess('Tes sudah disubmit sebelumnya', $response);
        return;
    }
    
    // Verify test is in progress
    if ($testData['status_tes'] !== 'sedang_tes') {
        sendError('Status tes tidak valid: ' . $testData['status_tes'], 'INVALID_STATUS', 400);
    }
    
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
    
    // Calculate score (use id_soal to join with jawaban_peserta)
    $scoreQuery = "
        SELECT 
            COUNT(DISTINCT jp.id_soal) as total_jawab,
            SUM(CASE WHEN jp.jawaban = bs.jawaban_benar THEN 1 ELSE 0 END) as benar,
            COUNT(DISTINCT st.id_soal_tes) as total_soal
        FROM soal_tes st
        LEFT JOIN jawaban_peserta jp ON st.id_soal = jp.id_soal 
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
