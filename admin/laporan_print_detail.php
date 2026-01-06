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

$status_akhir = $peserta_tes['nilai'] !== null ? ($peserta_tes['nilai'] >= $peserta_tes['passing_grade'] ? 'LULUS' : 'TIDAK LULUS') : 'BELUM DINILAI';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Detail Tes - <?php echo $peserta_tes['nama_lengkap']; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #333;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 12px;
            color: #666;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            background-color: #f0f0f0;
            padding: 8px 12px;
            margin-bottom: 10px;
            border-left: 4px solid #007bff;
        }
        
        .info-table {
            width: 100%;
            margin-bottom: 15px;
        }
        
        .info-table tr {
            border-bottom: 1px solid #ddd;
        }
        
        .info-table td {
            padding: 8px;
            font-size: 12px;
        }
        
        .info-table td:first-child {
            width: 40%;
            font-weight: bold;
            background-color: #f9f9f9;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .stat-box {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
            background-color: #f9f9f9;
            border-radius: 4px;
        }
        
        .stat-box .label {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .stat-box .value {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
        }
        
        .stat-box.success .value {
            color: #28a745;
        }
        
        .stat-box.danger .value {
            color: #dc3545;
        }
        
        .stat-box.warning .value {
            color: #ffc107;
        }
        
        .answer-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 20px;
        }
        
        .answer-table thead {
            background-color: #007bff;
            color: white;
        }
        
        .answer-table th, .answer-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .answer-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .answer-table .correct {
            background-color: #d4edda;
            color: #155724;
            font-weight: bold;
        }
        
        .answer-table .incorrect {
            background-color: #f8d7da;
            color: #721c24;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 11px;
            color: #666;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 100%;
            }
            .stats {
                grid-template-columns: repeat(4, 1fr);
            }
            .no-print {
                display: none;
            }
        }
        
        .print-button {
            text-align: center;
            margin-bottom: 20px;
            display: no-print;
        }
        
        .print-button button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .print-button button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="print-button no-print">
            <button onclick="window.print()">
                <i class="bi bi-printer"></i> Print / Export PDF
            </button>
        </div>
        
        <div class="header">
            <h1>LAPORAN DETAIL HASIL TES</h1>
            <p>CBT Learning Platform</p>
            <p>Tanggal Cetak: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>
        
        <!-- Informasi Peserta -->
        <div class="section">
            <div class="section-title">INFORMASI PESERTA</div>
            <table class="info-table">
                <tr>
                    <td>Nomor Peserta</td>
                    <td><?php echo $peserta_tes['nomor_peserta']; ?></td>
                </tr>
                <tr>
                    <td>Nama Lengkap</td>
                    <td><?php echo $peserta_tes['nama_lengkap']; ?></td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td><?php echo $peserta_tes['email']; ?></td>
                </tr>
                <tr>
                    <td>Telepon</td>
                    <td><?php echo $peserta_tes['telepon']; ?></td>
                </tr>
            </table>
        </div>
        
        <!-- Informasi Tes -->
        <div class="section">
            <div class="section-title">INFORMASI TES</div>
            <table class="info-table">
                <tr>
                    <td>Nama Tes</td>
                    <td><?php echo $peserta_tes['nama_tes']; ?></td>
                </tr>
                <tr>
                    <td>Tanggal Tes</td>
                    <td><?php echo date('d/m/Y', strtotime($peserta_tes['created_at'])); ?></td>
                </tr>
                <tr>
                    <td>Waktu Mulai</td>
                    <td><?php echo $peserta_tes['waktu_mulai'] ? date('H:i:s', strtotime($peserta_tes['waktu_mulai'])) : '-'; ?></td>
                </tr>
                <tr>
                    <td>Waktu Selesai</td>
                    <td><?php echo $peserta_tes['waktu_selesai'] ? date('H:i:s', strtotime($peserta_tes['waktu_selesai'])) : '-'; ?></td>
                </tr>
                <tr>
                    <td>Durasi Tes</td>
                    <td><?php echo $peserta_tes['durasi']; ?> menit</td>
                </tr>
            </table>
        </div>
        
        <!-- Statistik Hasil -->
        <div class="section">
            <div class="section-title">STATISTIK HASIL TES</div>
            <div class="stats">
                <div class="stat-box">
                    <div class="label">Total Soal</div>
                    <div class="value"><?php echo $total_soal; ?></div>
                </div>
                <div class="stat-box success">
                    <div class="label">Jawaban Benar</div>
                    <div class="value"><?php echo $jumlah_benar; ?></div>
                </div>
                <div class="stat-box danger">
                    <div class="label">Jawaban Salah</div>
                    <div class="value"><?php echo $jumlah_salah; ?></div>
                </div>
                <div class="stat-box">
                    <div class="label">Presentase</div>
                    <div class="value"><?php echo $total_soal > 0 ? number_format(($jumlah_benar / $total_soal) * 100, 1) : 0; ?>%</div>
                </div>
            </div>
        </div>
        
        <!-- Hasil Akhir -->
        <div class="section">
            <div class="section-title">HASIL AKHIR</div>
            <table class="info-table">
                <tr>
                    <td>Nilai Akhir</td>
                    <td style="font-weight: bold; font-size: 16px; <?php echo $peserta_tes['nilai'] !== null ? ($peserta_tes['nilai'] >= $peserta_tes['passing_grade'] ? 'color: #28a745;' : 'color: #dc3545;') : ''; ?>">
                        <?php echo $peserta_tes['nilai'] !== null ? number_format($peserta_tes['nilai'], 2) : '-'; ?>
                    </td>
                </tr>
                <tr>
                    <td>Passing Grade (Minimum)</td>
                    <td><?php echo $peserta_tes['passing_grade']; ?></td>
                </tr>
                <tr>
                    <td>Status Kelulusan</td>
                    <td style="font-weight: bold; font-size: 14px; <?php echo $status_akhir == 'LULUS' ? 'color: #28a745;' : ($status_akhir == 'TIDAK LULUS' ? 'color: #dc3545;' : 'color: #666;'); ?>">
                        <?php echo $status_akhir; ?>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Detail Jawaban -->
        <div class="section">
            <div class="section-title">DETAIL JAWABAN PESERTA</div>
            <table class="answer-table">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="45%">Pertanyaan</th>
                        <th width="15%">Jawab Peserta</th>
                        <th width="15%">Jawab Benar</th>
                        <th width="10%">Status</th>
                        <th width="10%">Bobot</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    mysqli_data_seek($jawaban_result, 0);
                    while($row = mysqli_fetch_assoc($jawaban_result)): 
                    $jawaban_peserta = $row['jawaban'] ? $row['jawaban'] : 'Tidak dijawab';
                    $row_class = $row['is_correct'] ? 'correct' : 'incorrect';
                    $status = $row['is_correct'] ? '✓ Benar' : '✗ Salah';
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo substr($row['pertanyaan'], 0, 80); ?><?php echo strlen($row['pertanyaan']) > 80 ? '...' : ''; ?></td>
                        <td><?php echo $jawaban_peserta; ?></td>
                        <td><?php echo $row['jawaban_benar']; ?></td>
                        <td class="<?php echo $row_class; ?>"><?php echo $status; ?></td>
                        <td><?php echo $row['bobot']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <div class="footer">
            <p>Laporan ini dicetak dari Sistem CBT Learning Platform</p>
            <p><?php echo 'Halaman ini digenerate pada: ' . date('d/m/Y H:i:s'); ?></p>
        </div>
    </div>
</body>
</html>
