<?php
require_once '../config.php';
check_login_admin();

$id_peserta_tes = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_peserta_tes == 0) {
    die('ID peserta tes tidak valid!');
}

// Ambil data peserta tes
$query = "SELECT pt.*, p.nama_lengkap, p.nomor_peserta, p.email, p.telepon, jt.nama_tes, jt.durasi, jt.jumlah_soal, jt.passing_grade
          FROM peserta_tes pt
          JOIN peserta p ON pt.id_peserta = p.id_peserta
          JOIN jadwal_tes jt ON pt.id_jadwal = jt.id_jadwal
          WHERE pt.id_peserta_tes = $id_peserta_tes";
$result = mysqli_query($conn, $query);
$peserta_tes = mysqli_fetch_assoc($result);

if (!$peserta_tes) {
    die('Data peserta tes tidak ditemukan!');
}

// Ambil jawaban peserta
$jawaban_query = "SELECT jp.*, bs.pertanyaan, bs.pilihan_a, bs.pilihan_b, bs.pilihan_c, bs.pilihan_d, bs.pilihan_e, bs.jawaban_benar, bs.bobot
                  FROM jawaban_peserta jp
                  JOIN bank_soal bs ON jp.id_soal = bs.id_soal
                  ORDER BY jp.id_jawaban";
$jawaban_result = mysqli_query($conn, $jawaban_query);

// Hitung statistik
$total_soal = mysqli_num_rows($jawaban_result);
$benar = mysqli_query($conn, "SELECT COUNT(*) as total FROM jawaban_peserta WHERE id_peserta_tes = $id_peserta_tes AND is_correct = 1");
$result_benar = mysqli_fetch_assoc($benar);
$jumlah_benar = $result_benar['total'];
$jumlah_salah = $total_soal - $jumlah_benar;

// Set header untuk Excel
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="detail_tes_' . $peserta_tes['nomor_peserta'] . '_' . date('YmdHis') . '.xlsx"');
header('Cache-Control: max-age=0');

// Buat CSV dengan encoding UTF-8 (kompatibel dengan Excel)
echo "\xEF\xBB\xBF"; // BOM untuk UTF-8

// Header
echo "LAPORAN DETAIL HASIL TES\n\n";

// Informasi Peserta
echo "INFORMASI PESERTA\n";
echo "Nomor Peserta," . $peserta_tes['nomor_peserta'] . "\n";
echo "Nama Lengkap," . $peserta_tes['nama_lengkap'] . "\n";
echo "Email," . $peserta_tes['email'] . "\n";
echo "Telepon," . $peserta_tes['telepon'] . "\n\n";

// Informasi Tes
echo "INFORMASI TES\n";
echo "Nama Tes," . $peserta_tes['nama_tes'] . "\n";
echo "Tanggal Tes," . date('d/m/Y', strtotime($peserta_tes['created_at'])) . "\n";
echo "Waktu Mulai," . ($peserta_tes['waktu_mulai'] ? date('d/m/Y H:i:s', strtotime($peserta_tes['waktu_mulai'])) : '-') . "\n";
echo "Waktu Selesai," . ($peserta_tes['waktu_selesai'] ? date('d/m/Y H:i:s', strtotime($peserta_tes['waktu_selesai'])) : '-') . "\n\n";

// Hasil
echo "HASIL TES\n";
echo "Total Soal," . $total_soal . "\n";
echo "Jawaban Benar," . $jumlah_benar . "\n";
echo "Jawaban Salah," . $jumlah_salah . "\n";
echo "Nilai Akhir," . ($peserta_tes['nilai'] !== null ? number_format($peserta_tes['nilai'], 2) : '-') . "\n";
echo "Passing Grade," . $peserta_tes['passing_grade'] . "\n";
echo "Status," . ($peserta_tes['nilai'] !== null ? ($peserta_tes['nilai'] >= $peserta_tes['passing_grade'] ? 'LULUS' : 'TIDAK LULUS') : 'BELUM DINILAI') . "\n\n";

// Detail Jawaban
echo "DETAIL JAWABAN PESERTA\n";
echo "No,Pertanyaan,Jawaban Peserta,Jawaban Benar,Status,Bobot\n";

$no = 1;
mysqli_data_seek($jawaban_result, 0);
while($row = mysqli_fetch_assoc($jawaban_result)) {
    $jawaban_peserta = $row['jawaban'] ? $row['jawaban'] : 'Tidak dijawab';
    $status = $row['is_correct'] ? 'Benar' : 'Salah';
    
    echo $no . ',"' . addslashes($row['pertanyaan']) . '","' . $jawaban_peserta . '","' . $row['jawaban_benar'] . '","' . $status . '",' . $row['bobot'] . "\n";
    $no++;
}

exit;
?>
