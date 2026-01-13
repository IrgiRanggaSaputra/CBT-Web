<?php
/**
 * Mobile API - Test Results Endpoints
 * Endpoints untuk melihat hasil dan riwayat tes
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

// ==================== GET TEST RESULT ====================
if ($action === 'get' && $method === 'GET') {
    getTestResult($peserta_id);
}

// ==================== GET RESULT WITH DETAILS ====================
elseif ($action === 'detail' && $method === 'GET') {
    getTestResultDetail($peserta_id);
}

// ==================== GET TEST HISTORY ====================
elseif ($action === 'history' && $method === 'GET') {
    getTestHistory($peserta_id);
}

// ==================== DEFAULT ====================
else {
    sendError('Endpoint tidak ditemukan', 'NOT_FOUND', 404);
}

/**
 * Get Test Result
 * GET /api/mobile_hasil.php?action=get&id_peserta_tes=1
 */
function getTestResult($peserta_id) {
    global $conn;
    
    if (!isset($_GET['id_peserta_tes'])) {
        sendError('Parameter id_peserta_tes harus diisi', 'VALIDATION_ERROR', 400);
    }
    
    $id_peserta_tes = (int)$_GET['id_peserta_tes'];
    
    $query = "
        SELECT 
            pt.id_peserta_tes, jt.nama_tes,
            pt.waktu_mulai, pt.waktu_selesai, pt.nilai,
            jt.passing_grade, pt.status_kelulusan, jt.jumlah_soal
        FROM peserta_tes pt
        JOIN jadwal_tes jt ON pt.id_jadwal = jt.id_jadwal
        WHERE pt.id_peserta_tes = ? AND pt.id_peserta = ?
        AND pt.status_tes = 'selesai'
        LIMIT 1
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $id_peserta_tes, $peserta_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendError('Hasil tes tidak ditemukan', 'NOT_FOUND', 404);
    }
    
    $hasil = $result->fetch_assoc();
    
    // Calculate stats
    $statsQuery = "
        SELECT 
            COUNT(DISTINCT jp.id_soal_tes) as total_jawab,
            SUM(CASE WHEN jp.jawaban = bs.jawaban_benar THEN 1 ELSE 0 END) as benar
        FROM soal_tes st
        LEFT JOIN jawaban_peserta jp ON st.id_soal_tes = jp.id_soal_tes 
            AND jp.id_peserta_tes = ?
        LEFT JOIN bank_soal bs ON st.id_soal = bs.id_soal
        WHERE st.id_jadwal = (SELECT id_jadwal FROM peserta_tes WHERE id_peserta_tes = ?)
    ";
    
    $stmt = $conn->prepare($statsQuery);
    $stmt->bind_param('ii', $id_peserta_tes, $id_peserta_tes);
    $stmt->execute();
    $statsResult = $stmt->get_result()->fetch_assoc();
    
    $totalSoal = (int)$hasil['jumlah_soal'];
    $benar = (int)($statsResult['benar'] ?? 0);
    $jawab = (int)$statsResult['total_jawab'];
    $salah = $jawab - $benar;
    $kosong = $totalSoal - $jawab;
    
    $waktuPengerjaan = $hasil['waktu_selesai'] && $hasil['waktu_mulai']
        ? (int)((strtotime($hasil['waktu_selesai']) - strtotime($hasil['waktu_mulai'])) / 60)
        : 0;
    
    $response = [
        'id_peserta_tes' => (int)$hasil['id_peserta_tes'],
        'nama_tes' => $hasil['nama_tes'],
        'tanggal_mulai' => $hasil['waktu_mulai'],
        'tanggal_selesai' => $hasil['waktu_selesai'],
        'waktu_pengerjaan' => $waktuPengerjaan,
        'total_soal' => $totalSoal,
        'soal_benar' => $benar,
        'soal_salah' => $salah,
        'soal_kosong' => $kosong,
        'nilai' => (float)$hasil['nilai'],
        'passing_grade' => (float)$hasil['passing_grade'],
        'status_kelulusan' => $hasil['status_kelulusan'] ?? 'belum_dinilai'
    ];
    
    sendSuccess('Hasil tes berhasil diambil', $response);
}

/**
 * Get Test Result Detail (with answers)
 * GET /api/mobile_hasil.php?action=detail&id_peserta_tes=1
 */
