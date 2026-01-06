<?php
require_once __DIR__ . '/resources.php';
require_auth_admin();

$resource = $_GET['resource'] ?? '';
$config = resource_config($resource);
if (!$config) {
    json_response(['status' => 'error', 'message' => 'Resource tidak dikenal'], 400);
}

$data = get_input_data();
apply_input_transformations($resource, $data);

$cols = [];
$vals = [];
foreach ($config['columns'] as $col) {
    if (array_key_exists($col, $data)) {
        $cols[] = $col;
        $vals[] = "'" . mysqli_real_escape_string($conn, (string)$data[$col]) . "'";
    }
}
if (!$cols) {
    json_response(['status' => 'error', 'message' => 'Tidak ada field yang valid untuk disimpan'], 400);
}

$sql = "INSERT INTO {$config['table']} (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")";
if (!mysqli_query($conn, $sql)) {
    json_response(['status' => 'error', 'message' => mysqli_error($conn)], 500);
}
$newId = mysqli_insert_id($conn);
json_response(['status' => 'success', 'id' => $newId]);
