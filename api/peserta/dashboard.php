<?php
require_once __DIR__ . '/../config_api.php';
require_once __DIR__ . '/../helpers_api.php';
require_once __DIR__ . '/../auth_peserta.php';

$query = "
SELECT jt.*, pt.status_tes, pt.nilai, ks.nama_kategori
FROM jadwal_tes jt
LEFT JOIN peserta_tes pt 
  ON jt.id_jadwal = pt.id_jadwal AND pt.id_peserta = $peserta_id
LEFT JOIN kategori_soal ks 
  ON jt.id_kategori = ks.id_kategori
WHERE jt.status = 'aktif'
ORDER BY jt.tanggal_mulai DESC
";

$result = mysqli_query($conn, $query);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

json_response([
    'status' => 'success',
    'peserta' => $peserta,
    'jadwal' => $data
]);
