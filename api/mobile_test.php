<?php
/**
 * Mobile API - Test Management Endpoints
 * Endpoints untuk jadwal tes, instruksi, dan mulai tes
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

// ==================== GET TEST LIST ====================
if ($action === 'list' && $method === 'GET') {
    getTestList($peserta_id);
}

// ==================== GET TEST DETAIL ====================
elseif ($action === 'detail' && $method === 'GET') {
    getTestDetail($peserta_id);
}

// ==================== START TEST ====================
elseif ($action === 'start' && $method === 'POST') {
    startTest($peserta_id);
}

// ==================== GET ALL QUESTIONS ====================
elseif ($action === 'questions' && $method === 'GET') {
    getAllQuestions($peserta_id);
}

// ==================== DEFAULT ====================
else {
    sendError('Endpoint tidak ditemukan', 'NOT_FOUND', 404);
}

/**
 * Get Test List
 * GET /api/mobile_test.php?action=list&status=&search=&sort=date
 */
function getTestList($peserta_id) {
    global $conn;
    
    $status = $_GET['status'] ?? '';
    $search = $_GET['search'] ?? '';
    $sort = $_GET['sort'] ?? 'date';
    
    $query = "
        SELECT 
            jt.id_jadwal, jt.nama_tes, ks.nama_kategori as kategori,
            jt.tanggal_mulai, jt.tanggal_selesai, jt.durasi, jt.jumlah_soal,
            jt.passing_grade, jt.status, pt.id_peserta_tes,
            pt.status_tes, pt.nilai, pt.status_kelulusan
        FROM jadwal_tes jt
        LEFT JOIN kategori_soal ks ON jt.id_kategori = ks.id_kategori
        LEFT JOIN peserta_tes pt ON jt.id_jadwal = pt.id_jadwal AND pt.id_peserta = ?
        WHERE jt.status = 'aktif'
    ";
    
    $params = [$peserta_id];
    
    // Apply search filter
    if (!empty($search)) {
        $query .= " AND jt.nama_tes LIKE ?";
        $params[] = '%' . $search . '%';
    }
    
    // Apply status filter
    if (!empty($status)) {
        if ($status === 'belum_mulai') {
            $query .= " AND (pt.status_tes IS NULL OR pt.status_tes = 'belum_mulai')";
        } else if ($status === 'sedang_tes') {
            $query .= " AND pt.status_tes = 'sedang_tes'";
        } else if ($status === 'selesai') {
            $query .= " AND pt.status_tes = 'selesai'";
        }
    }
    
    // Apply sort
    if ($sort === 'date') {
        $query .= " ORDER BY jt.tanggal_mulai ASC";
    } else if ($sort === 'newest') {
        $query .= " ORDER BY jt.created_at DESC";
    }
    
    $stmt = $conn->prepare($query);
    if (count($params) > 1) {
        $types = 'i' . str_repeat('s', count($params) - 1);
        $stmt->bind_param($types, ...$params);
    } else {
        $stmt->bind_param('i', $peserta_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $tests = [];
    while ($row = $result->fetch_assoc()) {
        $tests[] = [
            'id_jadwal' => (int)$row['id_jadwal'],
            'nama_tes' => $row['nama_tes'],
            'kategori' => $row['kategori'] ?? 'Umum',
            'tanggal_mulai' => $row['tanggal_mulai'],
            'tanggal_selesai' => $row['tanggal_selesai'],
            'durasi' => (int)$row['durasi'],
            'jumlah_soal' => (int)$row['jumlah_soal'],
            'passing_grade' => (float)$row['passing_grade'],
            'status' => $row['status_tes'] ?? 'belum_mulai',
            'nilai' => $row['nilai'] ? (float)$row['nilai'] : null,
            'status_kelulusan' => $row['status_kelulusan']
        ];
    }
    
    sendSuccess('Daftar tes berhasil diambil', $tests);
}

/**
 * Get Test Detail
 * GET /api/mobile_test.php?action=detail&id_jadwal=1
 */
function getTestDetail($peserta_id) {
    global $conn;
    
    if (!isset($_GET['id_jadwal'])) {
        sendError('Parameter id_jadwal harus diisi', 'VALIDATION_ERROR', 400);
    }
    
    $id_jadwal = (int)$_GET['id_jadwal'];
    
    $query = "
        SELECT 
            jt.id_jadwal, jt.nama_tes, ks.nama_kategori as kategori,
            jt.tanggal_mulai, jt.tanggal_selesai, jt.durasi, jt.jumlah_soal,
            jt.passing_grade, jt.instruksi, pt.status_tes
        FROM jadwal_tes jt
        LEFT JOIN kategori_soal ks ON jt.id_kategori = ks.id_kategori
        LEFT JOIN peserta_tes pt ON jt.id_jadwal = pt.id_jadwal AND pt.id_peserta = ?
        WHERE jt.id_jadwal = ? AND jt.status = 'aktif'
        LIMIT 1
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $peserta_id, $id_jadwal);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendError('Tes tidak ditemukan', 'NOT_FOUND', 404);
    }
    
    $test = $result->fetch_assoc();
    
    $response = [
        'id_jadwal' => (int)$test['id_jadwal'],
        'nama_tes' => $test['nama_tes'],
        'kategori' => $test['kategori'] ?? 'Umum',
        'tanggal_mulai' => $test['tanggal_mulai'],
        'tanggal_selesai' => $test['tanggal_selesai'],
        'durasi' => (int)$test['durasi'],
        'jumlah_soal' => (int)$test['jumlah_soal'],
        'passing_grade' => (float)$test['passing_grade'],
        'instruksi' => $test['instruksi'] ?? 'Baca soal dengan teliti dan jawab dengan benar'
    ];
    
    sendSuccess('Detail tes berhasil diambil', $response);
}

