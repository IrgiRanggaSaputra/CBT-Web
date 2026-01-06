<?php
require_once '../config.php';
check_login_admin();

// Get ongoing tests
$query = "SELECT pt.*, p.nama_lengkap, p.nomor_peserta, jt.nama_tes, jt.durasi
          FROM peserta_tes pt
          JOIN peserta p ON pt.id_peserta = p.id_peserta
          JOIN jadwal_tes jt ON pt.id_jadwal = jt.id_jadwal
          WHERE pt.status_tes = 'sedang_tes'
          ORDER BY pt.waktu_mulai DESC";
$result = mysqli_query($conn, $query);

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-display"></i> Monitoring Real-time</h2>
    <button class="btn btn-primary" onclick="location.reload()">
        <i class="bi bi-arrow-clockwise"></i> Refresh
    </button>
</div>

<?php show_alert(); ?>

<div class="card">
    <div class="card-body">
        <h5 class="mb-3">Peserta yang Sedang Mengerjakan Tes</h5>
        
        <?php if(mysqli_num_rows($result) == 0): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Tidak ada peserta yang sedang mengerjakan tes saat ini.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Peserta</th>
                            <th>Nama Tes</th>
                            <th>Waktu Mulai</th>
                            <th>Durasi</th>
                            <th>Sisa Waktu</th>
                            <th>Progress</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while($row = mysqli_fetch_assoc($result)): 
                            // Calculate remaining time
                            $waktu_mulai = strtotime($row['waktu_mulai']);
                            $durasi_detik = $row['durasi'] * 60;
                            $waktu_sekarang = time();
                            $elapsed = $waktu_sekarang - $waktu_mulai;
                            $sisa = $durasi_detik - $elapsed;
                            
                            $sisa_menit = floor($sisa / 60);
                            $sisa_detik = $sisa % 60;
                            
                            // Get progress
                            $id_jadwal = $row['id_jadwal'];
                            $jumlah_soal = mysqli_fetch_assoc(mysqli_query($conn, "SELECT jumlah_soal FROM jadwal_tes WHERE id_jadwal = $id_jadwal"))['jumlah_soal'];
                            $dijawab = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM jawaban_peserta WHERE id_peserta_tes = {$row['id_peserta_tes']}"))['total'];
                            $progress = round(($dijawab / $jumlah_soal) * 100);
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td>
                                <strong><?php echo $row['nama_lengkap']; ?></strong><br>
                                <small class="text-muted"><?php echo $row['nomor_peserta']; ?></small>
                            </td>
                            <td><?php echo $row['nama_tes']; ?></td>
                            <td><?php echo date('H:i:s', strtotime($row['waktu_mulai'])); ?></td>
                            <td><?php echo $row['durasi']; ?> menit</td>
                            <td>
                                <?php if($sisa > 0): ?>
                                    <span class="badge <?php echo $sisa < 300 ? 'bg-danger' : ($sisa < 600 ? 'bg-warning' : 'bg-success'); ?>">
                                        <?php echo $sisa_menit; ?>:<?php echo str_pad($sisa_detik, 2, '0', STR_PAD_LEFT); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Habis</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $progress; ?>%" aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100">
                                        <?php echo $dijawab; ?>/<?php echo $jumlah_soal; ?> (<?php echo $progress; ?>%)
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-warning">
                                    <i class="bi bi-hourglass-split"></i> Sedang Tes
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card mt-4">
    <div class="card-body">
        <h5 class="mb-3">Statistik Hari Ini</h5>
        <div class="row">
            <div class="col-md-3">
                <div class="text-center">
                    <h3 class="text-primary">
                        <?php 
                        $today_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peserta_tes WHERE DATE(created_at) = CURDATE()"))['total'];
                        echo $today_total;
                        ?>
                    </h3>
                    <p>Total Tes Hari Ini</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <h3 class="text-success">
                        <?php 
                        $today_finished = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peserta_tes WHERE DATE(created_at) = CURDATE() AND status_tes = 'selesai'"))['total'];
                        echo $today_finished;
                        ?>
                    </h3>
                    <p>Selesai</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <h3 class="text-warning">
                        <?php 
                        $today_ongoing = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peserta_tes WHERE DATE(created_at) = CURDATE() AND status_tes = 'sedang_tes'"))['total'];
                        echo $today_ongoing;
                        ?>
                    </h3>
                    <p>Sedang Berlangsung</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <h3 class="text-info">
                        <?php 
                        $today_avg = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(nilai) as rata FROM peserta_tes WHERE DATE(created_at) = CURDATE() AND nilai IS NOT NULL"))['rata'];
                        echo $today_avg ? number_format($today_avg, 2) : '0';
                        ?>
                    </h3>
                    <p>Rata-rata Nilai</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto refresh every 30 seconds
    setTimeout(function() {
        location.reload();
    }, 30000);
</script>

<?php include 'includes/footer.php'; ?>
