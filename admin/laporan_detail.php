<?php
require_once '../config.php';
check_login_admin();

$id_peserta_tes = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_peserta_tes == 0) {
    alert('ID peserta tes tidak valid!', 'danger');
    redirect('admin/laporan.php');
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
    alert('Data peserta tes tidak ditemukan!', 'danger');
    redirect('admin/laporan.php');
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

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-clipboard-check"></i> Detail Hasil Tes</h2>
    <a href="laporan.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<?php show_alert(); ?>

<!-- Kartu Informasi Peserta -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Informasi Peserta</h5>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Nomor Peserta</strong></td>
                        <td><?php echo $peserta_tes['nomor_peserta']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Nama Lengkap</strong></td>
                        <td><?php echo $peserta_tes['nama_lengkap']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Email</strong></td>
                        <td><?php echo $peserta_tes['email']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Telepon</strong></td>
                        <td><?php echo $peserta_tes['telepon']; ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Informasi Tes</h5>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Nama Tes</strong></td>
                        <td><?php echo $peserta_tes['nama_tes']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal Tes</strong></td>
                        <td><?php echo date('d/m/Y', strtotime($peserta_tes['created_at'])); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Waktu Mulai</strong></td>
                        <td><?php echo $peserta_tes['waktu_mulai'] ? date('H:i:s', strtotime($peserta_tes['waktu_mulai'])) : '-'; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Waktu Selesai</strong></td>
                        <td><?php echo $peserta_tes['waktu_selesai'] ? date('H:i:s', strtotime($peserta_tes['waktu_selesai'])) : '-'; ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Kartu Hasil -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title text-muted">Total Soal</h6>
                <h3><?php echo $total_soal; ?> Soal</h3>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title text-muted">Jawaban Benar</h6>
                <h3 class="text-success"><?php echo $jumlah_benar; ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title text-muted">Jawaban Salah</h6>
                <h3 class="text-danger"><?php echo $jumlah_salah; ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title text-muted">Nilai Akhir</h6>
                <?php if($peserta_tes['nilai'] !== null): ?>
                    <h3 class="<?php echo $peserta_tes['nilai'] >= $peserta_tes['passing_grade'] ? 'text-success' : 'text-danger'; ?>">
                        <?php echo number_format($peserta_tes['nilai'], 2); ?>
                    </h3>
                    <small class="<?php echo $peserta_tes['nilai'] >= $peserta_tes['passing_grade'] ? 'text-success' : 'text-danger'; ?>">
                        <?php echo $peserta_tes['nilai'] >= $peserta_tes['passing_grade'] ? 'LULUS' : 'TIDAK LULUS'; ?>
                        (Min. <?php echo $peserta_tes['passing_grade']; ?>)
                    </small>
                <?php else: ?>
                    <h3 class="text-muted">-</h3>
                    <small class="text-muted">Belum dinilai</small>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Detail Jawaban -->
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Detail Jawaban Peserta</h5>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="45%">Pertanyaan</th>
                        <th width="15%">Jawaban Peserta</th>
                        <th width="15%">Jawaban Benar</th>
                        <th width="10%">Status</th>
                        <th width="10%">Bobot</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    mysqli_data_seek($jawaban_result, 0);
                    while($row = mysqli_fetch_assoc($jawaban_result)): 
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td>
                            <strong><?php echo substr($row['pertanyaan'], 0, 100); ?></strong>
                            <?php if(strlen($row['pertanyaan']) > 100): ?>
                                ...
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($row['jawaban']): ?>
                                <span class="badge bg-primary"><?php echo $row['jawaban']; ?></span>
                            <?php else: ?>
                                <span class="text-muted">Tidak dijawab</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-success"><?php echo $row['jawaban_benar']; ?></span>
                        </td>
                        <td>
                            <?php if($row['is_correct']): ?>
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Benar</span>
                            <?php else: ?>
                                <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Salah</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $row['bobot']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Opsi Export -->
<div class="mt-4">
    <a href="laporan_export_detail.php?id=<?php echo $id_peserta_tes; ?>" class="btn btn-success" target="_blank">
        <i class="bi bi-file-earmark-excel"></i> Export ke Excel
    </a>
    <a href="laporan_print_detail.php?id=<?php echo $id_peserta_tes; ?>" class="btn btn-info" target="_blank">
        <i class="bi bi-printer"></i> Print
    </a>
</div>

<?php include 'includes/footer.php'; ?>
