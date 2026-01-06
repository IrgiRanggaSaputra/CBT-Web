<?php
require_once '../config.php';
check_login_admin();

// Hapus peserta
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Hapus jawaban peserta terlebih dahulu
    mysqli_query($conn, "DELETE FROM jawaban_peserta WHERE id_peserta_tes IN (SELECT id_peserta_tes FROM peserta_tes WHERE id_peserta = $id)");
    
    // Hapus data tes peserta
    mysqli_query($conn, "DELETE FROM peserta_tes WHERE id_peserta = $id");
    
    // Hapus peserta
    if (mysqli_query($conn, "DELETE FROM peserta WHERE id_peserta = $id")) {
        header('Location: peserta.php');
        exit;
    }
}

// Ambil data peserta
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where = $search ? "WHERE nama_lengkap LIKE '%$search%' OR nomor_peserta LIKE '%$search%'" : '';
$query = "SELECT * FROM peserta $where ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people-fill"></i> Data Peserta</h2>
    <div>
        <a href="peserta_add.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Peserta
        </a>
        <a href="peserta_import.php" class="btn btn-success">
            <i class="bi bi-file-earmark-excel"></i> Import Excel
        </a>
    </div>
</div>

<?php show_alert(); ?>

<div class="card">
    <div class="card-body">
        <!-- Search -->
        <form method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Cari peserta..." value="<?php echo $search; ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nomor Peserta</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>Telepon</th>
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
                        <td><strong><?php echo $row['nomor_peserta']; ?></strong></td>
                        <td><?php echo $row['nama_lengkap']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['telepon']; ?></td>
                        <td>
                            <?php if($row['status'] == 'aktif'): ?>
                                <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="peserta_edit.php?id=<?php echo $row['id_peserta']; ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="peserta.php?delete=<?php echo $row['id_peserta']; ?>" 
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
