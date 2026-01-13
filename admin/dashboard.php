<?php
require_once '../config.php';
check_login_admin();

$page_title = 'Dashboard';

// Statistik Dashboard
$stat_peserta = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peserta"))['total'];
$stat_jadwal = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM jadwal_tes WHERE status = 'aktif'"))['total'];
$stat_soal = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM bank_soal"))['total'];
$stat_tes_selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peserta_tes WHERE status_tes = 'selesai'"))['total'];

// Data tes terbaru
$query_tes_terbaru = "SELECT pt.*, p.nama_lengkap, p.nomor_peserta, jt.nama_tes 
                      FROM peserta_tes pt
                      JOIN peserta p ON pt.id_peserta = p.id_peserta
                      JOIN jadwal_tes jt ON pt.id_jadwal = jt.id_jadwal
                      ORDER BY pt.created_at DESC LIMIT 10";
$result_tes_terbaru = mysqli_query($conn, $query_tes_terbaru);

// Jadwal tes aktif
$query_jadwal_aktif = "SELECT * FROM jadwal_tes WHERE status = 'aktif' ORDER BY tanggal_mulai DESC LIMIT 5";
$result_jadwal_aktif = mysqli_query($conn, $query_jadwal_aktif);

include 'includes/header.php';
?>

        <h2>Dashboard</h2>

        <?php show_alert(); ?>

        <!-- Statistik Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card text-white bg-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Peserta</h6>
                                <h2><?php echo $stat_peserta; ?></h2>
                            </div>
                            <div>
                                <i class="bi bi-people-fill" style="font-size: 3rem; opacity: 0.5;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card text-white bg-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Jadwal Aktif</h6>
                                <h2><?php echo $stat_jadwal; ?></h2>
                            </div>
                            <div>
                                <i class="bi bi-calendar-check" style="font-size: 3rem; opacity: 0.5;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card text-white bg-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Bank Soal</h6>
                                <h2><?php echo $stat_soal; ?></h2>
                            </div>
                            <div>
                                <i class="bi bi-journal-text" style="font-size: 3rem; opacity: 0.5;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card text-white bg-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Tes Selesai</h6>
                                <h2><?php echo $stat_tes_selesai; ?></h2>
                            </div>
                            <div>
                                <i class="bi bi-clipboard-check" style="font-size: 3rem; opacity: 0.5;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Tes Terbaru -->
            <div class="col-md-7">
                <div class="table-responsive">
                    <h5 class="mb-3"><i class="bi bi-clock-history"></i> Aktivitas Tes Terbaru</h5>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Peserta</th>
                                <th>Nama Tes</th>
                                <th>Status</th>
                                <th>Nilai</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($result_tes_terbaru)): ?>
                            <tr>
                                <td>
                                    <strong><?php echo $row['nama_lengkap']; ?></strong><br>
                                    <small class="text-muted"><?php echo $row['nomor_peserta']; ?></small>
                                </td>
                                <td><?php echo $row['nama_tes']; ?></td>
                                <td>
                                    <?php if($row['status_tes'] == 'selesai'): ?>
                                        <span class="badge bg-success">Selesai</span>
                                    <?php elseif($row['status_tes'] == 'sedang_tes'): ?>
                                        <span class="badge bg-warning">Sedang Tes</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Belum Mulai</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo $row['nilai'] ? number_format($row['nilai'], 2) : '-'; ?>
                                </td>
                                <td>
                                    <small><?php echo time_elapsed($row['created_at']); ?></small>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Jadwal Aktif -->
            <div class="col-md-5">
                <div class="table-responsive">
                    <h5 class="mb-3"><i class="bi bi-calendar-event"></i> Jadwal Tes Aktif</h5>
                    <?php while($row = mysqli_fetch_assoc($result_jadwal_aktif)): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="card-title"><?php echo $row['nama_tes']; ?></h6>
                            <p class="card-text mb-2">
                                <i class="bi bi-calendar3"></i> <?php echo format_tanggal($row['tanggal_mulai']); ?><br>
                                <i class="bi bi-clock"></i> <?php echo $row['durasi']; ?> menit<br>
                                <i class="bi bi-file-text"></i> <?php echo $row['jumlah_soal']; ?> soal
                            </p>
                            <span class="badge bg-success">Aktif</span>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

<?php include 'includes/footer.php'; ?>