function getTestResultDetail($peserta_id) {
    global $conn;
    
    if (!isset($_GET['id_peserta_tes'])) {
        sendError('Parameter id_peserta_tes harus diisi', 'VALIDATION_ERROR', 400);
    }
    
    $id_peserta_tes = (int)$_GET['id_peserta_tes'];
    
    // Get basic result info
    $query = "
        SELECT 
            pt.id_peserta_tes, jt.nama_tes,
            pt.waktu_mulai, pt.waktu_selesai, pt.nilai,
            jt.passing_grade, pt.status_kelulusan, jt.jumlah_soal
        FROM peserta_tes pt
        JOIN jadwal_tes jt ON pt.id_jadwal = jt.id_jadwal
        WHERE pt.id_peserta_tes = ? AND pt.id_peserta = ?
        AND pt.status_tes = 'selesai'
        LIMIT 1
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $id_peserta_tes, $peserta_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendError('Hasil tes tidak ditemukan', 'NOT_FOUND', 404);
    }
    
    $hasil = $result->fetch_assoc();
    
    // Get detailed answers
    $detailQuery = "
        SELECT 
            st.nomor_urut as nomor,
            bs.pertanyaan,
            COALESCE(jp.jawaban, '') as jawaban_peserta,
            bs.jawaban_benar,
            CASE 
                WHEN jp.jawaban IS NULL THEN 'kosong'
                WHEN jp.jawaban = bs.jawaban_benar THEN 'benar'
                ELSE 'salah'
            END as status
        FROM soal_tes st
        JOIN bank_soal bs ON st.id_soal = bs.id_soal
        LEFT JOIN jawaban_peserta jp ON st.id_soal_tes = jp.id_soal_tes 
            AND jp.id_peserta_tes = ?
        WHERE st.id_jadwal = (SELECT id_jadwal FROM peserta_tes WHERE id_peserta_tes = ?)
        ORDER BY st.nomor_urut ASC
    ";
    
    $stmt = $conn->prepare($detailQuery);
    $stmt->bind_param('ii', $id_peserta_tes, $id_peserta_tes);
    $stmt->execute();
    $detailResult = $stmt->get_result();
    
    $details = [];
    $benar = 0;
    $salah = 0;
    $kosong = 0;
    
    while ($row = $detailResult->fetch_assoc()) {
        $details[] = [
            'nomor' => (int)$row['nomor'],
            'pertanyaan' => $row['pertanyaan'],
            'jawaban_peserta' => $row['jawaban_peserta'] ?: null,
            'jawaban_benar' => $row['jawaban_benar'],
            'status' => $row['status']
        ];
        
        if ($row['status'] === 'benar') $benar++;
        elseif ($row['status'] === 'salah') $salah++;
        elseif ($row['status'] === 'kosong') $kosong++;
    }
    
    $waktuPengerjaan = $hasil['waktu_selesai'] && $hasil['waktu_mulai']
        ? (int)((strtotime($hasil['waktu_selesai']) - strtotime($hasil['waktu_mulai'])) / 60)
        : 0;
    
    $response = [
        'id_peserta_tes' => (int)$hasil['id_peserta_tes'],
        'nama_tes' => $hasil['nama_tes'],
        'tanggal_mulai' => $hasil['waktu_mulai'],
        'tanggal_selesai' => $hasil['waktu_selesai'],
        'waktu_pengerjaan' => $waktuPengerjaan,
        'total_soal' => (int)$hasil['jumlah_soal'],
        'soal_benar' => $benar,
        'soal_salah' => $salah,
        'soal_kosong' => $kosong,
        'nilai' => (float)$hasil['nilai'],
        'passing_grade' => (float)$hasil['passing_grade'],
        'status_kelulusan' => $hasil['status_kelulusan'] ?? 'belum_dinilai',
        'detail' => $details
    ];
    
    sendSuccess('Detail hasil tes berhasil diambil', $response);
}

/**
 * Get Test History
 * GET /api/mobile_hasil.php?action=history&search=&filter=&sort=
 */
function getTestHistory($peserta_id) {
    global $conn;
    
    $search = $_GET['search'] ?? '';
    $filter = $_GET['filter'] ?? 'all';
    $sort = $_GET['sort'] ?? 'date';
    
    $query = "
        SELECT 
            pt.id_peserta_tes, jt.nama_tes,
            pt.waktu_mulai, pt.waktu_selesai, pt.nilai,
            pt.status_kelulusan, pt.status_tes
        FROM peserta_tes pt
        JOIN jadwal_tes jt ON pt.id_jadwal = jt.id_jadwal
        WHERE pt.id_peserta = ? AND pt.status_tes = 'selesai'
    ";
    
    $params = [$peserta_id];
    
    // Apply search filter
    if (!empty($search)) {
        $query .= " AND jt.nama_tes LIKE ?";
        $params[] = '%' . $search . '%';
    }
    
    // Apply status filter
    if ($filter === 'lulus') {
        $query .= " AND pt.status_kelulusan = 'lulus'";
    } elseif ($filter === 'gagal') {
        $query .= " AND pt.status_kelulusan = 'tidak_lulus'";
    } elseif ($filter === 'belum_dinilai') {
        $query .= " AND pt.status_kelulusan = 'belum_dinilai'";
    }
    
    // Apply sort
    if ($sort === 'newest') {
        $query .= " ORDER BY pt.waktu_selesai DESC";
    } else {
        $query .= " ORDER BY pt.waktu_selesai DESC";
    }
    
    $stmt = $conn->prepare($query);
    
    $types = 'i' . str_repeat('s', count($params) - 1);
    if (count($params) > 1) {
        $stmt->bind_param($types, ...$params);
    } else {
        $stmt->bind_param('i', $peserta_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = [
            'id_peserta_tes' => (int)$row['id_peserta_tes'],
            'nama_tes' => $row['nama_tes'],
            'tanggal_mulai' => $row['waktu_mulai'],
            'tanggal_selesai' => $row['waktu_selesai'],
            'nilai' => (float)($row['nilai'] ?? 0),
            'status_kelulusan' => $row['status_kelulusan'] ?? 'belum_dinilai',
            'status' => $row['status_tes']
        ];
    }
    
    sendSuccess('Riwayat tes berhasil diambil', $history);
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
