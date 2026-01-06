<?php
require_once '../config.php';
check_login_admin();

// Hapus kategori
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Cek apakah ada soal terkait
    $checkSoal = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM bank_soal WHERE id_kategori = $id"))['total'];
    
    // Cek apakah ada jadwal tes terkait
    $checkJadwal = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM jadwal_tes WHERE id_kategori = $id"))['total'];
    
    if ($checkSoal > 0 || $checkJadwal > 0) {
        alert('Kategori tidak bisa dihapus karena masih ada soal atau jadwal tes yang terkait. Hapus soal dan jadwal terlebih dahulu!', 'danger');
    } else {
        mysqli_query($conn, "DELETE FROM kategori_soal WHERE id_kategori = $id");
        alert('Kategori berhasil dihapus!', 'success');
    }
    redirect('admin/kategori_soal.php');
}

// Tambah/Edit kategori
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nama = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
    $desk = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    
    if ($id > 0) {
        $query = "UPDATE kategori_soal SET nama_kategori = '$nama', deskripsi = '$desk' WHERE id_kategori = $id";
        $msg = 'diupdate';
    } else {
        $query = "INSERT INTO kategori_soal (nama_kategori, deskripsi) VALUES ('$nama', '$desk')";
        $msg = 'ditambahkan';
    }
    
    mysqli_query($conn, $query);
    alert("Kategori berhasil $msg!", 'success');
    redirect('admin/kategori_soal.php');
}

$result = mysqli_query($conn, "SELECT * FROM kategori_soal ORDER BY nama_kategori");

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-bookmarks-fill"></i> Kategori Soal</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalKategori">
        <i class="bi bi-plus-circle"></i> Tambah Kategori
    </button>
</div>

<?php show_alert(); ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="30%">Nama Kategori</th>
                        <th width="45%">Deskripsi</th>
                        <th width="10%">Jumlah Soal</th>
                        <th width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while($row = mysqli_fetch_assoc($result)): 
                        $count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM bank_soal WHERE id_kategori = {$row['id_kategori']}"))['total'];
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><strong><?php echo $row['nama_kategori']; ?></strong></td>
                        <td><?php echo $row['deskripsi']; ?></td>
                        <td><span class="badge bg-info"><?php echo $count; ?> soal</span></td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editKategori(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="kategori_soal.php?delete=<?php echo $row['id_kategori']; ?>" 
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

<!-- Modal -->
<div class="modal fade" id="modalKategori" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="kategoriId">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control" name="nama_kategori" id="namaKategori" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="deskripsiKategori" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editKategori(data) {
    $('#modalTitle').text('Edit Kategori');
    $('#kategoriId').val(data.id_kategori);
    $('#namaKategori').val(data.nama_kategori);
    $('#deskripsiKategori').val(data.deskripsi);
    $('#modalKategori').modal('show');
}
</script>

<?php include 'includes/footer.php'; ?>
