<?php

function json_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function unauthorized($message = 'Unauthorized') {
    json_response([
        'status' => 'error',
        'message' => $message
    ], 401);
}