/**
 * Start Test
 * POST /api/mobile_test.php?action=start
 */
function startTest($peserta_id) {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    validateRequired($input, ['id_jadwal']);
    
    $id_jadwal = (int)$input['id_jadwal'];
    
    // Check if test exists and is active
    $testQuery = "
        SELECT id_jadwal, nama_tes, durasi, jumlah_soal, id_kategori
        FROM jadwal_tes
        WHERE id_jadwal = ? AND status = 'aktif'
        AND tanggal_mulai <= NOW() AND tanggal_selesai >= NOW()
        LIMIT 1
    ";
    
    $stmt = $conn->prepare($testQuery);
    $stmt->bind_param('i', $id_jadwal);
    $stmt->execute();
    $testResult = $stmt->get_result();
    
    if ($testResult->num_rows === 0) {
        sendError('Tes tidak tersedia', 'NOT_FOUND', 404);
    }
    
    $test = $testResult->fetch_assoc();
    
    // Check if peserta already started this test
    $checkQuery = "
        SELECT id_peserta_tes, status_tes
        FROM peserta_tes
        WHERE id_jadwal = ? AND id_peserta = ?
        LIMIT 1
    ";
    
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param('ii', $id_jadwal, $peserta_id);
    $stmt->execute();
    $checkResult = $stmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $pesertaTes = $checkResult->fetch_assoc();
        $peserta_tes_id = $pesertaTes['id_peserta_tes'];
        
        if ($pesertaTes['status_tes'] === 'selesai') {
            sendError('Anda sudah menyelesaikan tes ini', 'ALREADY_COMPLETED', 409);
        }
    } else {
        // Create new peserta_tes record
        $insertQuery = "
            INSERT INTO peserta_tes 
            (id_jadwal, id_peserta, token, status_tes, waktu_mulai)
            VALUES (?, ?, ?, 'sedang_tes', NOW())
        ";
        
        $token = bin2hex(random_bytes(16));
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param('iis', $id_jadwal, $peserta_id, $token);
        
        if (!$stmt->execute()) {
            sendError('Gagal memulai tes', 'DB_ERROR', 500);
        }
        
        $peserta_tes_id = $conn->insert_id;
    }
    
    // Check if soal_tes already has questions for this jadwal
    $checkSoalQuery = "SELECT COUNT(*) as total FROM soal_tes WHERE id_jadwal = ?";
    $stmt = $conn->prepare($checkSoalQuery);
    $stmt->bind_param('i', $id_jadwal);
    $stmt->execute();
    $soalCount = $stmt->get_result()->fetch_assoc()['total'];
    
    // Auto-populate soal_tes if empty
    if ($soalCount == 0) {
        $id_kategori = $test['id_kategori'];
        $jumlah_soal = (int)$test['jumlah_soal'];
        
        // Get random questions from bank_soal based on kategori
        $randomSoalQuery = "
            SELECT id_soal FROM bank_soal 
            WHERE id_kategori = ? 
            ORDER BY RAND() 
            LIMIT ?
        ";
        $stmt = $conn->prepare($randomSoalQuery);
        $stmt->bind_param('ii', $id_kategori, $jumlah_soal);
        $stmt->execute();
        $randomResult = $stmt->get_result();
        
        $nomor_urut = 1;
        while ($soal = $randomResult->fetch_assoc()) {
            $insertSoalQuery = "INSERT INTO soal_tes (id_jadwal, id_soal, nomor_urut) VALUES (?, ?, ?)";
            $stmtInsert = $conn->prepare($insertSoalQuery);
            $stmtInsert->bind_param('iii', $id_jadwal, $soal['id_soal'], $nomor_urut);
            $stmtInsert->execute();
            $nomor_urut++;
        }
    }
    
    // Get questions for this test
    $questionsQuery = "
        SELECT 
            st.id_soal_tes, st.nomor_urut, bs.id_soal, bs.pertanyaan,
            bs.pilihan_a, bs.pilihan_b, bs.pilihan_c, bs.pilihan_d, bs.pilihan_e,
            bs.gambar, bs.bobot
        FROM soal_tes st
        JOIN bank_soal bs ON st.id_soal = bs.id_soal
        WHERE st.id_jadwal = ?
        ORDER BY st.nomor_urut ASC
    ";
    
    $stmt = $conn->prepare($questionsQuery);
    $stmt->bind_param('i', $id_jadwal);
    $stmt->execute();
    $questionsResult = $stmt->get_result();
    
    $questions = [];
    while ($row = $questionsResult->fetch_assoc()) {
        $questions[] = [
            'id_soal_tes' => (int)$row['id_soal_tes'],
            'nomor_urut' => (int)$row['nomor_urut'],
            'id_soal' => (int)$row['id_soal'],
            'pertanyaan' => $row['pertanyaan'],
            'pilihan_a' => $row['pilihan_a'],
            'pilihan_b' => $row['pilihan_b'],
            'pilihan_c' => $row['pilihan_c'],
            'pilihan_d' => $row['pilihan_d'],
            'pilihan_e' => $row['pilihan_e'],
            'gambar' => $row['gambar'],
            'bobot' => (int)$row['bobot']
        ];
    }
    
    $response = [
        'id_peserta_tes' => $peserta_tes_id,
        'waktu_mulai' => date('Y-m-d H:i:s'),
        'durasi' => (int)$test['durasi'],
        'soal' => $questions
    ];
    
    sendSuccess('Tes dimulai', $response, 201);
}

