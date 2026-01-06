<?php
require '../config_api.php';
require '../auth_peserta.php';

$id_jadwal = $_GET['id_jadwal'];

$q = mysqli_query(
    $conn,
    "SELECT * FROM jadwal_tes WHERE id_jadwal=$id_jadwal"
);

json_response([
    'status' => 'success',
    'tes' => mysqli_fetch_assoc($q)
]);
