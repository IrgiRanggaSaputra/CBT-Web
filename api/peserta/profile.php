<?php
require '../config_api.php';
require '../auth_peserta.php';

json_response([
    'status' => 'success',
    'data' => [
        'nama' => $peserta['nama'],
        'nomor' => $peserta['nomor_peserta'],
        'email' => $peserta['email']
    ]
]);
