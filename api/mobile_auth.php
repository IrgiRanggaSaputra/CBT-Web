<?php
/**
 * Mobile API - Authentication Endpoints
 * Endpoints untuk login, logout, dan verifikasi token
 */

require_once __DIR__ . '/mobile_config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ==================== LOGIN ====================
if ($action === 'login' && $method === 'POST') {
    loginPeserta();
}

// ==================== LOGOUT ====================
elseif ($action === 'logout' && $method === 'POST') {
    logoutPeserta();
}

// ==================== VERIFY TOKEN ====================
elseif ($action === 'verify-token' && $method === 'POST') {
    verifyTokenEndpoint();
}

// ==================== DEFAULT ====================
else {
    sendError('Endpoint tidak ditemukan', 'NOT_FOUND', 404);
}

/**
 * Login Peserta
 * POST /api/mobile_auth.php?action=login
 */
function loginPeserta() {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    validateRequired($input, ['nomor_peserta', 'password']);
    
    $nomor_peserta = sanitizeInput($input['nomor_peserta']);
    $password = $input['password'];
    
    // Query peserta dari database
    $query = "
        SELECT id_peserta, nomor_peserta, nama_lengkap, email, 
               jenis_kelamin, tanggal_lahir, telepon, alamat, 
               password, status
        FROM peserta 
        WHERE nomor_peserta = ? 
        AND status = 'aktif'
        LIMIT 1
    ";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        sendError('Database error: ' . $conn->error, 'DB_ERROR', 500);
    }
    
    $stmt->bind_param('s', $nomor_peserta);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Log failed attempt (optional)
        sendError('Nomor peserta atau password salah', 'AUTH_FAILED', 401);
    }
    
    $peserta = $result->fetch_assoc();
    
    // Verify password
    if (!password_verify($password, $peserta['password'])) {
        sendError('Nomor peserta atau password salah', 'AUTH_FAILED', 401);
    }
    
    // Generate token
    $token = generateToken($peserta['id_peserta']);
    
    // Response
    sendSuccess('Login berhasil', [
        'id_peserta' => (int)$peserta['id_peserta'],
        'nomor_peserta' => $peserta['nomor_peserta'],
        'nama_lengkap' => $peserta['nama_lengkap'],
        'email' => $peserta['email'],
        'jenis_kelamin' => $peserta['jenis_kelamin'],
        'tanggal_lahir' => $peserta['tanggal_lahir'],
        'telepon' => $peserta['telepon'],
        'alamat' => $peserta['alamat'],
        'token' => $token
    ], 200);
}

/**
 * Logout Peserta
 * POST /api/mobile_auth.php?action=logout
 */
function logoutPeserta() {
    $token = getAuthToken();
    
    if (!$token) {
        sendError('Token tidak ditemukan', 'UNAUTHORIZED', 401);
    }
    
    // Destroy session
    session_destroy();
    
    sendSuccess('Logout berhasil');
}

/**
 * Verify Token
 * POST /api/mobile_auth.php?action=verify-token
 */
function verifyTokenEndpoint() {
    $token = getAuthToken();
    
    if (!$token) {
        sendError('Token tidak ditemukan', 'UNAUTHORIZED', 401);
    }
    
    $peserta_id = verifyTokenAndGetPesertaId($token);
    
    if (!$peserta_id) {
        sendError('Token tidak valid', 'INVALID_TOKEN', 401);
    }
    
    sendSuccess('Token valid', [
        'valid' => true,
        'peserta_id' => $peserta_id
    ]);
}

/**
 * Generate Token
 * Format: peserta_id|timestamp|hash
 */
function generateToken($peserta_id) {
    $timestamp = time();
    $secret = $_ENV['APP_SECRET'] ?? 'your-secret-key';
    $hash = hash('sha256', $peserta_id . $timestamp . $secret);
    
    return base64_encode($peserta_id . '|' . $timestamp . '|' . $hash);
}

/**
 * Verify Token and Get Peserta ID
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
