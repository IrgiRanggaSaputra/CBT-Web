<?php
require '../config_api.php';
require '../auth_peserta.php';

$data = json_decode(file_get_contents("php://input"), true);

mysqli_query(
    $conn,
    "UPDATE peserta_tes
     SET status_tes='selesai'
     WHERE id_peserta_tes={$data['id_peserta_tes']}
     AND id_peserta={$peserta['id']}"
);

json_response(['status' => 'success']);
