<?php
/**
 * Mobile API - Configuration & CORS Headers
 * Setup untuk integrasi dengan aplikasi mobile Flutter
 */

require_once __DIR__ . '/../config.php';

// ==================== CORS Configuration ====================
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ==================== Helper Functions ====================

/**
 * Send JSON Response
 */
function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

/**
 * Send Success Response
 */
function sendSuccess($message, $data = null, $status = 200) {
    sendResponse([
        'status' => 'success',
        'message' => $message,
        'data' => $data
    ], $status);
}

/**
 * Send Error Response
 */
function sendError($message, $error_code = 'ERROR', $status = 400) {
    sendResponse([
        'status' => 'error',
        'message' => $message,
        'error_code' => $error_code
    ], $status);
}

/**
 * Get Authorization Token from Header
 */
function getAuthToken() {
    $headers = getallheaders();
    
    if (isset($headers['Authorization'])) {
        $auth = $headers['Authorization'];
        // Bearer token format: "Bearer token_string"
        if (strpos($auth, 'Bearer ') === 0) {
            return substr($auth, 7);
        }
        return $auth;
    }
    
    return null;
}

/**
 * Verify Authorization Token
 * Token = peserta_id|timestamp|hash
 */
function verifyToken($token) {
    if (!$token) {
        sendError('Token tidak ditemukan', 'UNAUTHORIZED', 401);
    }

    // Untuk saat ini, gunakan session-based auth
    // Token = hasil hash dari id_peserta + timestamp
    // Di production, gunakan JWT atau OAuth2
    
    // Contoh: verifikasi token dari database
    global $conn;
    
    // Extract peserta_id dari token (simplified)
    // In production, implement proper JWT verification
    $_SESSION['peserta_id'] = true; // Placeholder
    
    return true;
}

/**
 * Get Peserta ID from Session or Token
 */
function getPesertaId() {
    if (isset($_SESSION['peserta_id'])) {
        return $_SESSION['peserta_id'];
    }
    
    return null;
}

/**
 * Validate Required Fields
 */
function validateRequired($data, $fields) {
    foreach ($fields as $field) {
        if (!isset($data[$field]) || empty(trim((string)$data[$field]))) {
            sendError("Field '$field' harus diisi", 'VALIDATION_ERROR', 400);
        }
    }
}

/**
 * Sanitize Input
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Format Response Data
 */
function formatPesertaData($row) {
    return [
        'id_peserta' => (int)$row['id_peserta'],
        'nomor_peserta' => $row['nomor_peserta'],
        'nama_lengkap' => $row['nama_lengkap'],
        'email' => $row['email'],
        'jenis_kelamin' => $row['jenis_kelamin'] ?? null,
        'tanggal_lahir' => $row['tanggal_lahir'] ?? null,
        'telepon' => $row['telepon'] ?? null,
        'alamat' => $row['alamat'] ?? null,
        'status' => $row['status'] ?? 'aktif'
    ];
}

/**
 * Get Peserta ID from Firebase UID
 */
function getPesertaIdFromFirebaseUID($firebase_uid) {
    global $conn;
    
    $firebase_uid = sanitizeInput($firebase_uid);
    
    $query = "SELECT id_peserta FROM peserta WHERE firebase_uid = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $firebase_uid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return null;
    }
    
    $row = $result->fetch_assoc();
    return (int)$row['id_peserta'];
}
?>

