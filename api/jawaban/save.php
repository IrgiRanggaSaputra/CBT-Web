<?php
require '../config_api.php';
require '../auth_peserta.php';

$data = json_decode(file_get_contents("php://input"), true);

mysqli_query(
    $conn,
    "REPLACE INTO jawaban_detail
     (id_peserta_tes, id_soal, jawaban)
     VALUES ({$data['id_peserta_tes']}, {$data['id_soal']}, '{$data['jawaban']}')"
);

json_response(['status' => 'success']);
