<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$input = file_get_contents('php://input');
$data = json_decode($input, true);

echo json_encode([
    'status' => 'success',
    'received_raw' => $input,
    'received_parsed' => $data,
    'method' => $_SERVER['REQUEST_METHOD'],
    'get_params' => $_GET,
    'php_version' => phpversion()
]);
