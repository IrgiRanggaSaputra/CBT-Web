<?php
require_once '../config.php';
check_login_admin();

// Hapus soal
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Hapus jawaban peserta yang terkait dengan soal ini terlebih dahulu
    mysqli_query($conn, "DELETE FROM jawaban_peserta WHERE id_soal = $id");
    
    // Hapus soal dari jadwal tes (soal_tes)
    mysqli_query($conn, "DELETE FROM soal_tes WHERE id_soal = $id");
    
    // Hapus soal
    if (mysqli_query($conn, "DELETE FROM bank_soal WHERE id_soal = $id")) {
        header('Location: bank_soal.php');
        exit;
    }
}

// Ambil data soal
$kategori_filter = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$where = [];
if ($kategori_filter > 0) {
    $where[] = "bs.id_kategori = $kategori_filter";
}
if ($search) {
    $where[] = "(bs.pertanyaan LIKE '%$search%')";
}
$where_clause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

$query = "SELECT bs.*, ks.nama_kategori 
          FROM bank_soal bs 
          LEFT JOIN kategori_soal ks ON bs.id_kategori = ks.id_kategori 
          $where_clause 
          ORDER BY bs.created_at DESC";
$result = mysqli_query($conn, $query);

// Get kategori
$kategori_list = mysqli_query($conn, "SELECT * FROM kategori_soal ORDER BY nama_kategori");

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-journal-text"></i> Bank Soal</h2>
    <div>
        <a href="bank_soal_add.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Soal
        </a>
        <a href="bank_soal_import.php" class="btn btn-success">
            <i class="bi bi-file-earmark-excel"></i> Import Excel
        </a>
    </div>
</div>

<?php show_alert(); ?>

<div class="card">
    <div class="card-body">
        <!-- Filter -->
        <form method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <select class="form-select" name="kategori" onchange="this.form.submit()">
                        <option value="0">Semua Kategori</option>
                        <?php while($kat = mysqli_fetch_assoc($kategori_list)): ?>
                            <option value="<?php echo $kat['id_kategori']; ?>" <?php echo $kategori_filter == $kat['id_kategori'] ? 'selected' : ''; ?>>
                                <?php echo $kat['nama_kategori']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Cari soal..." value="<?php echo $search; ?>">
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
                        <th width="5%">No</th>
                        <th width="50%">Pertanyaan</th>
                        <th width="15%">Kategori</th>
                        <th width="10%">Jawaban</th>
                        <th width="10%">Bobot</th>
                        <th width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while($row = mysqli_fetch_assoc($result)): 
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td>
                            <?php echo substr($row['pertanyaan'], 0, 100); ?>...
                            <?php if($row['gambar']): ?>
                                <span class="badge bg-info"><i class="bi bi-image"></i> Gambar</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $row['nama_kategori']; ?></td>
                        <td>
                            <span class="badge bg-success"><?php echo $row['jawaban_benar']; ?></span>
                        </td>
                        <td><?php echo $row['bobot']; ?></td>
                        <td>
                            <a href="bank_soal_edit.php?id=<?php echo $row['id_soal']; ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="bank_soal.php?delete=<?php echo $row['id_soal']; ?>" 
                               class="btn btn-sm btn-danger">
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
