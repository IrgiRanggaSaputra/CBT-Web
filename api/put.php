<?php
require_once __DIR__ . '/resources.php';
require_auth_admin();

$resource = $_GET['resource'] ?? '';
$config = resource_config($resource);
if (!$config) {
    json_response(['status' => 'error', 'message' => 'Resource tidak dikenal'], 400);
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$id) { json_response(['status' => 'error', 'message' => 'Parameter id diperlukan'], 400); }

$data = get_input_data();
apply_input_transformations($resource, $data);

$pairs = [];
foreach ($config['columns'] as $col) {
    if (array_key_exists($col, $data)) {
        $pairs[] = $col . "='" . mysqli_real_escape_string($conn, (string)$data[$col]) . "'";
    }
}
if (!$pairs) {
    json_response(['status' => 'error', 'message' => 'Tidak ada field yang valid untuk diperbarui'], 400);
}

$pk = $config['pk'];
$sql = "UPDATE {$config['table']} SET " . implode(',', $pairs) . " WHERE $pk = $id";
if (!mysqli_query($conn, $sql)) {
    json_response(['status' => 'error', 'message' => mysqli_error($conn)], 500);
}
json_response(['status' => 'success']);
