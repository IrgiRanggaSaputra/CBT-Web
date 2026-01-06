<?php
require_once __DIR__ . '/resources.php';
require_auth_admin();

$resource = $_GET['resource'] ?? '';
$config = resource_config($resource);
if (!$config) {
    json_response(['status' => 'error', 'message' => 'Resource tidak dikenal'], 400);
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($id) {
    $pk = $config['pk'];
    $sql = "SELECT * FROM {$config['table']} WHERE $pk = $id";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        json_response(['status' => 'error', 'message' => mysqli_error($conn)], 500);
    }
    $row = mysqli_fetch_assoc($result);
    json_response(['status' => 'success', 'data' => $row]);
} else {
    $result = mysqli_query($conn, "SELECT * FROM {$config['table']} ORDER BY {$config['pk']} DESC");
    if (!$result) {
        json_response(['status' => 'error', 'message' => mysqli_error($conn)], 500);
    }
    $rows = [];
    while ($r = mysqli_fetch_assoc($result)) { $rows[] = $r; }
    json_response(['status' => 'success', 'data' => $rows]);
}
