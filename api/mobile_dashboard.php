<?php
/**
 * Mobile API - Dashboard Endpoints
 * Endpoints untuk dashboard peserta
 */

require_once __DIR__ . '/mobile_config.php';

$method = $_SERVER['REQUEST_METHOD'];

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

if ($method === 'GET') {
    getDashboard($peserta_id);
} else {
    sendError('Method tidak diizinkan', 'METHOD_NOT_ALLOWED', 405);
}

/**
 * Get Dashboard Data
 * GET /api/mobile_dashboard.php
 */
function getDashboard($peserta_id) {
    global $conn;
    
    // Get peserta data
    $pesertaQuery = "
        SELECT id_peserta, nomor_peserta, nama_lengkap, email
        FROM peserta
        WHERE id_peserta = ?
        LIMIT 1
    ";
    $stmt = $conn->prepare($pesertaQuery);
    $stmt->bind_param('i', $peserta_id);
    $stmt->execute();
    $pesertaResult = $stmt->get_result()->fetch_assoc();
    
    // Get statistics
    $statsQuery = "
        SELECT 
            COUNT(*) as total_tes,
            SUM(CASE WHEN status_tes = 'selesai' THEN 1 ELSE 0 END) as tes_selesai,
            SUM(CASE WHEN status_kelulusan = 'lulus' THEN 1 ELSE 0 END) as tes_lulus,
            SUM(CASE WHEN status_kelulusan = 'tidak_lulus' THEN 1 ELSE 0 END) as tes_gagal,
            AVG(CASE WHEN nilai IS NOT NULL THEN nilai ELSE 0 END) as rata_rata_nilai
        FROM peserta_tes
        WHERE id_peserta = ?
    ";
    $stmt = $conn->prepare($statsQuery);
    $stmt->bind_param('i', $peserta_id);
    $stmt->execute();
    $statsResult = $stmt->get_result()->fetch_assoc();
    
    // Get upcoming tests
    $upcomingQuery = "
        SELECT 
            jt.id_jadwal, jt.nama_tes, ks.nama_kategori as kategori,
            jt.tanggal_mulai, jt.tanggal_selesai, jt.durasi, 
            jt.jumlah_soal, jt.passing_grade, pt.id_peserta_tes, pt.status_tes
        FROM jadwal_tes jt
        LEFT JOIN kategori_soal ks ON jt.id_kategori = ks.id_kategori
        LEFT JOIN peserta_tes pt ON jt.id_jadwal = pt.id_jadwal AND pt.id_peserta = ?
        WHERE jt.tanggal_mulai > NOW()
        AND jt.status = 'aktif'
        ORDER BY jt.tanggal_mulai ASC
        LIMIT 5
    ";
    $stmt = $conn->prepare($upcomingQuery);
    $stmt->bind_param('i', $peserta_id);
    $stmt->execute();
    $upcomingResult = $stmt->get_result();
    
    $upcomingTests = [];
    while ($row = $upcomingResult->fetch_assoc()) {
        $upcomingTests[] = [
            'id_jadwal' => (int)$row['id_jadwal'],
            'nama_tes' => $row['nama_tes'],
            'kategori' => $row['kategori'] ?? 'Umum',
            'tanggal_mulai' => $row['tanggal_mulai'],
            'tanggal_selesai' => $row['tanggal_selesai'],
            'durasi' => (int)$row['durasi'],
            'jumlah_soal' => (int)$row['jumlah_soal'],
            'passing_grade' => (float)$row['passing_grade'],
            'status' => $row['status_tes'] ?? 'belum_mulai'
        ];
    }
    
    // Get in-progress tests
    $progressQuery = "
        SELECT 
            pt.id_peserta_tes, pt.id_jadwal, jt.nama_tes,
            pt.waktu_mulai, jt.durasi,
            TIMESTAMPDIFF(MINUTE, pt.waktu_mulai, NOW()) as waktu_berlalu
        FROM peserta_tes pt
        JOIN jadwal_tes jt ON pt.id_jadwal = jt.id_jadwal
        WHERE pt.id_peserta = ?
        AND pt.status_tes = 'sedang_tes'
        ORDER BY pt.waktu_mulai DESC
    ";
    $stmt = $conn->prepare($progressQuery);
    $stmt->bind_param('i', $peserta_id);
    $stmt->execute();
    $progressResult = $stmt->get_result();
    
    $progressTests = [];
    while ($row = $progressResult->fetch_assoc()) {
        $sisaWaktu = $row['durasi'] - (int)$row['waktu_berlalu'];
        $progressTests[] = [
            'id_peserta_tes' => (int)$row['id_peserta_tes'],
            'id_jadwal' => (int)$row['id_jadwal'],
            'nama_tes' => $row['nama_tes'],
            'waktu_mulai' => $row['waktu_mulai'],
            'durasi' => (int)$row['durasi'],
            'sisa_waktu' => max(0, $sisaWaktu),
            'status_tes' => 'sedang_tes'
        ];
    }
    
    // Get test history
    $historyQuery = "
        SELECT 
            pt.id_peserta_tes, jt.nama_tes,
            pt.waktu_selesai, pt.nilai, pt.status_kelulusan
        FROM peserta_tes pt
        JOIN jadwal_tes jt ON pt.id_jadwal = jt.id_jadwal
        WHERE pt.id_peserta = ?
        AND pt.status_tes = 'selesai'
        ORDER BY pt.waktu_selesai DESC
        LIMIT 10
    ";
    $stmt = $conn->prepare($historyQuery);
    $stmt->bind_param('i', $peserta_id);
    $stmt->execute();
    $historyResult = $stmt->get_result();
    
    $history = [];
    while ($row = $historyResult->fetch_assoc()) {
        $history[] = [
            'id_peserta_tes' => (int)$row['id_peserta_tes'],
            'nama_tes' => $row['nama_tes'],
            'tanggal_selesai' => $row['waktu_selesai'],
            'nilai' => (float)($row['nilai'] ?? 0),
            'status_kelulusan' => $row['status_kelulusan'] ?? 'belum_dinilai'
        ];
    }
    
    $response = [
        'peserta' => [
            'id_peserta' => (int)$pesertaResult['id_peserta'],
            'nama_lengkap' => $pesertaResult['nama_lengkap'],
            'nomor_peserta' => $pesertaResult['nomor_peserta'],
            'email' => $pesertaResult['email']
        ],
        'statistik' => [
            'total_tes' => (int)($statsResult['total_tes'] ?? 0),
            'tes_selesai' => (int)($statsResult['tes_selesai'] ?? 0),
            'tes_lulus' => (int)($statsResult['tes_lulus'] ?? 0),
            'tes_gagal' => (int)($statsResult['tes_gagal'] ?? 0),
            'rata_rata_nilai' => (float)($statsResult['rata_rata_nilai'] ?? 0)
        ],
        'jadwal_mendatang' => $upcomingTests,
        'tes_dalam_progress' => $progressTests,
        'riwayat_tes' => $history
    ];
    
    sendSuccess('Dashboard data berhasil diambil', $response);
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
