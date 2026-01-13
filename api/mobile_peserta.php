<?php
/**
 * Mobile API - Peserta Profile Endpoints
 * Endpoints untuk profile peserta
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

// ==================== GET PROFILE ====================
if ($action === 'get' && $method === 'GET') {
    getProfile($peserta_id);
}

// ==================== UPDATE PROFILE ====================
elseif ($action === 'update' && $method === 'PUT') {
    updateProfile($peserta_id);
}

// ==================== CHANGE PASSWORD ====================
elseif ($action === 'change-password' && $method === 'PUT') {
    changePassword($peserta_id);
}

// ==================== DEFAULT ====================
else {
    sendError('Endpoint tidak ditemukan', 'NOT_FOUND', 404);
}

/**
 * Get Profile
 * GET /api/mobile_peserta.php?action=get
 */
function getProfile($peserta_id) {
    global $conn;
    
    $query = "
        SELECT id_peserta, nomor_peserta, nama_lengkap, email,
               jenis_kelamin, tanggal_lahir, telepon, alamat, status
        FROM peserta
        WHERE id_peserta = ?
        LIMIT 1
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $peserta_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendError('Peserta tidak ditemukan', 'NOT_FOUND', 404);
    }
    
    $peserta = $result->fetch_assoc();
    
    sendSuccess('Data profil berhasil diambil', formatPesertaData($peserta));
}

/**
 * Update Profile
 * PUT /api/mobile_peserta.php?action=update
 */
function updateProfile($peserta_id) {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    validateRequired($input, ['nama_lengkap', 'email', 'telepon', 'alamat']);
    
    $nama_lengkap = sanitizeInput($input['nama_lengkap']);
    $email = sanitizeInput($input['email']);
    $telepon = sanitizeInput($input['telepon']);
    $alamat = sanitizeInput($input['alamat']);
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendError('Format email tidak valid', 'VALIDATION_ERROR', 400);
    }
    
    $query = "
        UPDATE peserta
        SET nama_lengkap = ?,
            email = ?,
            telepon = ?,
            alamat = ?
        WHERE id_peserta = ?
    ";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        sendError('Database error: ' . $conn->error, 'DB_ERROR', 500);
    }
    
    $stmt->bind_param('ssssi', $nama_lengkap, $email, $telepon, $alamat, $peserta_id);
    
    if ($stmt->execute()) {
        sendSuccess('Profil berhasil diperbarui');
    } else {
        sendError('Gagal memperbarui profil', 'DB_ERROR', 500);
    }
}

/**
 * Change Password
 * PUT /api/mobile_peserta.php?action=change-password
 */
function changePassword($peserta_id) {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    validateRequired($input, ['password_lama', 'password_baru', 'konfirmasi_password']);
    
    $passwordLama = $input['password_lama'];
    $passwordBaru = $input['password_baru'];
    $konfirmasiPassword = $input['konfirmasi_password'];
    
    // Validate password
    if (strlen($passwordBaru) < 6) {
        sendError('Password minimal 6 karakter', 'VALIDATION_ERROR', 400);
    }
    
    if ($passwordBaru !== $konfirmasiPassword) {
        sendError('Konfirmasi password tidak cocok', 'VALIDATION_ERROR', 400);
    }
    
    // Get current password
    $query = "SELECT password FROM peserta WHERE id_peserta = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $peserta_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendError('Peserta tidak ditemukan', 'NOT_FOUND', 404);
    }
    
    $peserta = $result->fetch_assoc();
    
    // Verify old password
    if (!password_verify($passwordLama, $peserta['password'])) {
        sendError('Password lama tidak sesuai', 'AUTH_FAILED', 401);
    }
    
    // Hash new password
    $hashedPassword = password_hash($passwordBaru, PASSWORD_DEFAULT);
    
    // Update password
    $updateQuery = "UPDATE peserta SET password = ? WHERE id_peserta = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param('si', $hashedPassword, $peserta_id);
    
    if ($updateStmt->execute()) {
        sendSuccess('Password berhasil diubah');
    } else {
        sendError('Gagal mengubah password', 'DB_ERROR', 500);
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
