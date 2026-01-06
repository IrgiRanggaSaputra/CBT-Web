<?php
// Shared resource configuration for CRUD endpoints
require_once __DIR__ . '/_helpers.php';

function resource_config(string $resource): ?array {
    $configs = [
        'peserta' => [
            'table' => 'peserta',
            'pk' => 'id_peserta',
            'columns' => ['nomor_peserta','nama_lengkap','email','password','jenis_kelamin','tanggal_lahir','telepon','alamat','status'],
            'readonly' => ['created_at'],
        ],
        'kategori_soal' => [
            'table' => 'kategori_soal',
            'pk' => 'id_kategori',
            'columns' => ['nama_kategori','deskripsi'],
            'readonly' => ['created_at'],
        ],
        'bank_soal' => [
            'table' => 'bank_soal',
            'pk' => 'id_soal',
            'columns' => ['id_kategori','pertanyaan','pilihan_a','pilihan_b','pilihan_c','pilihan_d','pilihan_e','jawaban_benar','bobot','gambar'],
            'readonly' => ['created_at'],
        ],
        'jadwal_tes' => [
            'table' => 'jadwal_tes',
            'pk' => 'id_jadwal',
            'columns' => ['nama_tes','id_kategori','tanggal_mulai','tanggal_selesai','durasi','jumlah_soal','passing_grade','instruksi','status'],
            'readonly' => ['created_at'],
        ],
        'peserta_tes' => [
            'table' => 'peserta_tes',
            'pk' => 'id_peserta_tes',
            'columns' => ['id_jadwal','id_peserta','token','status_tes','waktu_mulai','waktu_selesai','nilai','status_kelulusan'],
            'readonly' => ['created_at'],
        ],
        'soal_tes' => [
            'table' => 'soal_tes',
            'pk' => 'id_soal_tes',
            'columns' => ['id_jadwal','id_soal','nomor_urut'],
            'readonly' => [],
        ],
        'jawaban_peserta' => [
            'table' => 'jawaban_peserta',
            'pk' => 'id_jawaban',
            'columns' => ['id_peserta_tes','id_soal','jawaban','is_correct'],
            'readonly' => ['waktu_jawab'],
        ],
    ];
    return $configs[$resource] ?? null;
}

function apply_input_transformations(string $resource, array &$data): void {
    if ($resource === 'peserta' && isset($data['password'])) {
        if ($data['password'] === '' || $data['password'] === null) {
            unset($data['password']);
        } else {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
    }
}