/**
 * Get All Questions
 * GET /api/mobile_test.php?action=questions&id_peserta_tes=1
 */
function getAllQuestions($peserta_id) {
    global $conn;
    
    try {
        if (!isset($_GET['id_peserta_tes'])) {
            sendError('Parameter id_peserta_tes harus diisi', 'VALIDATION_ERROR', 400);
            return;
        }
        
        $id_peserta_tes = (int)$_GET['id_peserta_tes'];
        
        // Verify that this test belongs to the peserta
        $verifyQuery = "
            SELECT pt.id_peserta_tes, pt.id_jadwal
            FROM peserta_tes pt
            WHERE pt.id_peserta_tes = ? AND pt.id_peserta = ?
            LIMIT 1
        ";
        
        $stmt = $conn->prepare($verifyQuery);
        if (!$stmt) {
            sendError('Database error: ' . $conn->error, 'DATABASE_ERROR', 500);
            return;
        }
        $stmt->bind_param('ii', $id_peserta_tes, $peserta_id);
        $stmt->execute();
        $verifyResult = $stmt->get_result();
        
        if ($verifyResult->num_rows === 0) {
            sendError('Tes tidak ditemukan atau bukan milik peserta ini', 'NOT_FOUND', 404);
            return;
        }
        
        $verifyData = $verifyResult->fetch_assoc();
        $id_jadwal = $verifyData['id_jadwal'];
    
    // Get questions
    $questionsQuery = "
        SELECT 
            st.id_soal_tes, st.nomor_urut, bs.id_soal, bs.pertanyaan,
            bs.pilihan_a, bs.pilihan_b, bs.pilihan_c, bs.pilihan_d, bs.pilihan_e,
            bs.gambar, bs.bobot
        FROM soal_tes st
        JOIN bank_soal bs ON st.id_soal = bs.id_soal
        WHERE st.id_jadwal = ?
        ORDER BY st.nomor_urut ASC
    ";
    
    $stmt = $conn->prepare($questionsQuery);
    $stmt->bind_param('i', $id_jadwal);
    $stmt->execute();
    $questionsResult = $stmt->get_result();
    
    $questions = [];
    while ($row = $questionsResult->fetch_assoc()) {
        $questions[] = [
            'id_soal_tes' => (int)$row['id_soal_tes'],
            'nomor_urut' => (int)$row['nomor_urut'],
            'id_soal' => (int)$row['id_soal'],
            'pertanyaan' => $row['pertanyaan'],
            'pilihan_a' => $row['pilihan_a'],
            'pilihan_b' => $row['pilihan_b'],
            'pilihan_c' => $row['pilihan_c'],
            'pilihan_d' => $row['pilihan_d'],
            'pilihan_e' => $row['pilihan_e'],
            'gambar' => $row['gambar'],
            'bobot' => (int)$row['bobot']
        ];
    }
    
    // Get saved answers
    $answersQuery = "
        SELECT jp.id_soal_tes, jp.jawaban
        FROM jawaban_peserta jp
        WHERE jp.id_peserta_tes = ?
    ";
    
    $stmt = $conn->prepare($answersQuery);
    $stmt->bind_param('i', $id_peserta_tes);
    $stmt->execute();
    $answersResult = $stmt->get_result();
    
    $answers = [];
    while ($row = $answersResult->fetch_assoc()) {
        $answers[(int)$row['id_soal_tes']] = $row['jawaban'];
    }
    
    $response = [
        'id_peserta_tes' => $id_peserta_tes,
        'total_soal' => count($questions),
        'soal' => $questions,
        'jawaban_tersimpan' => $answers
    ];
    
    sendSuccess('Soal berhasil diambil', $response);
    
    } catch (Exception $e) {
        sendError('Server error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
    }
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
        
        // Check if token expired (24 hours)
        $now = time();
        if ($now - $timestamp > 86400) {
            return null;
        }
        
        // Verify hash
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
