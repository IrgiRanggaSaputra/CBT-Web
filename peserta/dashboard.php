<?php
require_once '../config.php';
check_login_peserta();

$peserta_id = $_SESSION['peserta_id'];

// Get active test schedules for this participant
$query = "SELECT jt.*, pt.id_peserta_tes, pt.status_tes, pt.waktu_mulai, pt.nilai, ks.nama_kategori
          FROM jadwal_tes jt
          LEFT JOIN peserta_tes pt ON jt.id_jadwal = pt.id_jadwal AND pt.id_peserta = $peserta_id
          LEFT JOIN kategori_soal ks ON jt.id_kategori = ks.id_kategori
          WHERE jt.status = 'aktif' 
          AND NOW() BETWEEN jt.tanggal_mulai AND jt.tanggal_selesai
          ORDER BY jt.tanggal_mulai DESC";
$result = mysqli_query($conn, $query);

include 'includes/header.php';
?>

<h2><i class="bi bi-laptop"></i> Dashboard Peserta</h2>

<?php show_alert(); ?>

<!-- Info Peserta -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h5><i class="bi bi-person-circle"></i> Informasi Peserta</h5>
                <table class="table table-borderless">
                    <tr>
                        <td width="200"><strong>Nama</strong></td>
                        <td>: <?php echo $_SESSION['peserta_nama']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Nomor Peserta</strong></td>
                        <td>: <?php echo $_SESSION['peserta_nomor']; ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Jadwal Tes -->
<div class="row">
    <div class="col-md-12">
        <h5 class="mb-3"><i class="bi bi-calendar-event"></i> Jadwal Tes Tersedia</h5>
        
        <?php if(mysqli_num_rows($result) == 0): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Tidak ada jadwal tes yang tersedia saat ini.
            </div>
        <?php else: ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="card-title"><?php echo $row['nama_tes']; ?></h5>
                            <p class="card-text">
                                <i class="bi bi-bookmarks"></i> <?php echo $row['nama_kategori']; ?><br>
                                <i class="bi bi-clock"></i> Durasi: <?php echo $row['durasi']; ?> menit<br>
                                <i class="bi bi-file-text"></i> Jumlah Soal: <?php echo $row['jumlah_soal']; ?><br>
                                <i class="bi bi-trophy"></i> Passing Grade: <?php echo $row['passing_grade']; ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <?php if($row['status_tes'] == 'selesai'): ?>
                                <span class="badge bg-success mb-2">Selesai</span><br>
                                <?php if(!$row['nilai']): ?>
                                    <span class="badge bg-warning">Menunggu Penilaian</span>
                                <?php endif; ?>
                            <?php elseif($row['status_tes'] == 'sedang_tes'): ?>
                                <a href="tes_lanjut.php?id=<?php echo $row['id_peserta_tes']; ?>" class="btn btn-warning">
                                    <i class="bi bi-play-circle"></i> Lanjutkan Tes
                                </a>
                            <?php else: ?>
                                <a href="tes_petunjuk.php?id=<?php echo $row['id_jadwal']; ?>" class="btn btn-primary">
                                    <i class="bi bi-play-circle-fill"></i> Mulai Tes
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
