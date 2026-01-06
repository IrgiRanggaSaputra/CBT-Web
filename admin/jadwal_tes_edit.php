<?php
require_once '../config.php';
check_login_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id == 0) {
    alert('ID jadwal tidak valid!', 'danger');
    redirect('admin/jadwal_tes.php');
}

// Ambil data jadwal
$query = "SELECT * FROM jadwal_tes WHERE id_jadwal = $id";
$result = mysqli_query($conn, $query);
$jadwal = mysqli_fetch_assoc($result);

if (!$jadwal) {
    alert('Jadwal tidak ditemukan!', 'danger');
    redirect('admin/jadwal_tes.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_tes = mysqli_real_escape_string($conn, $_POST['nama_tes']);
    $id_kategori = (int)$_POST['id_kategori'];
    $tanggal_mulai = mysqli_real_escape_string($conn, $_POST['tanggal_mulai']);
    $tanggal_selesai = mysqli_real_escape_string($conn, $_POST['tanggal_selesai']);
    $durasi = (int)$_POST['durasi'];
    $jumlah_soal = (int)$_POST['jumlah_soal'];
    $passing_grade = (float)$_POST['passing_grade'];
    $instruksi = mysqli_real_escape_string($conn, $_POST['instruksi']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $query = "UPDATE jadwal_tes SET 
              nama_tes = '$nama_tes',
              id_kategori = $id_kategori,
              tanggal_mulai = '$tanggal_mulai',
              tanggal_selesai = '$tanggal_selesai',
              durasi = $durasi,
              jumlah_soal = $jumlah_soal,
              passing_grade = $passing_grade,
              instruksi = '$instruksi',
              status = '$status'
              WHERE id_jadwal = $id";
    
    if (mysqli_query($conn, $query)) {
        alert('Jadwal tes berhasil diupdate!', 'success');
        redirect('admin/jadwal_tes.php');
    } else {
        alert('Gagal mengupdate jadwal: ' . mysqli_error($conn), 'danger');
    }
}

$kategori_list = mysqli_query($conn, "SELECT * FROM kategori_soal ORDER BY nama_kategori");

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-pencil-square"></i> Edit Jadwal Tes</h2>
    <a href="jadwal_tes.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<?php show_alert(); ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Nama Tes <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_tes" value="<?php echo htmlspecialchars($jadwal['nama_tes']); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Kategori Soal <span class="text-danger">*</span></label>
                        <select class="form-select" name="id_kategori" required>
                            <option value="">Pilih Kategori</option>
                            <?php while($kat = mysqli_fetch_assoc($kategori_list)): ?>
                                <option value="<?php echo $kat['id_kategori']; ?>" <?php echo $jadwal['id_kategori'] == $kat['id_kategori'] ? 'selected' : ''; ?>>
                                    <?php echo $kat['nama_kategori']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Tanggal & Waktu Mulai <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" name="tanggal_mulai" 
                               value="<?php echo date('Y-m-d\TH:i', strtotime($jadwal['tanggal_mulai'])); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Tanggal & Waktu Selesai <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" name="tanggal_selesai" 
                               value="<?php echo date('Y-m-d\TH:i', strtotime($jadwal['tanggal_selesai'])); ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Durasi (menit) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="durasi" min="1" value="<?php echo $jadwal['durasi']; ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Jumlah Soal <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="jumlah_soal" min="1" value="<?php echo $jadwal['jumlah_soal']; ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Passing Grade <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="passing_grade" min="0" max="100" step="0.01" 
                               value="<?php echo $jadwal['passing_grade']; ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Instruksi Khusus</label>
                <textarea class="form-control" name="instruksi" rows="4" placeholder="Tambahkan instruksi khusus untuk tes ini..."><?php echo htmlspecialchars($jadwal['instruksi']); ?></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="draft" <?php echo $jadwal['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                    <option value="aktif" <?php echo $jadwal['status'] == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="selesai" <?php echo $jadwal['status'] == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Update Jadwal
            </button>
            <a href="jadwal_tes.php" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> Batal
            </a>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
