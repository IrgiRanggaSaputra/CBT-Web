<?php
require_once __DIR__ . '/../config.php';

function json_response($data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function require_auth_admin(): void {
    if (!isset($_SESSION['admin_id'])) {
        json_response([
            'status' => 'error',
            'message' => 'Unauthorized: admin belum login'
        ], 401);
    }
}

function require_auth_peserta(): void {
    if (!isset($_SESSION['peserta_id'])) {
        json_response([
            'status' => 'error',
            'message' => 'Unauthorized: peserta belum login'
        ], 401);
    }
}

function sanitize_int($value): int {
    return (int)$value;
}

function get_input_data(): array {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
    return $_POST;
}
