<?php
require '../config_api.php';
require '../auth_peserta.php';

$data = json_decode(file_get_contents("php://input"), true);
$id_jadwal = $data['id_jadwal'];

mysqli_query(
    $conn,
    "INSERT INTO peserta_tes (id_peserta, id_jadwal, status_tes, waktu_mulai)
     VALUES ({$peserta['id']}, $id_jadwal, 'sedang_tes', NOW())"
);

json_response([
    'status' => 'success',
    'id_peserta_tes' => mysqli_insert_id($conn)
]);
