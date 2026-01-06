<?php
require_once '../config.php';
check_login_admin();

$id_jadwal = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_jadwal == 0) {
    alert('ID jadwal tidak valid!', 'danger');
    redirect('admin/jadwal_tes.php');
}

// Ambil data jadwal
$query = "SELECT jt.*, ks.nama_kategori 
          FROM jadwal_tes jt 
          LEFT JOIN kategori_soal ks ON jt.id_kategori = ks.id_kategori 
          WHERE jt.id_jadwal = $id_jadwal";
$result = mysqli_query($conn, $query);
$jadwal = mysqli_fetch_assoc($result);

if (!$jadwal) {
    alert('Jadwal tidak ditemukan!', 'danger');
    redirect('admin/jadwal_tes.php');
}

// Tambah peserta ke jadwal
if (isset($_POST['tambah_peserta'])) {
    $id_peserta = (int)$_POST['id_peserta'];
    
    // Cek apakah peserta sudah terdaftar
    $check = mysqli_query($conn, "SELECT * FROM peserta_tes WHERE id_jadwal = $id_jadwal AND id_peserta = $id_peserta");
    if (mysqli_num_rows($check) > 0) {
        alert('Peserta sudah terdaftar di jadwal ini!', 'warning');
    } else {
        // Generate token unik
        $token = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        
        $query = "INSERT INTO peserta_tes (id_jadwal, id_peserta, token) VALUES ($id_jadwal, $id_peserta, '$token')";
        if (mysqli_query($conn, $query)) {
            alert('Peserta berhasil ditambahkan!', 'success');
        } else {
            alert('Gagal menambahkan peserta: ' . mysqli_error($conn), 'danger');
        }
    }
}

// Hapus peserta dari jadwal
if (isset($_GET['hapus_peserta'])) {
    $id_peserta_tes = (int)$_GET['hapus_peserta'];
    
    // Cek apakah sudah mengerjakan tes
    $check = mysqli_query($conn, "SELECT status_tes FROM peserta_tes WHERE id_peserta_tes = $id_peserta_tes");
    $data = mysqli_fetch_assoc($check);
    
    if ($data['status_tes'] != 'belum_mulai') {
        alert('Tidak dapat menghapus peserta yang sudah memulai tes!', 'danger');
    } else {
        mysqli_query($conn, "DELETE FROM peserta_tes WHERE id_peserta_tes = $id_peserta_tes");
        alert('Peserta berhasil dihapus dari jadwal!', 'success');
    }
    redirect('admin/jadwal_tes_peserta.php?id=' . $id_jadwal);
}

// Ambil daftar peserta yang sudah terdaftar
$peserta_terdaftar = mysqli_query($conn, "
    SELECT pt.*, p.nama_lengkap, p.email, p.telepon 
    FROM peserta_tes pt 
    LEFT JOIN peserta p ON pt.id_peserta = p.id_peserta 
    WHERE pt.id_jadwal = $id_jadwal 
    ORDER BY p.nama_lengkap
");

// Ambil daftar semua peserta untuk ditambahkan
$semua_peserta = mysqli_query($conn, "
    SELECT * FROM peserta 
    WHERE id_peserta NOT IN (
        SELECT id_peserta FROM peserta_tes WHERE id_jadwal = $id_jadwal
    )
    ORDER BY nama_lengkap
");

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="bi bi-people"></i> Peserta Jadwal Tes</h2>
        <p class="text-muted mb-0"><?php echo $jadwal['nama_tes']; ?></p>
    </div>
    <a href="jadwal_tes.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<?php show_alert(); ?>

<div class="row">
    <!-- Info Jadwal -->
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Info Jadwal</h5>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Kategori</strong></td>
                        <td><?php echo $jadwal['nama_kategori']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal Mulai</strong></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($jadwal['tanggal_mulai'])); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal Selesai</strong></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($jadwal['tanggal_selesai'])); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Durasi</strong></td>
                        <td><?php echo $jadwal['durasi']; ?> menit</td>
                    </tr>
                    <tr>
                        <td><strong>Jumlah Soal</strong></td>
                        <td><?php echo $jadwal['jumlah_soal']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Passing Grade</strong></td>
                        <td><?php echo $jadwal['passing_grade']; ?>%</td>
                    </tr>
                    <tr>
                        <td><strong>Status</strong></td>
                        <td>
                            <?php if($jadwal['status'] == 'aktif'): ?>
                                <span class="badge bg-success">Aktif</span>
                            <?php elseif($jadwal['status'] == 'selesai'): ?>
                                <span class="badge bg-secondary">Selesai</span>
                            <?php else: ?>
                                <span class="badge bg-warning">Draft</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Form Tambah Peserta -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Tambah Peserta</h5>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Pilih Peserta</label>
                        <select class="form-select" name="id_peserta" required>
                            <option value="">Pilih Peserta</option>
                            <?php while($p = mysqli_fetch_assoc($semua_peserta)): ?>
                                <option value="<?php echo $p['id_peserta']; ?>">
                                    <?php echo $p['nama_lengkap']; ?> (<?php echo $p['email']; ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" name="tambah_peserta" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-plus-circle"></i> Tambahkan
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Daftar Peserta -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Daftar Peserta Terdaftar</h5>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>No HP</th>
                                <th>Token</th>
                                <th>Status</th>
                                <th>Nilai</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if (mysqli_num_rows($peserta_terdaftar) == 0): 
                            ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    <i class="bi bi-inbox"></i> Belum ada peserta terdaftar
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php while($row = mysqli_fetch_assoc($peserta_terdaftar)): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo $row['nama_lengkap']; ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td><?php echo $row['telepon']; ?></td>
                                <td><code><?php echo $row['token']; ?></code></td>
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
                                    <?php if($row['nilai'] !== null): ?>
                                        <strong><?php echo number_format($row['nilai'], 2); ?></strong>
                                        <?php if($row['status_kelulusan'] == 'lulus'): ?>
                                            <span class="badge bg-success">Lulus</span>
                                        <?php elseif($row['status_kelulusan'] == 'tidak_lulus'): ?>
                                            <span class="badge bg-danger">Tidak Lulus</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($row['status_tes'] == 'belum_mulai'): ?>
                                        <a href="jadwal_tes_peserta.php?id=<?php echo $id_jadwal; ?>&hapus_peserta=<?php echo $row['id_peserta_tes']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           >
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled title="Sudah mengerjakan tes">
                                            <i class="bi bi-lock"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
