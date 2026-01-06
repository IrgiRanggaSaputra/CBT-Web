<?php
require_once '../config.php';
check_login_admin();

// Hapus jadwal
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Cek apakah ada peserta yang sudah mengerjakan tes
    $check_peserta = mysqli_query($conn, "SELECT COUNT(*) as total FROM peserta_tes WHERE id_jadwal = $id AND status_tes != 'belum_mulai'");
    $result_check = mysqli_fetch_assoc($check_peserta);
    
    if ($result_check['total'] > 0) {
        alert('Tidak dapat menghapus jadwal! Ada peserta yang sudah mulai atau selesai mengerjakan tes.', 'danger');
        redirect('admin/jadwal_tes.php');
    }
    
    // Hapus data terkait terlebih dahulu
    // 1. Hapus jawaban peserta
    mysqli_query($conn, "DELETE jp FROM jawaban_peserta jp 
                        INNER JOIN peserta_tes pt ON jp.id_peserta_tes = pt.id_peserta_tes 
                        WHERE pt.id_jadwal = $id");
    
    // 2. Hapus peserta tes
    mysqli_query($conn, "DELETE FROM peserta_tes WHERE id_jadwal = $id");
    
    // 3. Hapus soal tes
    mysqli_query($conn, "DELETE FROM soal_tes WHERE id_jadwal = $id");
    
    // 4. Hapus jadwal
    if (mysqli_query($conn, "DELETE FROM jadwal_tes WHERE id_jadwal = $id")) {
        alert('Jadwal berhasil dihapus!', 'success');
    } else {
        alert('Gagal menghapus jadwal: ' . mysqli_error($conn), 'danger');
    }
    redirect('admin/jadwal_tes.php');
}

// Get jadwal
$result = mysqli_query($conn, "SELECT jt.*, ks.nama_kategori FROM jadwal_tes jt LEFT JOIN kategori_soal ks ON jt.id_kategori = ks.id_kategori ORDER BY jt.tanggal_mulai DESC");

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-calendar-event"></i> Jadwal Tes</h2>
    <a href="jadwal_tes_add.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Tambah Jadwal
    </a>
</div>

<?php show_alert(); ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Tes</th>
                        <th>Kategori</th>
                        <th>Tanggal</th>
                        <th>Durasi</th>
                        <th>Soal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while($row = mysqli_fetch_assoc($result)): 
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><strong><?php echo $row['nama_tes']; ?></strong></td>
                        <td><?php echo $row['nama_kategori']; ?></td>
                        <td>
                            <small>
                                <?php echo date('d/m/Y H:i', strtotime($row['tanggal_mulai'])); ?><br>
                                s/d <?php echo date('d/m/Y H:i', strtotime($row['tanggal_selesai'])); ?>
                            </small>
                        </td>
                        <td><?php echo $row['durasi']; ?> menit</td>
                        <td><?php echo $row['jumlah_soal']; ?> soal</td>
                        <td>
                            <?php if($row['status'] == 'aktif'): ?>
                                <span class="badge bg-success">Aktif</span>
                            <?php elseif($row['status'] == 'selesai'): ?>
                                <span class="badge bg-secondary">Selesai</span>
                            <?php else: ?>
                                <span class="badge bg-warning">Draft</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="jadwal_tes_edit.php?id=<?php echo $row['id_jadwal']; ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="jadwal_tes_peserta.php?id=<?php echo $row['id_jadwal']; ?>" class="btn btn-sm btn-info">
                                <i class="bi bi-people"></i> Peserta
                            </a>
                            <a href="jadwal_tes.php?delete=<?php echo $row['id_jadwal']; ?>" 
                               class="btn btn-sm btn-danger" 
                               >
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
